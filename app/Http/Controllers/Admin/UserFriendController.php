<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    UserFriendService,
};

use App\Models\{
    User,
};

class UserFriendController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.user_friends' );
        $this->data['content'] = 'admin.user_friend.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.user_friends' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'user_friend.pending' ),
            '20' => __( 'user_friend.accepted' ),
            '30' => __( 'user_friend.declined' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.user_friends' ) ) ] );
        $this->data['content'] = 'admin.user_friend.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.user_friend.index' ),
                'text' => __( 'template.user_friends' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.user_friends' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'user_friend.pending' ),
            '20' => __( 'user_friend.accepted' ),
            '30' => __( 'user_friend.declined' ),
        ];
        $this->data['data']['users'] = User::where( 'status', 10 )->get()->append( [ 'encrypted_id' ] );

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.user_friends' ) ) ] );
        $this->data['content'] = 'admin.user_friend.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.user_friend.index' ),
                'text' => __( 'template.user_friends' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.user_friends' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'user_friend.pending' ),
            '20' => __( 'user_friend.accepted' ),
            '30' => __( 'user_friend.declined' ),
        ];
        $this->data['data']['users'] = User::where( 'status', 10 )->get()->append( [ 'encrypted_id' ] );

        return view( 'admin.main' )->with( $this->data );
    }

    public function allUserFriends( Request $request ) {

        return UserFriendService::allUserFriends( $request );
    }

    public function oneUserFriend( Request $request ) {

        return UserFriendService::oneUserFriend( $request );
    }

    public function createUserFriend( Request $request ) {

        return UserFriendService::createUserFriend( $request );
    }

    public function updateUserFriend( Request $request ) {

        return UserFriendService::updateUserFriend( $request );
    }

    public function updateUserFriendStatus( Request $request ) {

        return UserFriendService::updateUserFriendStatus( $request );
    }
}
