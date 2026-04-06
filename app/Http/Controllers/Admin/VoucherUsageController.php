<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    VoucherUsageService,
};

class VoucherUsageController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.voucher_usages' );
        $this->data['content'] = 'admin.voucher_usage.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.voucher_usages' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['voucher_type'] = [
            '1' => __( 'voucher_usage.public_voucher' ),
            '2' => __( 'voucher_usage.user_specific_voucher' ),
            '3' => __( 'voucher_usage.login_reward_voucher' ),
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'voucher.percentage' ),
            '2' => __( 'voucher.fixed_amount' ),
            '3' => __( 'voucher.free_cup' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allVoucherUsages( Request $request ) {

        return VoucherUsageService::allVoucherUsages( $request );
    }
    
}
