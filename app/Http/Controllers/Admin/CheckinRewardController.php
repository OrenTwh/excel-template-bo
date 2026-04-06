<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CheckinRewardService,
};

class CheckinRewardController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.checkin_rewards' );
        $this->data['content'] = 'admin.checkin_reward.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.checkin_rewards' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        $this->data['data']['reward_types'] = [
            '1' => __('checkin_reward.points'),
            '2' => __('checkin_reward.voucher'),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.checkin_rewards' ) ) ] );
        $this->data['content'] = 'admin.checkin_reward.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.checkin_reward.index' ),
                'text' => __( 'template.checkin_rewards' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.checkin_rewards' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['reward_types'] = [
            '1' => __('checkin_reward.points'),
            '2' => __('checkin_reward.voucher'),
        ];
        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.checkin_rewards' ) ) ] );
        $this->data['content'] = 'admin.checkin_reward.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.checkin_reward.index' ),
                'text' => __( 'template.checkin_rewards' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.checkin_rewards' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['reward_types'] = [
            '1' => __('checkin_reward.points'),
            '2' => __('checkin_reward.voucher'),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allCheckinRewards( Request $request ) {

        return CheckinRewardService::allCheckinRewards( $request );
    }

    public function oneCheckinReward( Request $request ) {

        return CheckinRewardService::oneCheckinReward( $request );
    }

    public function createCheckinReward( Request $request ) {

        return CheckinRewardService::createCheckinReward( $request );
    }

    public function updateCheckinReward( Request $request ) {

        return CheckinRewardService::updateCheckinReward( $request );
    }

    public function updateCheckinRewardStatus( Request $request ) {

        return CheckinRewardService::updateCheckinRewardStatus( $request );
    }

    public function removeCheckinRewardGalleryImage( Request $request ) {

        return CheckinRewardService::removeCheckinRewardGalleryImage( $request );
    }    
    
    public function allCheckinRewardsForVendingMachine( Request $request ) {

        return CheckinRewardService::allCheckinRewardsForVendingMachine( $request );
    }
    
    public function getCheckinRewardStock( Request $request ) {

        return CheckinRewardService::getCheckinRewardStock( $request );
    }
    
}
