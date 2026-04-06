<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    NationalityService,
};

class NationalityController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.nationalities' );
        $this->data['content'] = 'admin.nationality.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.nationalities' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.nationalities' ) ) ] );
        $this->data['content'] = 'admin.nationality.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.nationality.index' ),
                'text' => __( 'template.nationalities' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.nationalities' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.nationalities' ) ) ] );
        $this->data['content'] = 'admin.nationality.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.nationality.index' ),
                'text' => __( 'template.nationalities' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.nationalities' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allNationalities( Request $request ) {

        return NationalityService::allNationalities( $request );
    }

    public function oneAgent( Request $request ) {

        return NationalityService::oneAgent( $request );
    }

    public function createAgent( Request $request ) {

        return NationalityService::createAgent( $request );
    }

    public function updateAgent( Request $request ) {

        return NationalityService::updateAgent( $request );
    }

    public function updateNationalitiestatus( Request $request ) {

        return NationalityService::updateNationalitiestatus( $request );
    }

    public function removeAgentProfilePicture( Request $request ) {

        return NationalityService::removeAgentProfilePicture( $request );
    }
}
