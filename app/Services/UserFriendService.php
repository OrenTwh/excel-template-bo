<?php

namespace App\Services;

use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use Helper;

use App\Models\{
    User,
    UserFriend,
};

use Carbon\Carbon;

class UserFriendService
{
    public static function allUserFriends( $request ) {

        $userFriend = UserFriend::with( [ 'user', 'friend' ] )->select( 'user_friends.*' );

        $filterObject = self::filter( $request, $userFriend );
        $userFriend = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = strToUpper( $request->input( 'order.0.dir' ) );

            switch ( $request->input( 'order.0.column' ) ) {
                case '2':
                    $userFriend->orderBy( 'user_friends.created_at', $dir );
                    break;
                case '5':
                    $userFriend->orderBy( 'user_friends.status', $dir );
                    break;
            }
        }

        $userFriendCount = $userFriend->count();

        $limit = $request->length == -1 ? 1000000 : $request->length;
        $offset = $request->start;

        $userFriends = $userFriend->skip( $offset )->take( $limit )->get();

        if ( $userFriends ) {
            $userFriends->append( [ 'encrypted_id' ] );
        }

        $totalRecord = UserFriend::count();

        $data = [
            'user_friends' => $userFriends,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $userFriendCount : $totalRecord,
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

                $model->whereBetween( 'user_friends.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {
                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'user_friends.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->user ) ) {
            $model->whereHas( 'user', function( $q ) use ( $request ) {
                $q->where( 'fullname', 'LIKE', '%' . $request->user . '%' )
                  ->orWhere( 'email', 'LIKE', '%' . $request->user . '%' );
            } );
            $filter = true;
        }

        if ( !empty( $request->friend ) ) {
            $model->whereHas( 'friend', function( $q ) use ( $request ) {
                $q->where( 'fullname', 'LIKE', '%' . $request->friend . '%' )
                  ->orWhere( 'email', 'LIKE', '%' . $request->friend . '%' );
            } );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'user_friends.status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneUserFriend( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $userFriend = UserFriend::find( $request->id );

        return response()->json( [
            'user_id'   => Helper::encode( $userFriend->user_id ),
            'friend_id' => Helper::encode( $userFriend->friend_id ),
            'status'    => $userFriend->status,
        ] );
    }

    public static function createUserFriend( $request ) {

        $request->merge( [
            'user_id'   => Helper::decode( $request->user_id ),
            'friend_id' => Helper::decode( $request->friend_id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'user_id'   => [ 'required', 'exists:users,id' ],
            'friend_id' => [ 'required', 'exists:users,id', 'different:user_id' ],
            'status'    => [ 'required', 'in:10,20,30' ],
        ] );

        $attributeName = [
            'user_id'   => strtolower( __( 'user_friend.user' ) ),
            'friend_id' => strtolower( __( 'user_friend.friend' ) ),
            'status'    => strtolower( __( 'datatables.status' ) ),
        ];

        $validator->setAttributeNames( $attributeName )->validate();

        $exists = UserFriend::where( 'user_id', $request->user_id )
            ->where( 'friend_id', $request->friend_id )
            ->exists();

        if ( $exists ) {
            return response()->json( [
                'errors' => [
                    'friend_id' => __( 'user_friend.duplicate_entry' ),
                ],
            ], 422 );
        }

        DB::beginTransaction();

        try {
            UserFriend::create( [
                'user_id'   => $request->user_id,
                'friend_id' => $request->friend_id,
                'status'    => $request->status,
            ] );

            DB::commit();
        } catch ( \Throwable $th ) {
            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => __( 'user_friend.user_friend' ) ] ),
        ] );
    }

    public static function updateUserFriend( $request ) {

        $request->merge( [
            'id'        => Helper::decode( $request->id ),
            'user_id'   => Helper::decode( $request->user_id ),
            'friend_id' => Helper::decode( $request->friend_id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'id'        => [ 'required', 'exists:user_friends,id' ],
            'user_id'   => [ 'required', 'exists:users,id' ],
            'friend_id' => [ 'required', 'exists:users,id', 'different:user_id' ],
            'status'    => [ 'required', 'in:10,20,30' ],
        ] );

        $attributeName = [
            'user_id'   => strtolower( __( 'user_friend.user' ) ),
            'friend_id' => strtolower( __( 'user_friend.friend' ) ),
            'status'    => strtolower( __( 'datatables.status' ) ),
        ];

        $validator->setAttributeNames( $attributeName )->validate();

        $exists = UserFriend::where( 'user_id', $request->user_id )
            ->where( 'friend_id', $request->friend_id )
            ->where( 'id', '!=', $request->id )
            ->exists();

        if ( $exists ) {
            return response()->json( [
                'errors' => [
                    'friend_id' => __( 'user_friend.duplicate_entry' ),
                ],
            ], 422 );
        }

        DB::beginTransaction();

        try {
            $userFriend = UserFriend::find( $request->id );
            $userFriend->user_id   = $request->user_id;
            $userFriend->friend_id = $request->friend_id;
            $userFriend->status    = $request->status;
            $userFriend->save();

            DB::commit();
        } catch ( \Throwable $th ) {
            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => __( 'user_friend.user_friend' ) ] ),
        ] );
    }

    public static function updateUserFriendStatus( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {
            $userFriend = UserFriend::find( $request->id );
            $userFriend->status = $request->status;
            $userFriend->save();

            DB::commit();

            return response()->json( [
                'message' => __( 'template.x_updated', [ 'title' => __( 'user_friend.user_friend' ) ] ),
            ] );
        } catch ( \Throwable $th ) {
            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }
    }
}
