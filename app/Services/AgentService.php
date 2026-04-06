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
    Agent,
    OtpAction,
    TmpAgent,
    MailContent,
    Wallet,
    Option,
    WalletTransaction,
    AgentNotification,
    AgentNotificationSeen,
    AgentNotificationAgent,
    AgentDevice,
    AgentSocial,
    FileManager,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class AgentService
{
    public static function allAgents( $request ) {

        $agent = Agent::select( 'agents.*' )->orderBy( 'created_at', 'DESC' );

        $filterObject = self::filter( $request, $agent );
        $agent = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'agent.0.column' ) != 0 ) {
            $dir = $request->input( 'agent.0.dir' );
            switch ( $request->input( 'agent.0.column' ) ) {
                case 1:
                    $agent->orderBy( 'created_at', $dir );
                    break;
                case 2:
                    $agent->orderBy( 'agentname', $dir );
                    break;
                case 3:
                    $agent->orderBy( 'email', $dir );
                    break;
            }
        }

        $agentCount = $agent->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $agents = $agent->skip( $offset )->take( $limit )->get();

        if ( $agents ) {
            $agents->append( [
                'encrypted_id',
                'profile_picture_path_new',
            ] );
        }

        $totalRecord = Agent::count();

        $data = [
            'agents' => $agents,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $agentCount : $totalRecord,
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

                $model->whereBetween( 'agents.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'agents.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
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

        if ( !empty( $request->last_name ) ) {
            $model->where( 'last_name', 'LIKE', '%' . $request->last_name . '%' );
            $filter = true;
        }

        if ( !empty( $request->agentname ) ) {
            $model->where( 'agentname', 'LIKE', '%' . $request->agentname . '%' );
            $filter = true;
        }

        if ( !empty( $request->email ) ) {
            $model->where( 'email', 'LIKE', '%' . $request->email . '%' );
            $filter = true;
        }

        if ( !empty( $request->phone_number ) ) {
            $agentInput = $request->phone_number;
        
            $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $agentInput );
        
            $model->where( function ( $query ) use ( $normalizedPhone, $agentInput ) {
                $query->where( 'agents.phone_number', 'LIKE', "%$normalizedPhone%" );
            } );
        
            $filter = true;
        }

        if ( !empty( $request->agent ) ) {
            $agentInput = $request->agent;
        
            $normalizedPhone = preg_replace( '/^.*?(1)/', '$1', $agentInput );
        
            $model->where( function ( $query ) use ( $agentInput ) {
                $query->where( 'agents.email', 'LIKE', '%' . $agentInput . '%' )
                      ->orWhere( 'agents.first_name', 'LIKE', '%' . $agentInput . '%' )
                      ->orWhere( 'agents.last_name', 'LIKE', '%' . $agentInput . '%' );
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

    public static function oneAgent( $request ) {

        $agent = Agent::find( Helper::decode( $request->id ) );

        $agent->append( ['profile_picture_path_new'] );

        return response()->json( $agent );
    }

    public static function removeAgentProfilePicture( $request ) {

        $updateAgent = Agent::find( Helper::decode($request->id) );
        
        Storage::delete( 'public/' . $updateAgent->image );

        $updateAgent->profile_picture = null;
        $updateAgent->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'agent.profile_picture' ) ) ] ),
        ] );
    }

    public static function createAgent( $request ) {

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required', 'string', 'max:255' ],
            'nickname' => [ 'nullable', 'string', 'max:255' ],
            'serial_number' => [ 'nullable', 'string', 'max:255' ],
            'calling_code' => [ 'nullable', 'string', 'max:10' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {

                $defaultCallingCode = "+60";

                $exist = Agent::where( 'status', 10 )
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
            'whatsapp_link' => [ 'nullable' ],
            'facebook_link' => [ 'nullable' ],
            'telegram_link' => [ 'nullable' ],
            'instagram_link' => [ 'nullable' ],
        ] );

        $attributeName = [
            'name' => __( 'agent.name' ),
            'nickname' => __( 'agent.nickname' ),
            'serial_number' => __( 'agent.serial_number' ),
            'calling_code' => __( 'agent.calling_code' ),
            'phone_number' => __( 'agent.phone_number' ),
            'whatsapp_link' => __( 'agent.whatsapp_link' ),
            'facebook_link' => __( 'agent.facebook_link' ),
            'telegram_link' => __( 'agent.telegram_link' ),
            'instagram_link' => __( 'agent.instagram_link' ),
            'property_sold' => __( 'agent.property_sold' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $profilePicturePath = null;
            if ( !empty( $request->profile_picture ) ) {
                $file = FileManager::where( 'id', $request->profile_picture )->first();

                if ( $file ) {
                    $profilePicturePath = $file->file;
                }
            }

            $createAgentObject = [
                'name' => $request->name ?? null,
                'nickname' => $request->nickname ?? null,
                'profile_picture' => $profilePicturePath,
                'serial_number' => $request->serial_number ?? null,
                'calling_code' => $request->calling_code ? $request->calling_code : '+60',
                'phone_number' => $request->phone_number,
                'whatsapp_link' => $request->whatsapp_link ?? null,
                'facebook_link' => $request->facebook_link ?? null,
                'telegram_link' => $request->telegram_link ?? null,
                'instagram_link' => $request->instagram_link ?? null,
                'status' => 10,
            ];

            $createAgent = Agent::create( $createAgentObject );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.agents' ) ) ] ),
        ] );
    }

    public static function updateAgent( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required', 'string', 'max:255' ],
            'nickname' => [ 'nullable', 'string', 'max:255' ],
            'serial_number' => [ 'nullable', 'string', 'max:255' ],
            'calling_code' => [ 'nullable', 'string', 'max:10' ],
            'phone_number' => [ 'required', 'digits_between:8,15', function( $attribute, $value, $fail ) use ( $request ) {
                
                $defaultCallingCode = "+60";

                $exist = Agent::where( 'id', '!=', $request->id )
                    ->where( 'status', 10 )
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
            'whatsapp_link' => [ 'nullable' ],
            'facebook_link' => [ 'nullable' ],
            'telegram_link' => [ 'nullable' ],
            'instagram_link' => [ 'nullable' ],
        ] );

        $attributeName = [
            'name' => __( 'agent.name' ),
            'nickname' => __( 'agent.nickname' ),
            'serial_number' => __( 'agent.serial_number' ),
            'calling_code' => __( 'agent.calling_code' ),
            'phone_number' => __( 'agent.phone_number' ),
            'whatsapp_link' => __( 'agent.whatsapp_link' ),
            'facebook_link' => __( 'agent.facebook_link' ),
            'telegram_link' => __( 'agent.telegram_link' ),
            'instagram_link' => __( 'agent.instagram_link' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateAgent = Agent::find( $request->id );
            
            if ( !empty( $request->profile_picture ) ) {
                $file = FileManager::where( 'id', $request->profile_picture )->first();
                if ( $file ) {
                    $updateAgent->profile_picture = $file->file;
                }
            }
            
            $updateAgent->name = $request->name ?? $updateAgent->name;
            $updateAgent->nickname = $request->nickname ?? $updateAgent->nickname;
            $updateAgent->serial_number = $request->serial_number ?? $updateAgent->serial_number;
            $updateAgent->calling_code = $request->calling_code ? $request->calling_code : '+60';
            $updateAgent->phone_number = $request->phone_number;
            $updateAgent->whatsapp_link = $request->whatsapp_link ?? $updateAgent->whatsapp_link;
            $updateAgent->facebook_link = $request->facebook_link ?? $updateAgent->facebook_link;
            $updateAgent->telegram_link = $request->telegram_link ?? $updateAgent->telegram_link;
            $updateAgent->instagram_link = $request->instagram_link ?? $updateAgent->instagram_link;

            $updateAgent->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.agents' ) ) ] ),
        ] );
    }

    public static function updateAgentStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateAgent = Agent::find( $request->id );
        $updateAgent->status = $request->status;
        $updateAgent->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.agents' ) ) ] ),
        ] );
    }
}