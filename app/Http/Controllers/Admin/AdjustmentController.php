<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    AdjustmentService,
};

class AdjustmentController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.adjustments' );
        $this->data['content'] = 'admin.adjustment.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.adjustments' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.adjustments' ) ) ] );
        $this->data['content'] = 'admin.adjustment.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.adjustment.index' ),
                'text' => __( 'template.adjustments' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.adjustments' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.adjustments' ) ) ] );
        $this->data['content'] = 'admin.adjustment.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.adjustment.index' ),
                'text' => __( 'template.adjustments' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.adjustments' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allAdjustments( Request $request ) {

        return AdjustmentService::allAdjustments( $request );
    }

    public function oneAdjustment( Request $request ) {

        return AdjustmentService::oneAdjustment( $request );
    }

    public function createAdjustment( Request $request ) {

        return AdjustmentService::createAdjustment( $request );
    }

    public function updateAdjustment( Request $request ) {

        return AdjustmentService::updateAdjustment( $request );
    }

    public function updateAdjustmentStatus( Request $request ) {

        return AdjustmentService::updateAdjustmentStatus( $request );
    }

    public function removeAdjustmentAttachment( Request $request ) {

        return AdjustmentService::removeAdjustmentAttachment( $request );
    }
}
