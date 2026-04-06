<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    WalletService,
};

use Helper;

class WalletController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.users' );
        $this->data['content'] = 'admin.wallet.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.wallets' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.wallets' ),
        ];
        $this->data['data']['wallet'][''] = __( 'datatables.all_x' );

        foreach ( Helper::wallets() as $key => $wallet ) {
            $this->data['data']['wallet'][$key] = $wallet;
        }

        return view( 'admin.main' )->with( $this->data );
    }

    public function allWallets( Request $request ) {
        return WalletService::allWallets( $request );
    }

    public function oneWallet( Request $request ) {
        return WalletService::oneWallet( $request );
    }

    public function updateWallet( Request $request ) {
        return WalletService::updateWallet( $request );
    }

    public function updateWalletMultiple( Request $request ) {
        return WalletService::updateWalletMultiple( $request );
    }
}
