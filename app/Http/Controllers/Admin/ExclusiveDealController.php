<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ExclusiveDealService,
};

use App\Models\{
    ExclusiveDeal,
};

class ExclusiveDealController extends Controller
{

    public function updateOrder( Request $request ) {
        return ExclusiveDealService::updateOrder( $request );
    }

    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.exclusive_deals' );
        $this->data['content'] = 'admin.exclusive_deal.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.exclusive_deals' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['exclusive_deals'] = ExclusiveDeal::where( 'status', 10 )
            ->orderBy( 'sequence' )
            ->get();

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.exclusive_deals' ) ) ] );
        $this->data['content'] = 'admin.exclusive_deal.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.exclusive_deal.index' ),
                'text' => __( 'template.exclusive_deals' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.exclusive_deals' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['exclusive_deals'] = ExclusiveDeal::where( 'status', 10 )
            ->orderBy( 'sequence' )
            ->get();

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.exclusive_deals' ) ) ] );
        $this->data['content'] = 'admin.exclusive_deal.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.exclusive_deal.index' ),
                'text' => __( 'template.exclusive_deals' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.exclusive_deals' ) ) ] ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allExclusiveDeals( Request $request ) {

        return ExclusiveDealService::allExclusiveDeals( $request );
    }

    public function oneExclusiveDeal( Request $request ) {

        return ExclusiveDealService::oneExclusiveDeal( $request );
    }

    public function createExclusiveDeal( Request $request ) {

        return ExclusiveDealService::createExclusiveDeal( $request );
    }

    public function updateExclusiveDeal( Request $request ) {

        return ExclusiveDealService::updateExclusiveDeal( $request );
    }

    public function updateExclusiveDealStatus( Request $request ) {

        return ExclusiveDealService::updateExclusiveDealStatus( $request );
    }

    public function removeExclusiveDealImage( Request $request ) {

        return ExclusiveDealService::removeExclusiveDealImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return ExclusiveDealService::ckeUpload( $request );
    }

    public function deleteExclusiveDeal( Request $request ) {

        return ExclusiveDealService::deleteExclusiveDeal( $request );
    }

}
