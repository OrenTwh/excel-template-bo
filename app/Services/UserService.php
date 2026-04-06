<?php

namespace App\Services;

use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Hash,
    Validator,
    Mail,
    Crypt,
    Storage,
};

use App\Mail\EnquiryEmail;
use App\Mail\OtpMail;

use Illuminate\Validation\Rules\Password;

use App\Models\{
    User,
    OtpAction,
    TmpUser,
    MailContent,
    Wallet,
    Option,
    WalletTransaction,
    UserNotification,
    UserNotificationSeen,
    UserNotificationUser,
    UserDevice,
    UserSocial,
    UserFriend,
    Booking,
    CourtBooking,
    CourtBookingGroup,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class UserService
{
    public static function allUsers( $request ) {

        $user = User::with(  ['nationalityInfo'] )->select( 'users.*' );

        $filterObject = self::filter( $request, $user );
        $user = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = strToUpper($request->input( 'order.0.dir' ));

            switch ( $request->input( 'order.0.column' ) ) {
                case '2':
                    $user->orderBy( 'created_at', $dir );
                    break;
                case '3':
                    $user->orderBy( 'fullname', $dir );
                    break;
                case '4':
                    $user->orderBy( 'email', $dir );
                    break;
                case '5':
                    $user->orderBy( 'phone_number', $dir );
                    break;
                case '6':
                    $user->orderBy( 'date_of_birth', $dir );
                    break;
                case '7':
                    $user->orderBy( 'nationality', $dir );
                    break;
                case '8':
                    $user->orderBy( 'status', $dir );
                    break;
            }
        }

        $userCount = $user->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $users = $user->skip( $offset )->take( $limit )->get();

        if ( $users ) {
            $users->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = User::count();

        $data = [
            'users' => $users,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $userCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'users.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'users.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->date_of_birth ) ) {
            if ( str_contains( $request->date_of_birth, 'to' ) ) {
                $dates = explode( ' to ', $request->date_of_birth );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'users.date_of_birth', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->date_of_birth );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'users.date_of_birth', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->fullname ) ) {
            $model->where( 'fullname', 'LIKE', '%' . $request->fullname . '%' );
            $filter = true;
        }

        if ( !empty( $request->first_name ) ) {
            $model->where( 'first_name', 'LIKE', '%' . $request->first_name . '%' );
            $filter = true;
        }

        if (!empty($request->nationality)) {
            $model->whereHas('nationalityInfo', function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->nationality . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->last_name ) ) {
            $model->where( 'last_name', 'LIKE', '%' . $request->last_name . '%' );
            $filter = true;
        }

        if ( !empty( $request->username ) ) {
            $model->where( 'username', 'LIKE', '%' . $request->username . '%' );
            $filter = true;
        }

        if ( !empty( $request->email ) ) {
            $model->where( 'email', 'LIKE', '%' . $request->email . '%' );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->phone_number ) ) {
            $userInput = $request->phone_number;
        
            if ( preg_match( '/^\+\d+$/', $userInput ) ) {
                // Filter by calling_code
                $model->where( 'users.calling_code', $userInput );
            } else {
                $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $userInput );
            
                $model->where( function ( $query ) use ( $normalizedPhone, $userInput ) {
                    $query->where( 'users.phone_number', 'LIKE', "%$normalizedPhone%" );
                } );
            }
        
            $filter = true;
        }

        if ( !empty( $request->user ) ) {
            $userInput = $request->user;
        
            $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $userInput );
        
            $model->where( function ( $query ) use ( $userInput ) {
                $query->where( 'users.email', 'LIKE', '%' . $userInput . '%' )
                      ->orWhere( 'users.first_name', 'LIKE', '%' . $userInput . '%' )
                      ->orWhere( 'users.last_name', 'LIKE', '%' . $userInput . '%' );
            } );
        
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $model->where( 'phone_number', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'email', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneUser( $request ) {

        $user = User::find( Helper::decode( $request->id ) );

        return response()->json( $user );
    }

    public static function createUser( $request ) {

        $validator = Validator::make( $request->all(), [
            'username' => [ 'nullable', 'alpha_dash', 'unique:users,username', new CheckASCIICharacter ],
            'email' => [ 'required', 'bail', 'unique:users,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'required' ],
            'calling_code' => [ 'nullable' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {

                $defaultCallingCode = "+60";

                $exist = User::where( 'status', 10 )
                ->where( 'calling_code', request( 'calling_code' ) ? request( 'calling_code' ) : $defaultCallingCode )
                ->where( function ( $query ) use ( $value ) {
                    $query->where( 'phone_number', request( 'phone_number' ) )
                        ->orWhere( 'phone_number', ltrim( request( 'phone_number' ), '0' ) );
                } )->first();

                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'required', Password::min( 8 ) ],
            'invitation_code' => [ 'nullable', 'unique:users,invitation_code' ],
        ] );

        $attributeName = [
            'username' => __( 'user.username' ),
            'email' => __( 'user.email' ),
            'fullname' => __( 'user.fullname' ),
            'password' => __( 'user.password' ),
            'phone_number' => __( 'user.phone_number' ),
            'invitation_code' => __( 'user.invitation_code' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createUserObject = [
                'fullname' => $request->fullname ?? null,
                'username' => $request->username ?? null,
                'first_name' => $request->first_name ?? null,
                'last_name' => $request->last_name ?? null,
                'fullname' => $request->fullname ?? null,
                'email' => $request->email ? strtolower( $request->email ) : null,
                'phone_number' => $request->phone_number,
                'calling_code' => $request->calling_code ? $request->calling_code : null,
                'password' => Hash::make( $request->password ),
                'date_of_birth' => $request->date_of_birth,
                'nationality' => $request->nationality,
                'status' => 10,
                'invitation_code' => $request->invitation_code ? strtoupper( $request->invitation_code ) : strtoupper( \Str::random( 6 ) ),
            ];

            $createUser = User::create( $createUserObject );

            for ( $i = 1; $i <= 2; $i++ ) {
                $userWallet = Wallet::create( [
                    'user_id' => $createUser->id,
                    'type' => $i,
                    'balance' => 0,
                ] );
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.users' ) ) ] ),
        ] );
    }

    public static function updateUser( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'username' => [ 'nullable', 'alpha_dash', 'unique:users,username,' . $request->id, new CheckASCIICharacter ],
            'email' => [ 'required', 'bail', 'unique:users,email,' . $request->id, 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'required' ],
            // 'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {
                
            //     // $exist = User::where( 'phone_number', $value )
            //     //     ->where( 'id', '!=', $request->id )
            //     //     ->first();

            //     $defaultCallingCode = "+60";

            //     $exist = User::where( 'id', '!=', $request->id )
            //         ->where( 'status', 10 )
            //         ->where( 'calling_code', request( 'calling_code' ) ? request( 'calling_code' ) : $defaultCallingCode )
            //         ->where( function ( $query ) use ( $value ) {
            //             $query->where( 'phone_number', request( 'phone_number' ) )
            //                 ->orWhere( 'phone_number', ltrim( request( 'phone_number' ), '0' ) );
            //         } )->first();

            //     if ( $exist ) {
            //         $fail( __( 'validation.exists' ) );
            //         return false;
            //     }
            // } ],
            'password' => [ 'nullable', Password::min( 8 ) ],
            'invitation_code' => [ 'nullable', 'unique:users,invitation_code,' . $request->id ],
        ] );

        $attributeName = [
            'username' => __( 'user.username' ),
            'email' => __( 'user.email' ),
            'fullname' => __( 'user.fullname' ),
            'password' => __( 'user.password' ),
            'phone_number' => __( 'user.phone_number' ),
            'invitation_code' => __( 'user.invitation_code' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateUser = User::find( $request->id );
            $updateUser->username = strtolower( $request->username );
            $updateUser->first_name = strtolower( $request->first_name );
            $updateUser->last_name = strtolower( $request->last_name );
            $updateUser->fullname = $request->fullname;
            $updateUser->email = strtolower( $request->email );
            $updateUser->phone_number = $request->phone_number ?? $updateUser->phone_number;
            $updateUser->calling_code = $request->calling_code ? $request->calling_code : $updateUser->calling_code;
            $updateUser->date_of_birth = $request->date_of_birth;
            $updateUser->nationality = $request->nationality;
            $updateUser->fullname = $request->fullname;
            if ( !empty( $request->password ) ) {
                $updateUser->password = Hash::make( $request->password );
            }
            if ( !empty( $request->invitation_code ) ) {
                $updateUser->invitation_code = strtoupper( $request->invitation_code );
            }

            $updateUser->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.users' ) ) ] ),
        ] );
    }

    public static function sendFriendRequest( $request ) {

        $authUser = auth()->user();

        $validator = Validator::make( $request->all(), [
            'invitation_code' => [ 'required' ],
        ] );
        $validator->validate();

        $target = User::where( 'invitation_code', strtoupper( $request->invitation_code ) )
            ->where( 'status', 10 )
            ->first();

        if ( !$target ) {
            return response()->json( [ 'message' => __( 'user.friend_not_found' ) ], 422 );
        }

        if ( $target->id === $authUser->id ) {
            return response()->json( [ 'message' => __( 'user.friend_self' ) ], 422 );
        }

        $existing = UserFriend::where( function( $q ) use ( $authUser, $target ) {
            $q->where( 'user_id', $authUser->id )->where( 'friend_id', $target->id );
        } )->orWhere( function( $q ) use ( $authUser, $target ) {
            $q->where( 'user_id', $target->id )->where( 'friend_id', $authUser->id );
        } )->first();

        if ( $existing ) {
            if ( $existing->status === UserFriend::STATUS_ACCEPTED ) {
                return response()->json( [ 'message' => __( 'user.already_friends' ) ], 422 );
            }
            if ( $existing->status === UserFriend::STATUS_PENDING ) {
                return response()->json( [ 'message' => __( 'user.friend_request_pending' ) ], 422 );
            }
            // declined — allow re-request
            $existing->update( [ 'status' => UserFriend::STATUS_PENDING ] );
            return response()->json( [ 'message' => __( 'user.friend_request_sent' ) ] );
        }

        UserFriend::create( [
            'user_id'   => $authUser->id,
            'friend_id' => $target->id,
            'status'    => UserFriend::STATUS_PENDING,
        ] );

        return response()->json( [ 'message' => __( 'user.friend_request_sent' ) ] );
    }

    public static function respondFriendRequest( $request ) {

        $authUser = auth()->user();

        $validator = Validator::make( $request->all(), [
            'id'     => [ 'required' ],
            'action' => [ 'required', 'in:accept,decline' ],
        ] );
        $validator->validate();

        $friendRequest = UserFriend::where( 'id', $request->id )
            ->where( 'friend_id', $authUser->id )
            ->where( 'status', UserFriend::STATUS_PENDING )
            ->first();

        if ( !$friendRequest ) {
            return response()->json( [ 'message' => __( 'user.friend_request_not_found' ) ], 404 );
        }

        $friendRequest->status = $request->action === 'accept'
            ? UserFriend::STATUS_ACCEPTED
            : UserFriend::STATUS_DECLINED;
        $friendRequest->save();

        $message = $request->action === 'accept'
            ? __( 'user.friend_request_accepted' )
            : __( 'user.friend_request_declined' );

        return response()->json( [ 'message' => $message ] );
    }

    public static function removeFriend( $request ) {

        $authUser = auth()->user();

        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
        $validator->validate();

        $friendship = UserFriend::where( 'id', $request->id )
            ->where( function( $q ) use ( $authUser ) {
                $q->where( 'user_id', $authUser->id )
                  ->orWhere( 'friend_id', $authUser->id );
            } )
            ->where( 'status', UserFriend::STATUS_ACCEPTED )
            ->first();

        if ( !$friendship ) {
            return response()->json( [ 'message' => __( 'user.friend_not_found' ) ], 404 );
        }

        $friendship->delete();

        return response()->json( [ 'message' => __( 'user.friend_removed' ) ] );
    }

    public static function getFriends( $request ) {

        $authUser = auth()->user();

        $friends = UserFriend::with( [ 'user', 'friend' ] )
            ->where( 'status', UserFriend::STATUS_ACCEPTED )
            ->where( function( $q ) use ( $authUser ) {
                $q->where( 'user_id', $authUser->id )
                  ->orWhere( 'friend_id', $authUser->id );
            } )
            ->get()
            ->map( function( $f ) use ( $authUser ) {
                $other = $f->user_id === $authUser->id ? $f->friend : $f->user;
                return [
                    'friendship_id'   => $f->id,
                    'user_id'         => $other->id,
                    'fullname'        => $other->fullname,
                    'email'           => $other->email,
                    'calling_code'    => $other->calling_code,
                    'phone_number'    => $other->phone_number,
                    'invitation_code' => $other->invitation_code,
                    'profile_picture' => $other->profile_picture_path_new,
                    'since'           => $f->updated_at ? $f->updated_at->format( 'Y-m-d' ) : null,
                ];
            } );

        return response()->json( [ 'friends' => $friends ] );
    }

    public static function getFriendRequests( $request ) {

        $authUser = auth()->user();

        $requests = UserFriend::with( 'user' )
            ->where( 'friend_id', $authUser->id )
            ->where( 'status', UserFriend::STATUS_PENDING )
            ->get()
            ->map( function( $f ) {
                return [
                    'id'           => $f->id,
                    'fullname'     => $f->user->fullname,
                    'email'        => $f->user->email,
                    'calling_code' => $f->user->calling_code,
                    'phone_number' => $f->user->phone_number,
                    'profile_picture' => $f->user->profile_picture_path_new,
                    'requested_at' => $f->created_at ? $f->created_at->format( 'Y-m-d H:i:s' ) : null,
                ];
            } );

        return response()->json( [ 'friend_requests' => $requests ] );
    }

    public static function userDownlines( $request ) {

        $user = User::find( Helper::decode( $request->id ) );

        if ( !$user ) {
            return response()->json( [ 'message' => 'User not found' ], 404 );
        }

        $downlines = User::where( 'referral_id', $user->id )
            ->get()
            ->append( [ 'encrypted_id' ] )
            ->map( function( $d ) {
                return [
                    'encrypted_id' => $d->encrypted_id,
                    'fullname'     => $d->fullname,
                    'email'        => $d->email,
                    'calling_code' => $d->calling_code,
                    'phone_number' => $d->phone_number,
                    'status'       => $d->status,
                    'created_at'   => $d->created_at ? $d->created_at->format( 'Y-m-d' ) : null,
                ];
            } );

        return response()->json( [
            'downlines' => $downlines,
        ] );
    }

    public static function sendAdminTestNotification( $request ) {
        try {
            $userId = Helper::decode( $request->input( 'id' ) );
            $user   = User::find( $userId );

            if ( !$user ) {
                return response()->json( [ 'message' => 'User not found.' ], 404 );
            }

            $devices = UserDevice::where( 'user_id', $userId )->get();

            $titleEn   = $request->input( 'title_en', 'Xpark' );
            $titleMs   = $request->input( 'title_ms', 'Xpark' );
            $titleZh   = $request->input( 'title_zh', 'Xpark' );
            $contentEn = $request->input( 'content_en', 'Test notification.' );
            $contentMs = $request->input( 'content_ms', 'Test notification.' );
            $contentZh = $request->input( 'content_zh', 'Test notification.' );

            // Create UserNotification record with raw JSON (bypass lang-key mutators)
            $notification = new UserNotification();
            $notification->setRawAttributes( [
                'type'           => 2,
                'title'          => json_encode( [ 'en' => $titleEn, 'ms' => $titleMs, 'zh' => $titleZh ] ),
                'content'        => json_encode( [ 'en' => strip_tags( $contentEn ), 'ms' => strip_tags( $contentMs ), 'zh' => strip_tags( $contentZh ) ] ),
                'url_slug'       => null,
                'system_title'   => null,
                'system_content' => null,
                'meta_data'      => null,
                'key'            => 'test',
            ] );
            $notification->save();

            UserNotificationUser::create( [
                'user_notification_id' => $notification->id,
                'user_id'              => $userId,
            ] );

            // Send OneSignal push to all user devices
            $header = [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Basic ' . config( 'services.os.api_key' ),
            ];

            $results = [];

            foreach ( $devices as $device ) {
                $payload = [
                    'app_id'             => config( 'services.os.app_id' ),
                    'headings'           => [ 'en' => $titleEn, 'ms' => $titleMs, 'zh' => $titleZh ],
                    'contents'           => [ 'en' => strip_tags( $contentEn ), 'ms' => strip_tags( $contentMs ), 'zh' => strip_tags( $contentZh ) ],
                    'include_player_ids' => [ $device->register_token ],
                    'data'               => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound'        => 'default',
                        'status'       => 'done',
                        'key'          => 'test',
                        'id'           => $notification->id,
                    ],
                ];

                $results[] = Helper::curlPost(
                    'https://onesignal.com/api/v1/notifications',
                    json_encode( $payload ),
                    $header
                );
            }

            return response()->json( [
                'message'  => 'Test notification sent to ' . $devices->count() . ' device(s).',
                'response' => $results,
            ] );

        } catch ( \Throwable $th ) {
            return response()->json( [
                'message' => 'Failed to send test notification.',
            ], 500 );
        }
    }

    public static function updateUserStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateUser = User::find( $request->id );
        $updateUser->status = $request->status;
        $updateUser->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.users' ) ) ] ),
        ] );
    }

    public static function createUserClient( $request ) {

        $validator = Validator::make( $request->all(), [
            'email' => [ 'required', 'bail', 'unique:users,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'required' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {

                $exist = User::where( 'phone_number', $value )
                    ->first();

                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'required', 'confirmed', Password::min( 8 ) ],
        ] );

        $attributeName = [
            'email' => __( 'user.email' ),
            'fullname' => __( 'user.fullname' ),
            'password' => __( 'user.password' ),
            'phone_number' => __( 'user.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createUserObject = [
                'name' => strtolower( $request->fullname ),
                'fullname' => $request->fullname,
                'email' => strtolower( $request->email ),
                'phone_number' => $request->phone_number,
                'password' => Hash::make( $request->password ),
                'status' => 10,
            ];

            $createUser = User::create( $createUserObject );
            
            $createUser->save();
            
            $createUser = User::create( [
                'user_id' => $createUser->id,
                'fullname' => $request->fullname,
                'user_name' => $request->fullname,
                'feedback_email' => $createUser->email,
                'calling_code' => '+60',
                'phone_number' => $createUser->phone_number,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.users' ) ) ] ),
        ] );
    }

    public static function updateProfile( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            // 'name' => [ 'required', 'alpha_dash', 'unique:users,name,' . $request->id, new CheckASCIICharacter ],
            'email' => [ 'required', 'bail', 'unique:users,email,' . $request->id, 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'required' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {
                
                $exist = User::where( 'phone_number', $value )
                    ->where( 'id', '!=', $request->id )
                    ->first();

                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'nullable', Password::min( 8 ) ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
        ] );

        $attributeName = [
            'username' => __( 'user.username' ),
            'email' => __( 'user.email' ),
            'fullname' => __( 'user.fullname' ),
            'password' => __( 'user.password' ),
            'phone_number' => __( 'user.phone_number' ),
            'address_1' => __( 'user.address_1' ),
            'address_2' => __( 'user.address_2' ),
            'city' => __( 'user.city' ),
            'state' => __( 'user.state' ),
            'postcode' => __( 'user.postcode' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateUser = User::find( $request->id );
            // $updateUser->name = strtolower( $request->name );
            $updateUser->email = strtolower( $request->email );
            $updateUser->phone_number = $request->phone_number;
            $updateUser->fullname = $request->fullname;

            $updateUser = User::find( $request->id );
            $updateUser->address_1 = $request->address_1;
            $updateUser->address_2 = $request->address_2;
            $updateUser->city = $request->city;
            $updateUser->state = $request->state;
            $updateUser->postcode = $request->postcode;

            if ( !empty( $request->password ) ) {
                $updateUser->password = Hash::make( $request->password );
            }

            $updateUser->save();
            $updateUser->save();

            DB::commit();

            return redirect()->route('web.profile')->with('success', __('template.x_updated', ['title' => Str::singular(__('template.users'))]));

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.users' ) ) ] ),
        ] );
    }
    
    public static function updateUserProfile( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'user_name' => [ 'nullable' ],
            'user_fullname' => [ 'nullable' ],
            'feedback_email' => [ 'nullable' ],
            'user_phone_number' => [ 'nullable' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
        ] );

        $attributeName = [
            'user_name' => __( 'user.user_name' ),
            'user_fullname' => __( 'user.fullname' ),
            'feedback_email' => __( 'user.feedback_email' ),
            'user_phone_number' => __( 'user.phone_number' ),
            'address_1' => __( 'user.address_1' ),
            'address_2' => __( 'user.address_2' ),
            'city' => __( 'user.city' ),
            'state' => __( 'user.state' ),
            'postcode' => __( 'user.postcode' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateUser = User::find( $request->id );
            $updateUser->user->user_name = $request->user_name;
            $updateUser->user->fullname = $request->fullname;
            $updateUser->user->feedback_email = $request->feedback_email;
            $updateUser->user->phone_number = $request->user_phone_number;
            $updateUser->user->address_1 = $request->address_1;
            $updateUser->user->address_2 = $request->address_2;
            $updateUser->user->postcode = $request->postcode;
            $updateUser->user->state = $request->state;
            $updateUser->user->city = $request->city;
            $updateUser->user->save();

            DB::commit();

            return redirect()->route('web.profile')->with('success', __('template.x_updated', ['title' => Str::singular(__('template.users'))]));

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.users' ) ) ] ),
        ] );
    }

    public static function forgotPasswordOtp( $request ) {

        DB::beginTransaction();

        $request->merge( [
            'phone_number' => ltrim($request->phone_number, '0'),
        ] );

        $validator = Validator::make( $request->all(), [
            'phone_number' => [ 'required' , function( $attributes, $value, $fail ) {

                $defaultCallingCode = "+60";

                $user = User::where( 'status', 10 )
                        ->where( 'calling_code', request( 'calling_code' ) ? request( 'calling_code' ) : $defaultCallingCode )
                        ->where( function ( $query ) use ( $value ) {
                            $query->where( 'phone_number', $value )
                                ->orWhere( 'phone_number', ltrim( $value, '0' ) );
                        } )
                        ->first();

                if ( !$user ) {
                    $fail( __( 'user.user_wrong_user' ) );
                    return 0;
                }

                if( $user->status == 20 ) {
                    $fail( __( 'user.account_suspended' ) );
                    return 0;
                }

                if( $user->is_social_account == 1 ) {
                    $fail( __( 'user.registered_social' ) );
                    return 0;
                }
            } ],
        ] );

        $attributeName = [
            'phone_number' => __( 'user.phone_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $data['otp_code'] = '';
            $data['identifier'] = '';

            $existingUser = User::where( 'calling_code', request( 'calling_code' ) )
                ->where( 'phone_number', request( 'phone_number' ) )
                ->orWhere('phone_number', ltrim(request('phone_number'), '0'))
                ->first();

            if ( $existingUser ) {
                $forgotPassword = Helper::requestOtp( 'forgot_password', [
                    'id' => $existingUser->id,
                    'email' => $existingUser->email,
                    'phone_number' => $existingUser->phone_number,
                    'calling_code' => $existingUser->calling_code,
                ] );
                
                DB::commit();

                // Mail::to( $existingUser->email )->send(new OtpMail( $forgotPassword ));
    
                if (Mail::failures() != 0) {

                    return response()->json( [
                        'message' => 'Reset Password Otp Success',
                        'message_key' => 'request_otp_success',
                        'data' => [
                            'title' => $forgotPassword ? __( 'user.otp_email_success' ) : '',
                            'note' => $forgotPassword ? __( 'user.otp_email_success_note' ) : '',
                            'identifier' => $forgotPassword['identifier'],
                            'otp_code' => '#DEBUG - ' . $forgotPassword['otp_code'],
                        ]
                    ] );
                }

                return "Oops! There was some error sending the email.";
            } else {
                return response()->json([
                    'message' => __('user.user_not_found'),
                    'message_key' => 'get_user_failed',
                    'data' => null,
                ]);
            }

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }

        return response()->json( [
            'message' => 'Reset Password Otp Success',
            'message_key' => 'request_otp_success',
            'data' => $data,
        ] );
    }

    public static function checkPhoneNumber( $request ) {

        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'string'],
            'calling_code' => ['nullable', 'string'],
        ]);

        $attributeName = [
            'phone_number' => __( 'user.phone_number' ),
            'calling_code' => __( 'user.calling_code' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $existingUser = User::where( 'phone_number', $request->phone_number )
                ->when($request->calling_code, function($query) use ($request) {
                    return $query->where('calling_code', $request->calling_code);
                })
                ->first();

            if ( $existingUser ) {
               
                return response()->json([
                    'message' => __('user.user_exist'),
                    'message_key' => 'user_exist',
                    'data' => [
                        'user_exists' => true,
                        'phone_number' => $request->phone_number,
                        'calling_code' => $request->calling_code ?? $existingUser->calling_code,
                    ]
                ]);

            } else {
                return response()->json([
                    'message' => __('user.user_not_found'),
                    'message_key' => 'user_not_found',
                    'data' => [
                        'user_exists' => false,
                        'phone_number' => $request->phone_number,
                        'calling_code' => $request->calling_code,
                    ]
                ]);
            }

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'check_phone_number_failed'
            ], 500 );
        }
    }

    public static function resetPassword( $request ) {

        DB::beginTransaction();

        try {
            $request->merge( [
                'identifier' => Crypt::decryptString( $request->identifier ),
            ] );
        } catch ( \Throwable $th ) {
            return response()->json( [
                'message' =>  __( 'user.invalid_otp' ),
            ], 500 );
        }

        $validator = Validator::make( $request->all(), [
            'identifier' => [ 'required', function( $attribute, $value, $fail ) use ( $request, &$currentOtpAction ) {

                $currentOtpAction = OtpAction::lockForUpdate()
                    ->find( $value );

                if ( !$currentOtpAction ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

                if ( $currentOtpAction->status != 1 ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

                if ( Carbon::parse( $currentOtpAction->expire_on )->isPast() ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

                if ( $currentOtpAction->otp_code != $request->otp_code ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

            } ],
            'password' => [ 'required', 'confirmed', Password::min( 8 ) ],
        ] );

        $attributeName = [
            'password' => __( 'user.password' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $updateUser = User::find( $currentOtpAction->user_id );

            if ( $updateUser->is_social_account == 1 ) {
                return response()->json( [
                    'message' => __( 'user.registered_social' ),
                ], 403 );
            }

            $updateUser->password = Hash::make( $request->password );
            $updateUser->save();

            $currentOtpAction->status = 10;
            $currentOtpAction->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }

        return response()->json( [
            'message' => 'Yippee! Password changed!',
            'message_key' => 'reset_success',
            'data' => $updateUser,
        ] );
    }

    // Api
    public static function registerUser( $request ) {

        $request->merge( [
            'phone_number' => ltrim($request->phone_number, '0'),
        ] );

        try {
            $request->merge( [
                'identifier' => Crypt::decryptString( $request->identifier ),
            ] );
        } catch ( \Throwable $th ) {
            return response()->json( [
                'message' => __( 'validation.header_message' ),
                'errors' => [
                    'identifier' => [
                        __( 'user.invalid_otp' ),
                    ],
                ]
            ], 422 );
        }

        $validator = Validator::make( $request->all(), [
            'otp_code' => [ 'required' ],
            'identifier' => [ 'required', function( $attribute, $value, $fail ) use ( $request, &$currentTmpUser ) {

                $currentTmpUser = TmpUser::lockForUpdate()->find( $value );

                if ( !$currentTmpUser ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

                if ( $currentTmpUser->status != 1 ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

                if ( $currentTmpUser->otp_code != $request->otp_code ) {
                    $fail( __( 'user.invalid_otp' ) );
                    return false;
                }

                if ( $currentTmpUser->phone_number != $request->phone_number ) {
                    $fail( __( 'user.invalid_phone_number' ) );
                    return false;
                }
            } ],
            'email' => [ 'nullable', 'bail', 'unique:users,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'fullname' => [ 'required' ],
            'calling_code' => [ 'nullable', 'exists:countries,calling_code' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) {

                $defaultCallingCode = "+60";

                $exist = User::where( 'status', 10 )
                ->where( 'calling_code', request( 'calling_code' ) ? request( 'calling_code' ) : $defaultCallingCode )
                ->where( function ( $query ) use ( $value ) {
                    $query->where( 'phone_number', request( 'phone_number' ) )
                        ->orWhere( 'phone_number', ltrim( request( 'phone_number' ), '0' ) );
                } )
                ->first();
                
                if ( $exist ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ],
            'password' => [ 'required', 'confirmed', Password::min( 8 ) ],
            'invitation_code' => [ 'sometimes', 'exists:users,invitation_code' ],
        ] );

        $attributeName = [
            'email' => __( 'user.email' ),
            'fullname' => __( 'user.fullname' ),
            'password' => __( 'user.password' ),
            'phone_number' => __( 'user.phone_number' ),
            'calling_code' => __( 'user.calling_code' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createUserObject = [
                'fullname' => $request->fullname ? strtolower( $request->fullname ) : null,
                'username' => $request->email ? strtolower( $request->email ) : null,
                'email' => $request->email ? strtolower( $request->email ) : null,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'calling_code' => $request->calling_code ? $request->calling_code : "+60",
                'password' => Hash::make( $request->password ),
                'status' => 10,
                'invitation_code' => strtoupper( \Str::random( 6 ) ),
            ];

            $referral = User::where( 'invitation_code', $request->invitation_code )->first();

            if ( $referral ) {
                $createUserObject['referral_id'] = $referral->id;
                $createUserObject['referral_structure'] = $referral->referral_structure . '|' . $referral->id;
            }

            $createUser = User::create( $createUserObject );
            // assign register bonus
            $registerBonus = Option::getRegisterBonusSettings();

            for ( $i = 1; $i <= 2; $i++ ) {
                $userWallet = Wallet::create( [
                    'user_id' => $createUser->id,
                    'type' => $i,
                    'balance' => 0,
                ] );
            }

            if ( $registerBonus ) {
                WalletService::transact( $userWallet, [
                    'amount' => $registerBonus->option_value,
                    'remark' => 'Register Bonus',
                    'type' => 2,
                    'transaction_type' => 20,
                ] );
            }

            // assign referral bonus
            $referralBonus = Option::getReferralBonusSettings();
            if( $referral && $referralBonus){

                $referralWallet = $referral->wallets->where('type',1)->first();

                if( $referralWallet ) {
                    WalletService::transact( $referralWallet, [
                        'amount' => $referralBonus->option_value,
                        'remark' => 'Register Bonus',
                        'type' => $referralWallet->type,
                        'transaction_type' => 22,
                    ] );
                }
            }

            $currentTmpUser = TmpUser::find( $request->identifier );
            $currentTmpUser->status = 10;
            $currentTmpUser->save();

            self::createUserNotification(
                $createUser->id,
                'notification.register_success',
                'notification.register_success_content',
                'register',
                'home'
            );

            // Register OneSignal
            if ( !empty( $request->register_token ) ) {
                self::registerOneSignal( $user->id, $request->device_type, $request->register_token );
            }

            $token = $createUser->createToken( 'user_token' )->plainTextToken;
            $createUser->token = $token;

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'user.register_success' ),
            'message_key' => 'register_success',
            'data' => $createUser,
            'token' => $token,
        ] );

    }

    public static function loginUser( $request ) {

        $request->merge( [ 'account' => 'test' ] );

        $request->validate( [
            'calling_code' => 'required',
            'phone_number' => 'required',
            'password' => 'required',
            'account' => [ 'sometimes', function( $attributes, $value, $fail ) {

                $defaultCallingCode = "+60";

                $user = User::where( 'status', 10 )
                    ->where( 'calling_code', request( 'calling_code' ) ? request( 'calling_code' ) : $defaultCallingCode )
                    ->where( function ( $query ) use ( $value ) {
                        $query->where( 'phone_number', request( 'phone_number' ) )
                            ->orWhere( 'phone_number', ltrim( request( 'phone_number' ), '0' ) );
                    } )
                    ->first();

                if ( !$user ) {
                    $fail( __( 'user.user_wrong_user' ) );
                    return 0;
                }

                if ( !Hash::check( request( 'password' ), $user->password ) ) {
                    $fail( __( 'user.user_wrong_user_password' ) );
                    return 0;
                }

                if( $user->status == 20 ) {
                    $fail( __( 'user.account_suspended' ) );
                    return 0;
                }

                if( $user->is_social_account == 1 ) {
                    $fail( __( 'user.registered_social' ) );
                    return 0;
                }


            } ],
        ] );

        $defaultCallingCode = "+60";

        $user = User::where( 'status', 10 )
        ->where( 'calling_code', $request->calling_code ? $request->calling_code : $defaultCallingCode )
        ->where( function ( $query ) use ( $request ) {
            $query->where( 'phone_number', $request->phone_number )
                ->orWhere( 'phone_number', ltrim( $request->phone_number, '0' ) );
        } )
        ->first();

        // Register OneSignal
        if ( !empty( $request->register_token ) ) {
            self::registerOneSignal( $user->id, $request->device_type, $request->register_token );
        }

        $token = $user->createToken( 'user_token' )->plainTextToken;
        $user->token = $token;

        return response()->json( [
            'message' => __( 'user.login_success' ),
            'message_key' => 'login_success',
            'data' => $user,
            'token' => $token
        ] );
    }

    private static function registerOneSignal( $user_id, $device_type, $register_token ) {

        UserDevice::updateOrCreate(
            [ 'user_id' => $user_id, 'device_type' => 1 ],
            [ 'register_token' => $register_token ]
        );
    }

    public static function loginUserSocial( $request ) {

        $request->validate( [
            'identifier' => [ 'required', function( $attributes, $value, $fail ) {
                $user = User::where( 'email', $value )->where( 'is_social_account', 0 )->first();
                if ( $user ) {
                    $fail( __( 'Email has been Registered' ) );
                }
                $userSocial = UserSocial::where( 'identifier', $value )->first();
                if ( $userSocial ) {
                    if ( $userSocial->platform != request( 'platform' ) ) {
                        $fail( __( 'Email has been registered in other platform' ) );
                    }
                }
            } ],
            'email' => [ 'sometimes', function( $attributes, $value, $fail ) {
                $user = User::where( 'email', $value )->where( 'is_social_account', 0 )->first();
                if ( $user ) {
                    $fail( __( 'Email has been Registered' ) );
                }
            } ],
            'platform' => 'required|in:1,2,3',
            'device_type' => 'required|in:1,2,3',
        ] );

        $userSocial = UserSocial::where( 'identifier', $request->identifier )->firstOr( function() use ( $request )  {

            \DB::beginTransaction();

            try {
                $createUser = User::create( [
                    'username' => null,
                    'email' => $request->email,
                    'country_id' => 136,
                    'phone_number' => null,
                    'is_social_account' => 1,
                    'invitation_code' => strtoupper( \Str::random( 6 ) ),
                    'referral_id' => null,
                    'referral_structure' => '-',
                    'password' => Hash::make( $request->identifier ),
                ] );

                $createUserSocial = UserSocial::create( [
                    'platform' => request( 'platform' ),
                    'identifier' => request( 'identifier' ),
                    'uuid' => $createUser->id,
                    'user_id' => $createUser->id,
                ] );

                for ( $i = 1; $i <= 2; $i++ ) {
                    $userWallet = Wallet::create( [
                        'user_id' => $createUser->id,
                        'type' => $i,
                        'balance' => 0,
                    ] );
                }

                $registerBonus = Option::getRegisterBonusSettings();

                if ( $registerBonus ) {
                    WalletService::transact( $userWallet, [
                        'amount' => $registerBonus->option_value,
                        'remark' => 'Register Bonus',
                        'type' => 2,
                        'transaction_type' => 20,
                    ] );
                }
    
                // assign referral bonus
                $referralBonus = Option::getReferralBonusSettings();
                $referral = User::where( 'invitation_code', $request->invitation_code )->first();

                if( $referral && $registerBonus){
    
                    $referralWallet = $referral->wallets->where('type',1)->first();
    
                    if( $referralWallet ) {
                        WalletService::transact( $referralWallet, [
                            'amount' => $referralBonus->option_value,
                            'remark' => 'Register Bonus',
                            'type' => $referralWallet->type,
                            'transaction_type' => 22,
                        ] );
                    }
                }

                self::createUserNotification(
                    $createUser->id,
                    'notification.register_success',
                    'notification.register_success_content',
                    'register',
                    'home'
                );
    
                // Register OneSignal
                if ( !empty( $request->register_token ) ) {
                    self::registerOneSignal( $createUser->id, $request->device_type, $request->register_token );
                }
    
                return $createUserSocial;
    
            } catch ( \Throwable $th ) {
    
                \DB::rollBack();
                abort( 500, $th->getMessage() . ' in line: ' . $th->getLine() );
            }
        } );

        \DB::commit();

        $user = User::find( $userSocial->user_id );

        // Register OneSignal
        if ( !empty( $request->register_token ) ) {
            self::registerOneSignal( $user->id, $request->device_type, $request->register_token );
        }

        return response()->json( [ 'data' => $user, 'token' => $user->createToken( 'x_api' )->plainTextToken ] );
    }

    public static function getUser( $request, $filterClientCode ) {

        $user = User::find( auth()->user()->id );

        if ( $user ) {
            $user->makeHidden( [
                'status',
                'updated_at',
            ] );

            $user->profile_picture_path = $user->profile_picture_path_new;
            $user->profile_picture = $user->profile_picture_path_new;
            $user->iso_code = $user->nationalityInfo ? $user->nationalityInfo->symbol : 'MY';
        }
    
        // If user not found, return early with error response
        if (!$user) {
            return response()->json([
                'message' => __('user.user_not_found'),
                'message_key' => 'get_user_failed',
                'data' => null,
            ]);
        }
    
        $wallets = Wallet::where('user_id', $user->id)->get();

        $user->total_bookings = CourtBookingGroup::where('user_id', $user->id)->count();
        $user->total_credits  = Helper::numberFormatNoComma($wallets->where('type', 2)->first()?->balance ?? 0, 2);
        $user->total_points   = Helper::numberFormatNoComma($wallets->where('type', 1)->first()?->balance ?? 0, 2);

        // Success response
        return response()->json([
            'message' => '',
            'message_key' => 'get_user_success',
            'data' => $user,
        ]);
    }

    public static function updateUserApi( $request ) {

        $validator = Validator::make( $request->all(), [
            'fullname' => [ 'required' ],
            'nationality' => [ 'nullable', 'exists:nationalities,id' ],
            'email' => [ 'required', 'email', 'unique:users,email,' . auth()->user()->id, ],
            'to_remove' => ['nullable', 'in:1,2'],
            'profile_picture' => [ 'nullable', 'file', 'mimes:jpg,png,jpeg' ],
        ] );

        $attributeName = [
            'username' => __( 'user.username' ),
            'date_of_birth' => __( 'user.date_of_birth' ),
            'email' => __( 'user.email' ),
            'first_name' => __( 'user.first_name' ),
            'last_name' => __( 'user.last_name' ),
            'fullname' => __( 'user.fullname' ),
            'phone_number' => __( 'user.phone_number' ),
            'nationality' => __( 'user.nationality' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        $updateUser = User::find( auth()->user()->id );
        $updateUser->email = $request->email;
        $updateUser->fullname = $request->fullname;
        $updateUser->nationality = $request->nationality;
        $updateUser->date_of_birth = $request->date_of_birth;

        if ( $request->to_remove == 1 && $updateUser->profile_picture ) {
            Storage::disk( 'public' )->delete( $updateUser->profile_picture );
            $updateUser->profile_picture = null;
        }

        if( $request->file( 'profile_picture' ) ) {
            
            if( $updateUser->profile_picture  ) {
                Storage::disk( 'public' )->delete( $updateUser->profile_picture );
            }

            $updateUser->profile_picture = $request->file( 'profile_picture' )->store( 'users/' . $updateUser->id, [ 'disk' => 'public' ] );
        }

        $updateUser->save();

        self::createUserNotification(
            $updateUser->id,
            'notification.profile_updated_title',
            'notification.profile_updated_content',
            null,
            'profile_updated'
        );

        $updateUser->profile_picture_path = $updateUser->profile_picture_path_new;
        $updateUser->profile_picture = $updateUser->profile_picture_path_new;

        return response()->json( [
            'message' => __( 'user.user_updated' ),
            'message_key' => 'update_user_success',
            'data' => $updateUser
        ] );
    }

    public static function updateUserPassword( $request ) {

        $validator = Validator::make( $request->all(), [
            'old_password' => [ 'required', Password::min( 8 ), function( $attribute, $value, $fail ) {
                if ( !Hash::check( $value, auth()->user()->password ) ) {
                    $fail( __( 'user.old_password_not_match' ) );
                }
            } ],
            'password' => [ 'required', Password::min( 8 ), 'confirmed' ],
        ] );

        $attributeName = [
            'old_password' => __( 'user.old_password' ),
            'password' => __( 'user.password' ),
            'password_confirmation' => __( 'user.password_confirmation' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $updateUser = User::find( auth()->user()->id );
        $updateUser->password = Hash::make( $request->password );
        $updateUser->save();

        self::createUserNotification(
            $updateUser->id,
            'notification.password_updated_title',
            'notification.password_updated_content',
            null,
            'password_updated'
        );

        return response()->json( [
            'message' => __( 'user.user_password_updated' ),
            'message_key' => 'update_user_password_success',
        ] );
    }

    public static function requestOtp( $request ) {

        DB::beginTransaction();

        $callingCode = $request->calling_code ?? '+60';

        if ( $request->request_type == 1 ) {

            $action = 'register';

            // If identifier exists, it's a resend request
            if( $request->identifier ){
                try {
                    $request->merge( [
                        'identifier' => Crypt::decryptString( $request->identifier ),
                    ] );

                    $tmpUser = TmpUser::find( $request->identifier );

                    if($tmpUser->status == 10){
                        return response()->json( [
                            'message' => __( 'validation.header_message' ),
                            'errors' => [
                                'identifier' => [
                                    __( 'user.invalid_otp' ),
                                ],
                            ]
                        ], 422 );
                    }

                } catch ( \Throwable $th ) {
                    DB::rollBack();
                    return response()->json( [
                        'message' => __( 'validation.header_message' ),
                        'errors' => [
                            'identifier' => [
                                __( 'user.invalid_otp' ),
                            ],
                        ]
                    ], 422 );
                }
                $action = 'resend';
            } else {
                // Only validate full registration fields when it's NOT a resend (no identifier)
                $validator = Validator::make( $request->all(), [
                    'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {

                        if ( mb_substr( $value, 0, 1 ) == 0 ) {
                            $value = mb_substr( $value, 1 );
                        }

                        $user = User::where( 'phone_number', $value )
                            ->orWhere('phone_number', ltrim($value, '0'))
                            ->first();

                        if ( $user ) {
                            $fail( __( 'validation.unique' ) );
                        }
                    } ],
                    'email' => [ 'nullable', 'unique:users,email', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
                    'fullname' => [ 'required' ],
                    'password' => [ 'required', 'confirmed', Password::min( 8 ) ],
                    'request_type' => [ 'required', 'in:1' ],
                ] );

                $attributeName = [
                    'phone_number' => __( 'user.phone_number' ),
                    'email' => __( 'user.email' ),
                    'fullname' => __( 'user.fullname' ),
                    'password' => __( 'user.password' ),
                    'request_type' => __( 'user.request_type' ),
                ];

                foreach ( $attributeName as $key => $aName ) {
                    $attributeName[$key] = strtolower( $aName );
                }

                $validator->setAttributeNames( $attributeName )->validate();
            }

            try {

                $createTmpUser = Helper::requestOtp( $action, [
                    'calling_code' => $request->calling_code,
                    'phone_number' => $request->phone_number,
                    'email' => $request->email,
                    'request_type' => $request->request_type,
                    'identifier' => $request->identifier ? $request->identifier : null,
                ] );
    
                DB::commit();
                $phoneNumber  = $request->calling_code . $request->phone_number;
                $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $request->phone_number );

                // Mail::to( $request->email )->send(new OtpMail( $createTmpUser ));
                // self::sendSMS( false, '+60' . $normalizedPhone, $createTmpUser['otp_code'], '' );
                
                return response()->json( [
                    'message' => $request->calling_code . $request->phone_number . ' request otp success',
                    'message_key' => 'request_otp_success',
                    'data' => [
                        'otp_code' => '#DEBUG - ' . $createTmpUser['otp_code'],
                        'identifier' => $createTmpUser['identifier'],
                        'title' => $createTmpUser ? __( 'user.otp_email_success' ) : '',
                        'note' => $createTmpUser ? __( 'user.otp_email_success_note', [ 'title' => $phoneNumber ] ) : ''
                    ]
                ] );
    
            } catch ( \Throwable $th ) {
                DB::rollBack();
                abort( 500, $th->getMessage() . ' in line: ' . $th->getLine() );
            }
    
        } else {
            // Forgot password OTP
            $action = 'forgot_password';
            $identifier = null;

            if( $request->identifier ){
                try {
                    $request->merge( [
                        'identifier' => Crypt::decryptString( $request->identifier ),
                    ] );

                    $otpAction = OtpAction::find( $request->identifier );

                    if(!$otpAction){
                        return response()->json( [
                            'message' => __( 'validation.header_message' ),
                            'errors' => [
                                'identifier' => [
                                    __( 'user.invalid_otp' ),
                                ],
                            ]
                        ], 422 );
                    }
                } catch ( \Throwable $th ) {
                    DB::rollBack();
                    return response()->json( [
                        'message' => __( 'validation.header_message' ),
                        'errors' => [
                            'identifier' => [
                                __( 'user.invalid_otp' ),
                            ],
                        ]
                    ], 422 );
                }
                $action = 'resend_forget_password';
                $identifier = $request->identifier;
            } else {
                // Only validate phone_number when it's NOT a resend (no identifier)
                $validator = Validator::make( $request->all(), [
                    'phone_number' => [ 'required', 'digits_between:8,15' ],
                    'request_type' => [ 'required', 'in:2' ],
                ] );

                $attributeName = [
                    'phone_number' => __( 'user.phone_number' ),
                    'request_type' => __( 'user.request_type' ),
                ];

                foreach ( $attributeName as $key => $aName ) {
                    $attributeName[$key] = strtolower( $aName );
                }

                $validator->setAttributeNames( $attributeName )->validate();
            }

            try {
                if( !$request->identifier ){

                    $userInput = $request->phone_number;
                    $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $userInput );

                    $existingUser = User::where( 'phone_number', $normalizedPhone )->where( 'calling_code', $callingCode )->first();

                    if ( $existingUser ) {
                        $forgotPassword = Helper::requestOtp( $action, [
                            'id' => $existingUser->id,
                            'phone_number' => $existingUser->phone_number,
                            'calling_code' => $existingUser->calling_code,
                            'identifier' => $identifier,
                        ] );

                        // Mail::to( $existingUser->email )->send(new OtpMail( $forgotPassword ));
                        // $mailable = new \App\Mail\OtpMail( $forgotPassword );
                        // $mailable->sendWithBrevo( $existingUser->email, $existingUser->email ?? '' );

                        DB::commit();

                        return response()->json( [
                            'message' => 'Reset Password Otp Success',
                            'message_key' => 'request_otp_success',
                            'data' => [
                                'title' => $forgotPassword ? __( 'user.otp_email_success' ) : '',
                                'note' => $forgotPassword ? __( 'user.otp_email_success_note' ) : '',
                                'identifier' => $forgotPassword['identifier'],
                                'otp_code' => '#DEBUG - ' . $forgotPassword['otp_code'],
                            ]
                        ] );
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'message' => __( 'validation.header_message' ),
                            'errors' => [
                                'phone_number' => [
                                    __('user.user_not_found'),
                                ],
                            ]
                        ], 422);
                    }
                }else{
                    
                    $forgotPassword = Helper::requestOtp( $action, [
                        'identifier' => $identifier,
                    ] );

                    // Mail::to( $existingUser->email )->send(new OtpMail( $forgotPassword ));
                    // $mailable = new \App\Mail\OtpMail( $forgotPassword );
                    // $mailable->sendWithBrevo( $existingUser->email, $existingUser->email ?? '' );

                    DB::commit();

                    return response()->json( [
                        'message' => 'Reset Password Otp Success',
                        'message_key' => 'request_otp_success',
                        'data' => [
                            'title' => $forgotPassword ? __( 'user.otp_email_success' ) : '',
                            'note' => $forgotPassword ? __( 'user.otp_email_success_note' ) : '',
                            'identifier' => $forgotPassword['identifier'],
                            'otp_code' => '#DEBUG - ' . $forgotPassword['otp_code'],
                        ]
                    ] );
                }
            } catch ( \Throwable $th ) {
                DB::rollBack();
                abort( 500, $th->getMessage() . ' in line: ' . $th->getLine() );
            }
        }
    }

    public static function createEnquiryMail( $request ) {

        $validator = Validator::make( $request->all(), [
            'fullname' => [ 'nullable' ],
            'email' => [ 'required' ],
            'phone_number' => [ 'required' ],
            'message' => [ 'nullable' ],
        ] );

        $attributeName = [
            'fullname' => __( 'user.fullname' ),
            'email' => __( 'user.email' ),
            'phone_number' => __( 'user.phone_number' ),
            'message' => __( 'user.message' ),
        ];
        
        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $mailContent = MailContent::create( [
                'fullname' => $request->fullname,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'remarks' =>$request->message,
            ] );
            
            DB::commit();

            // Mail::to( config( 'services.mail.receiver' ) )->send(new EnquiryEmail( $mailContent ));
            
            return response()->json( [
                'data' => [
                    'message_key' => 'Enquiry Received!',
                    'message_key' => 'enquiry_received',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_enquiry_failed',
            ], 500 );
        }
    }

    public static function deleteVerification($request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ], [
            'password.required' => __('The password field is required.'),
        ]);
    
        $attributeName = [
            'password' => __('user.password'),
        ];
    
        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }
    
        $validator->setAttributeNames($attributeName)->validate();
    
        try {
            // Assume the authenticated user is making this request
            $currentUser = auth()->user();
    
            if (!$currentUser) {
                return response()->json([
                    'message' => __('user.not_authenticated'),
                    'message_key' => 'user_not_authenticated',
                    'data' => null,
                ], 401);
            }
    
            // Verify password
            if (!Hash::check($request->password, $currentUser->password)) {
                return response()->json([
                    'message' => __('user.invalid_password'),
                    'message_key' => 'invalid_password',
                    'errors' => [
                        'user' => __('user.invalid_password'),
                    ]
                ], 422);
            }
    
            return response()->json([
                'message' => __('user.password_verified'),
                'message_key' => 'account_deleted',
                'data' => null,
            ]);
    
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }

    public static function deleteConfirm( $request ) {

        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ], [
            'password.required' => __('The password field is required.'),
        ]);
    
        $attributeName = [
            'password' => __('user.password'),
        ];
    
        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }
    
        $validator->setAttributeNames($attributeName)->validate();
    
        try {
            // Assume the authenticated user is making this request
            $currentUser = auth()->user();
    
            if (!$currentUser) {
                return response()->json([
                    'message' => __('user.not_authenticated'),
                    'message_key' => 'user_not_authenticated',
                    'data' => null,
                ], 401);
            }
    
            // Verify password
            if (!Hash::check($request->password, $currentUser->password)) {
                return response()->json([
                    'message' => __('user.invalid_password'),
                    'message_key' => 'invalid_password',
                    'errors' => [
                        'user' => __('user.invalid_password'),
                    ]
                ], 422);
            }
    
            DB::beginTransaction();
    
            $currentUser->status = 20;
            $currentUser->save();
            DB::commit();
    
            return response()->json([
                'message' => __('user.account_deleted'),
                'message_key' => 'account_deleted',
                'data' => null,
            ]);
    
        } catch (\Throwable $th) {
            DB::rollBack();
    
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }
    

    public static function getNotifications( $request ) {

        $notifications = UserNotification::select(
            'user_notifications.*',
            // DB::raw( '( SELECT COUNT(*) FROM user_notification_seens AS a WHERE a.user_notification_id = user_notifications.id AND a.user_id = ' .request()->user()->id. ' ) as is_read' )
            DB::raw( 'CASE WHEN user_notification_seens.id > 0 THEN 1 ELSE 0 END as is_read' )
        )->where( function( $query ) {
            $query->where( 'user_notifications.status', 10 );
            $query->where( 'user_notifications.is_broadcast', 10 );
            $query->orWhere( 'user_notification_users.user_id', auth()->user()->id );
        } );

        $notifications->leftJoin( 'user_notification_users', function( $query ) {
            $query->on( 'user_notification_users.user_notification_id', '=', 'user_notifications.id' );
            // $query->on( 'user_notification_users.user_id', '=', DB::raw( auth()->user()->id ) );
        } );

        // $notifications->leftJoin( 'user_notification_seens', 'user_notification_seens.user_notification_id', '=', 'user_notifications.id' );
        $notifications->leftJoin( 'user_notification_seens', function( $query ) {
            $query->on( 'user_notification_seens.user_notification_id', '=', 'user_notifications.id' );
            $query->on( 'user_notification_seens.user_id', '=', DB::raw( auth()->user()->id ) );
        } );

        $notifications->when( !empty( $request->type ), function( $query ) {
            return $query->where( 'user_notifications.type', request( 'type' ) );
        } );

        $notifications->when( $request->is_read != '' , function( $query ) {
            if ( request( 'is_read' ) == 0 ) {
                return $query->whereNull( 'user_notification_seens.id' );
            } else {
                return $query->where( 'user_notification_seens.id', '>', 0 );
            }
        } );

        $notifications->when( $request->notification != '' , function( $query ) use( $request ) {
            return $query->where( 'user_notifications.id', $request->notification );
        } );

        $notifications->orderBy( 'user_notifications.created_at', 'DESC' );

        $notifications = $notifications->simplePaginate( empty( $request->per_page ) ? 100 : $request->per_page );

        return response()->json( $notifications );
    }

    public static function getNotification( $request ) {

        $notification = UserNotification::find( $request->notification );

        return response()->json( [
            'data' => $notification,
        ] );
    }

    public static function updateNotificationSeen( $request ) {

        $notification = UserNotification::find( $request->notification );
        if ( !$notification ) {
            return response()->json( [
                'message' => '',
            ] );
        }

        UserNotificationSeen::firstOrCreate( [
            'user_notification_id' => $request->notification,
            'user_id' => auth()->user()->id,
        ], [
            'user_notification_id' => $request->notification,
            'user_id' => auth()->user()->id,
        ] );

        return response()->json( [
            'message' => __( 'notification.notification_seen' ),
        ] );
    }

    public static function createUserNotification( $user, $title = null, $content = null, $slug = null, $key = null ){

        $createNotification = UserNotification::create( [
            'type' => 2,
            'title' => $title,
            'content' => $content,
            'url_slug' => $slug ? \Str::slug( $slug ) : null,
            'system_title' => NULL,
            'system_content' => NULL,
            'system_data' => NULL,
            'meta_data' => NULL,
            'key' => $key,
        ] );

        $createUserNotificationUser = UserNotificationUser::create( [
            'user_notification_id' => $createNotification->id,
            'user_id' => $user,
        ] );

    }

    private static function sendNotification( $user, $key, $message ) {

        $messageContent = array();

        $messageContent['key'] = $key;
        $messageContent['id'] = $user->id;
        $messageContent['message'] = $message;

        Helper::sendNotification( $user->user_id, $messageContent );
        
    }
    private static function sendSMS( $customMessage = false, $mobile, $otp, $message = '' ) {

        $url = config( 'services.sms.sms_url' );
        $builtMessage = $customMessage ? $message : 'Your One Time Password (OTP) is '.$otp.'. This OTP expires in 30 minutes.';
        $encodedMessage = rawurlencode($builtMessage);

        $request = array(
            'un' => config( 'services.sms.username' ),
            'pwd' => config( 'services.sms.password' ),
            'dstno' => $mobile,
            'msg' => $encodedMessage,
            'type' => 1,
            'agreedterm'=> 'YES',
        );

        $sendSMS = Helper::curlGet( $url . '?' . http_build_query( $request ) );

        ApiLog::create( [
            'url' => $url . '?' . http_build_query( $request ),
            'method' => 'GET',
            'raw_response' => json_encode( $sendSMS ),
        ] );

    }

    // ========== EMAIL-BASED AUTHENTICATION METHODS ==========

    public static function checkEmail( $request ) {

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'string', 'max:255'],
        ]);

        $attributeName = [
            'email' => __( 'user.email' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $existingUser = User::where( 'email', $request->email )->first();

            if ( $existingUser ) {
               
                return response()->json([
                    'message' => __('user.user_exist'),
                    'message_key' => 'user_exist',
                    'data' => [
                        'user_exists' => true,
                        'email' => $request->email,
                    ]
                ]);

            } else {
                return response()->json([
                    'message' => __('user.user_not_found'),
                    'message_key' => 'user_not_found',
                    'data' => [
                        'user_exists' => false,
                        'email' => $request->email,
                    ]
                ]);
            }

        } catch ( \Throwable $th ) {

            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'server_error',
            ], 500);
        }
    }

    public static function requestOtpEmail( $request ) {

        $rules = [
            'request_type' => [ 'required', 'in:1,2' ],
            'email' => [ 'required', 'email', 'string', 'max:255' ],
        ];
        
        if ($request->request_type == 2) {
            $rules['email'][] = 'exists:users,email';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        $attributeName = [
            'request_type' => __( 'user.request_type' ),
            'email'        => __( 'user.email' ),
        ];
        
        $validator->setAttributeNames($attributeName)->validate();        
    
        DB::beginTransaction();
    
        if ( $request->request_type == 1 ) {
    
            // Registration OTP
            $validator = Validator::make( $request->all(), [
                'email' => [ 'required', 'email', 'string', 'max:255', function( $attribute, $value, $fail ) {
                    if ( User::where( 'email', $value )->exists() ) {
                        $fail( __( 'validation.unique', [ 'attribute' => __( 'user.email' ) ] ) );
                    }
                } ],
            ] );

            $validator->setAttributeNames( $attributeName )->validate();

            try {

                $createTmpUser = Helper::requestOtp( 'register', [
                    'email' => $request->email,
                ] );

                // Mail::to( $request->email )->send(new OtpMail( $createTmpUser ));
    
                $mailable = new \App\Mail\OtpMail( $createTmpUser );
                $mailable->sendWithBrevo( $request->email, $request->email ?? '' );

                DB::commit();
                
                return response()->json( [
                    'message' => $request->email . ' request otp success',
                    'message_key' => 'request_otp_success',
                    'data' => [
                        'otp_code' => '#DEBUG - ' . $createTmpUser['otp_code'],
                        'identifier' => $createTmpUser['identifier'],
                        'title' => $createTmpUser ? __( 'user.otp_email_success' ) : '',
                        'note' => $createTmpUser ? __( 'user.otp_email_success_note', [ 'title' => $request->email ] ) : ''
                    ]
                ] );
    
            } catch ( \Throwable $th ) {
                DB::rollBack();
                return response()->json([
                    'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                    'message_key' => 'server_error',
                ], 500);
            }
    
        } else {
            // Forgot password OTP
            $existingUser = User::where( 'email', $request->email )->first();

            if ( $existingUser ) {
                $forgotPassword = Helper::requestOtp( 'forgot_password', [
                    'id' => $existingUser->id,
                    'email' => $existingUser->email,
                ] );

                // Mail::to( $existingUser->email )->send(new OtpMail( $forgotPassword ));
                $mailable = new \App\Mail\OtpMail( $forgotPassword );
                $mailable->sendWithBrevo( $existingUser->email, $existingUser->email ?? '' );

                DB::commit();
    
                return response()->json( [
                    'message' => 'Reset Password Otp Success',
                    'message_key' => 'request_otp_success',
                    'data' => [
                        'title' => $forgotPassword ? __( 'user.otp_email_success' ) : '',
                        'note' => $forgotPassword ? __( 'user.otp_email_success_note' ) : '',
                        'identifier' => $forgotPassword['identifier'],
                        'otp_code' => '#DEBUG - ' . $forgotPassword['otp_code'],
                    ]
                ] );
            } else {
                return response()->json([
                    'message' => __('user.user_not_found'),
                    'message_key' => 'get_user_failed',
                ], 404);
            }
        }
    }

    public static function resendOtpEmail( $request ) {

        $validator = Validator::make( $request->all(), [
            'identifier' => [ 'required' ],
            'request_type' => [ 'required', 'in:1,2' ],
        ] );

        $attributeName = [
            'identifier' => __( 'user.identifier' ),
            'request_type' => __( 'user.request_type' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $request->merge( [
            'identifier' => Crypt::decryptString( $request->identifier ),
        ] );
            
        try {

            if ( $request->request_type == 1 ) {

                $currentTmp = TmpUser::find( $request->identifier );
                
                if (!$currentTmp) {
                    return response()->json([
                        'message' => __('user.invalid_identifier'),
                        'message_key' => 'invalid_identifier',
                    ], 400);
                }

                $updateTmpUser = Helper::requestOtp( 'resend', [
                    'identifier' => $request->identifier,
                    'email' => $currentTmp->email,
                    'request_type' => $request->request_type,
                    'title' => __( 'user.otp_email_success' ),
                    'note' => __( 'user.otp_email_success_note', [ 'title' => $currentTmp->email ] ),
                ] );

                // Mail::to( $currentTmp->email )->send(new OtpMail( $updateTmpUser ));
                $mailable = new \App\Mail\OtpMail( $updateTmpUser );
                $mailable->sendWithBrevo( $currentTmp->email, $currentTmp->email ?? '' );

                return response()->json( [
                    'message' => 'resend_otp_success',
                    'message_key' => 'resend_otp_success',
                    'data' => [
                        'otp_code' => '#DEBUG - ' . $updateTmpUser['otp_code'],
                        'identifier' => $updateTmpUser['identifier'],
                        'title' => $updateTmpUser ? __( 'user.otp_email_success' ) : '',
                        'note' => $updateTmpUser ? __( 'user.otp_email_success_note', [ 'title' => $currentTmp->email ] ) : ''
                    ]
                ] );
            }else{
                $currentTmp = OtpAction::find( $request->identifier );
                
                if (!$currentTmp) {
                    return response()->json([
                        'message' => __('user.invalid_identifier'),
                        'message_key' => 'invalid_identifier',
                    ], 400);
                }

                $updateTmpUser = Helper::requestOtp( 'resend', [
                    'identifier' => $request->identifier,
                    'email' => $currentTmp->email,
                    'request_type' => $request->request_type,
                    'title' => __( 'user.otp_email_success' ),
                    'note' => __( 'user.otp_email_success_note', [ 'title' => $currentTmp->email ] ),
                ] );

                // Mail::to( $currentTmp->email )->send(new OtpMail( $updateTmpUser ));
                $mailable = new \App\Mail\OtpMail( $updateTmpUser );
                $mailable->sendWithBrevo( $currentTmp->email, $currentTmp->email ?? '' );

                return response()->json( [
                    'message' => 'resend_otp_success',
                    'message_key' => 'resend_otp_success',
                    'data' => [
                        'otp_code' => '#DEBUG - ' . $updateTmpUser['otp_code'],
                        'identifier' => $updateTmpUser['identifier'],
                        'title' => $updateTmpUser ? __( 'user.otp_email_success' ) : '',
                        'note' => $updateTmpUser ? __( 'user.otp_email_success_note', [ 'title' => $currentTmp->email ] ) : ''
                    ]
                ] );
            }

        } catch ( \Throwable $th ) {
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'server_error',
            ], 500);
        }
    }

    public static function registerUserEmail( $request ) {

        $validator = Validator::make( $request->all(), [
            'identifier' => [ 'required' ],
            'email' => [ 'required', 'email', 'string', 'max:255', 'unique:users' ],
            'otp_code' => [ 'required', 'string' ],
            'password' => [ 'required', 'string', 'min:8', 'confirmed' ],
            'fullname' => [ 'nullable', 'string', 'max:255' ],
            'invitation_code' => [ 'nullable', 'string' ],
            'date_of_birth' => ['nullable']
        ] );

        $attributeName = [
            'identifier' => __( 'user.identifier' ),
            'email' => __( 'user.email' ),
            'otp_code' => __( 'user.otp_code' ),
            'password' => __( 'user.password' ),
            'name' => __( 'user.name' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $verifyOtp = Helper::verifyOtp( $request->identifier, $request->otp_code, $request->email );

            if ( !$verifyOtp || !$verifyOtp['success'] ) {
                return response()->json( [
                    'message' => __( 'user.invalid_otp' ),
                    'message_key' => 'invalid_otp',
                ], 400 );
            }

            // Get the TmpUser data from verification result
            $tmpUser = $verifyOtp['data'];
            $tmpUser->status = 10;
            $tmpUser->save();

            $user = User::create([
                'email' => $request->email,
                'fullname' => $request->fullname,
                'date_of_birth' => $request->date_of_birth,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            $token = $user->createToken( 'user_token' )->plainTextToken;
            $user->token = $token;

            DB::commit();

            return response()->json([
                'message' => __( 'user.register_success' ),
                'message_key' => 'register_user_success',
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'fullname' => $user->fullname,
                ]
            ]);

        } catch ( \Throwable $th ) {
            DB::rollBack();
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'server_error',
            ], 500);
        }
    }

    public static function loginUserEmail( $request ) {

        $validator = Validator::make( $request->all(), [
            'email' => [ 'required', 'email', 'string' ],
            'password' => [ 'required', 'string' ],
        ] );

        $attributeName = [
            'email' => __( 'user.email' ),
            'password' => __( 'user.password' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => __('auth.failed'),
                    'message_key' => 'invalid_credentials',
                ], 401);
            }

            $token = $user->createToken( 'user_token' )->plainTextToken;
            $user->token = $token;

            return response()->json([
                'message' => __( 'user.login_success' ),
                'message_key' => 'login_user_success',
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);

        } catch ( \Throwable $th ) {
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'server_error',
            ], 500);
        }
    }

    public static function forgotPasswordEmail( $request ) {

        $validator = Validator::make( $request->all(), [
            'email' => [ 'required', 'email', 'string' ],
        ] );

        $attributeName = [
            'email' => __( 'user.email' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        try {

            $existingUser = User::where( 'email', $request->email )->first();

            if ( $existingUser ) {
                $forgotPassword = Helper::requestOtp( 'forgot_password', [
                    'id' => $existingUser->id,
                    'email' => $existingUser->email,
                ] );

                // Mail::to( $existingUser->email )->send(new OtpMail( $forgotPassword ));
                $mailable = new \App\Mail\OtpMail( $forgotPassword );
                $mailable->sendWithBrevo( $existingUser->email, $existingUser->email ?? '' );

                return response()->json( [
                    'message' => 'Reset Password Otp Success',
                    'message_key' => 'request_otp_success',
                    'data' => [
                        'title' => $forgotPassword ? __( 'user.otp_email_success' ) : '',
                        'note' => $forgotPassword ? __( 'user.otp_email_success_note' ) : '',
                        'identifier' => $forgotPassword['identifier'],
                        'otp_code' => '#DEBUG - ' . $forgotPassword['otp_code'],
                    ]
                ] );
            } else {
                return response()->json([
                    'message' => __('user.user_not_found'),
                    'message_key' => 'get_user_failed',
                ], 404);
            }

        } catch ( \Throwable $th ) {
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'server_error',
            ], 500);
        }
    }

    public static function resetPasswordEmail( $request ) {

        $validator = Validator::make( $request->all(), [
            'identifier' => [ 'required' ],
            'otp_code' => [ 'required', 'string' ],
            'password' => [ 'required', 'string', 'min:8', 'confirmed' ],
        ] );

        $attributeName = [
            'identifier' => __( 'user.identifier' ),
            'otp_code' => __( 'user.otp_code' ),
            'password' => __( 'user.password' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $verifyOtp = Helper::verifyOtp( $request->identifier, $request->otp_code );

            if ( !$verifyOtp || !$verifyOtp['success'] ) {
                return response()->json( [
                    'message' => __( 'user.invalid_otp' ),
                    'message_key' => 'invalid_otp',
                ], 400 );
            }

            // Get the TmpUser data from verification result
            $tmpUser = $verifyOtp['data'];
            if (!$tmpUser || !$tmpUser->user_id) {
                return response()->json([
                    'message' => __('user.invalid_request'),
                    'message_key' => 'invalid_request',
                ], 400);
            }

            $user = User::find( $tmpUser->user_id );
            if (!$user) {
                return response()->json([
                    'message' => __('user.user_not_found'),
                    'message_key' => 'user_not_found',
                ], 404);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            DB::commit();

            return response()->json([
                'message' => __( 'user.password_reset_success' ),
                'message_key' => 'password_reset_success',
            ]);

        } catch ( \Throwable $th ) {
            DB::rollBack();
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'server_error',
            ], 500);
        }
    }

    public static function verifyResetOtp( $request ) {

        $validator = Validator::make( $request->all(), [
            'identifier' => ['required'],
            'otp_code'   => ['required', 'string'],
        ]);
    
        $attributeName = [
            'identifier' => __('user.identifier'),
            'otp_code'   => __('user.otp_code'),
        ];
    
        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }
    
        $validator->setAttributeNames($attributeName)->validate();
    
        try {
    
            $verifyOtp = Helper::verifyOtp($request->identifier, $request->otp_code);
    
            if (!$verifyOtp || !$verifyOtp['success']) {
                return response()->json([
                    'message'     => __('user.invalid_otp'),
                    'message_key' => 'invalid_otp',
                ], 400);
            }

            // Get the TmpUser data from verification result
            $tmpUser = $verifyOtp['data'];
            if (!$tmpUser) {
                return response()->json([
                    'message'     => __('user.invalid_request'),
                    'message_key' => 'invalid_request',
                ], 400);
            }
    
            return response()->json([
                'message'     => __('user.otp_verified'),
                'message_key' => 'otp_verified',
            ]);
    
        } catch (\Throwable $th) {
            return response()->json([
                'message'     => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'server_error',
            ], 500);
        }
    }

    /**
     * Send contact form email to admin
     *
     * @param Request $request
     * @return JsonResponse
     */
    public static function sendContactFormApi($request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'phone_number' => 'required|string',
            'calling_code' => 'required|string',
            'location' => 'optional|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $name = $request->name;
            $email = $request->email;
            $phoneNumber = $request->calling_code . $request->phone_number;
            $location = $request->location;
            $subject = $request->subject;
            $message = $request->message;

            // Prepare HTML email content
            $htmlContent = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #d9ae80; color: white; padding: 20px; text-align: center; }
                        .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
                        .field { margin-bottom: 15px; }
                        .label { font-weight: bold; color: #555; }
                        .value { margin-top: 5px; }
                        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Contact Form Submission</h2>
                        </div>
                        <div class='content'>
                            <div class='field'>
                                <div class='label'>Name:</div>
                                <div class='value'>{$name}</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Email:</div>
                                <div class='value'>{$email}</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Phone Number:</div>
                                <div class='value'>{$phoneNumber}</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Location:</div>
                                <div class='value'>{$location}</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Subject:</div>
                                <div class='value'>{$subject}</div>
                            </div>
                            <div class='field'>
                                <div class='label'>Message:</div>
                                <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
                            </div>
                        </div>
                        <div class='footer'>
                            <p>This email was sent from the Ladang X contact form.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            // Send email to admin using Brevo
            $adminEmail = 'admin@xpark.com';
            $adminName = 'Ladang X Admin';
            $emailSubject = "Contact Form: {$subject}";

            $result = Helper::sendBrevoEmail(
                $adminEmail,
                $adminName,
                $emailSubject,
                $htmlContent
            );
            
            if ($result && isset($result['messageId'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contact form submitted successfully. We will get back to you soon.',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send email. Please try again later.',
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the contact form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public static function socialLogin( $request ) {

        $request->validate( [
            'identifier'      => 'required|string',
            'platform'        => 'required|in:1,2,3',
            'device_type'     => 'required|in:1,2,3',
            'email'           => [ 'nullable', 'email', 'string', function( $attribute, $value, $fail ) {
                if ( User::where( 'email', $value )->where( 'is_social_account', 0 )->exists() ) {
                    $fail( __( 'Email has been registered.' ) );
                }
            } ],
            'invitation_code' => 'nullable|string',
        ] );

        $userSocial = UserSocial::where( 'identifier', $request->identifier )
            ->where( 'platform', $request->platform )
            ->first();

        // Social account exists — log in
        if ( $userSocial ) {

            $user = User::find( $userSocial->user_id );

            if ( !$user || $user->status != 10 ) {
                return response()->json( [
                    'message'     => __( 'Account is inactive or not found.' ),
                    'message_key' => 'social_login_failed',
                ], 403 );
            }

            if ( !empty( $request->register_token ) ) {
                self::registerOneSignal( $user->id, $request->device_type, $request->register_token );
            }

            return response()->json( [
                'message_key' => 'social_login_success',
                'data'        => $user,
                'token'       => $user->createToken( 'x_api' )->plainTextToken,
            ] );
        }

        // Social account not found — register
        DB::beginTransaction();

        try {

            $createUser = User::create( [
                'username'           => null,
                'email'              => $request->email,
                'phone_number'       => null,
                'is_social_account'  => 1,
                'status'             => 10,
                'invitation_code'    => strtoupper( Str::random( 6 ) ),
                'referral_id'        => null,
                'referral_structure' => '-',
                'password'           => Hash::make( $request->identifier ),
            ] );

            UserSocial::create( [
                'platform'   => $request->platform,
                'identifier' => $request->identifier,
                'uuid'       => $createUser->id,
                'user_id'    => $createUser->id,
            ] );

            $registerBonus = Option::getRegisterBonusSettings();

            for ( $i = 1; $i <= 2; $i++ ) {
                $userWallet = Wallet::create( [
                    'user_id' => $createUser->id,
                    'type' => $i,
                    'balance' => 0,
                ] );
            }

            if ( $registerBonus ) {
                WalletService::transact( $userWallet, [
                    'amount' => $registerBonus->option_value,
                    'remark' => 'Register Bonus',
                    'type' => 2,
                    'transaction_type' => 20,
                ] );
            }
            
            self::createUserNotification(
                $createUser->id,
                'notification.register_success',
                'notification.register_success_content',
                'register',
                'home'
            );

            if ( !empty( $request->register_token ) ) {
                self::registerOneSignal( $createUser->id, $request->device_type, $request->register_token );
            }

            DB::commit();

            return response()->json( [
                'message_key' => 'social_register_success',
                'data'        => $createUser,
                'token'       => $createUser->createToken( 'x_api' )->plainTextToken,
            ] );

        } catch ( \Throwable $th ) {

            DB::rollBack();
            abort( 500, $th->getMessage() . ' in line: ' . $th->getLine() );
        }
    }

    public static function getUserQr( $request ) {

        $user = $request->user();

        // Encode the raw invitation code into the QR.
        // When you're ready to switch to a link, replace $payload with:
        // $payload = config('app.url') . '/register?ref=' . $user->invitation_code;
        $payload = $user->invitation_code;

        $options = new \chillerlan\QRCode\QROptions( [
            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'   => \chillerlan\QRCode\QRCode::ECC_H,
            'scale'      => 10,
            'imageBase64' => true,
        ] );

        $qrImage = ( new \chillerlan\QRCode\QRCode( $options ) )->render( $payload );

        return response()->json( [
            'invitation_code' => $user->invitation_code,
            'invitation_link' => config( 'app.url' ) . '/register?ref=' . $user->invitation_code,
            'qr_image'        => $qrImage,
        ] );
    }

}