<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ExclusiveDealService
};

class ExclusiveDealController extends Controller
{
    /**
     * 1. Get exclusive deals
     *
     * <aside class="notice">Get all exclusive deals ( sorted )</aside>
     *
     * @group Exclusive Deal API
     *
     */
    public function getExclusiveDeals( Request $request ) {

        return ExclusiveDealService::getExclusiveDeals( $request );
    }

    /**
     * 2. Get one exclusive deal detail
     *
     * @group Exclusive Deal API
     *
     * @bodyParam id string required The id of the exclusive deal. Example: 1
     *
     */
    public function oneExclusiveDeal( Request $request ) {

        return ExclusiveDealService::oneExclusiveDealClient( $request );
    }
}
