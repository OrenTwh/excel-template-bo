<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    OtpService,
};

class OtpController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.otp_logs' );
        $this->data['content'] = 'admin.otp.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.otp_logs' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.otp_logs' ),
        ];

        $this->data['data']['type'] = [
            '1' => __( 'user.user_action' ),
            '2' => __( 'user.register' ),
        ];

        $this->data['data']['status'] = [
            '1' => __( 'user.active' ),
            '2' => __( 'user.expired' ),
            '10' => __( 'user.used' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allOtp( Request $request ) {

        return OtpService::allOtp( $request );
    }

    public function resendOtp( Request $request ) {

        return OtpService::resendOtp( $request );
    }
    
}
