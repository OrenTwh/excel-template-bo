<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AmenityService,
};

class AmenityController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.amenities' );
        $this->data['content'] = 'admin.amenity.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.amenities' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.amenities' ) ) ] );
        $this->data['content'] = 'admin.amenity.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.amenity.index' ),
                'text' => __( 'template.amenities' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.amenities' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.amenities' ) ) ] );
        $this->data['content'] = 'admin.amenity.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.amenity.index' ),
                'text' => __( 'template.amenities' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.amenities' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allAmenities( Request $request ) {

        return AmenityService::allAmenities( $request );
    }

    public function oneAmenity( Request $request ) {

        return AmenityService::oneAmenity( $request );
    }

    public function createAmenity( Request $request ) {

        return AmenityService::createAmenity( $request );
    }

    public function updateAmenity( Request $request ) {

        return AmenityService::updateAmenity( $request );
    }

    public function updateAmenityStatus( Request $request ) {

        return AmenityService::updateAmenityStatus( $request );
    }

    public function removeAmenityIcon( Request $request ) {

        return AmenityService::removeAmenityIcon( $request );
    }
}