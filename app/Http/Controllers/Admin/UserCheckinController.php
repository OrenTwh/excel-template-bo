<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    UserCheckinService,
};

class UserCheckinController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.user_checkins' );
        $this->data['content'] = 'admin.user_checkin.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.user_checkins' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.user_checkins' ) ) ] );
        $this->data['content'] = 'admin.user_checkin.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.user_checkin.index' ),
                'text' => __( 'template.user_checkins' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.user_checkins' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.user_checkins' ) ) ] );
        $this->data['content'] = 'admin.user_checkin.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.user_checkin.index' ),
                'text' => __( 'template.user_checkins' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.user_checkins' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function calendar( Request $request ) {

        $this->data['header']['title'] = __( 'template.checkin_calendar' );
        $this->data['content'] = 'admin.user_checkin.calendar';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.user_checkin.index' ),
                'text' => __( 'template.user_checkins' ),
                'class' => '',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allUserCheckins( Request $request ) {

        return UserCheckinService::allUserCheckins( $request );
    }

    public function oneUserCheckin( Request $request ) {

        return UserCheckinService::oneUserCheckin( $request );
    }

    public function createUserCheckin( Request $request ) {

        return UserCheckinService::createUserCheckin( $request );
    }

    public function updateUserCheckin( Request $request ) {

        return UserCheckinService::updateUserCheckin( $request );
    }

    public function updateUserCheckinStatus( Request $request ) {

        return UserCheckinService::updateUserCheckinStatus( $request );
    }

    public function removeUserCheckinGalleryImage( Request $request ) {

        return UserCheckinService::removeUserCheckinGalleryImage( $request );
    }
    
    public function allUserCheckinCalendars( Request $request ) {

        return UserCheckinService::allUserCheckinCalendars( $request );
    }
    
}
