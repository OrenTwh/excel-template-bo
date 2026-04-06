<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    WalletService,
};

use Helper;

class WalletTransactionController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.wallet_transactions' );
        $this->data['content'] = 'admin.wallet_transaction.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.wallet_transactions' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.wallet_transactions' ),
        ];

        foreach ( Helper::wallets() as $key => $wallet ) {
            $this->data['data']['wallet'][$key] = $wallet;
        }
        foreach ( Helper::trxTypes() as $key => $trxtype ) {
            $this->data['data']['transaction_type'][$key] = $trxtype;
        }

        return view( 'admin.main' )->with( $this->data );
    }

    public function allWalletTransactions( Request $request ) {
        return WalletService::allWalletTransactions( $request );
    }
}
