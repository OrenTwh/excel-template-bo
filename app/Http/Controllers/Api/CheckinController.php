<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    UserCheckinService
};

class CheckinController extends Controller
{
    /**
     * 1. Get Check-in History
     * 
     * @authenticated
     * 
     * @group Check-in API
     * 
     * @queryParam per_page integer Retrieve how many product in a page, default is 10. Example: 10
     * 
     */
    public function getCheckinHistory( Request $request ) {

        return UserCheckinService::getCheckinHistory( $request );
    }

    /**
     * 2. Check-in
     * 
     * @authenticated
     * 
     * @group Check-in API
     * 
     * 
     */
    public function checkin( Request $request ) {

        return UserCheckinService::checkin( $request );
    }

    /**
     * 3. Get Check-in Rewards
     * 
     * @authenticated
     * 
     * @group Check-in API
     * 
     * 
     */
    public function getCheckinRewards( Request $request ) {

        return UserCheckinService::getCheckinRewards( $request );
    }
}
