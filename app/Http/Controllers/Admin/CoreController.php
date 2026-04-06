<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CoreService,
};

class CoreController extends Controller
{
    public function getNotificationList( Request $request ) {
        return CoreService::getNotificationList( $request );
    }

    public function seenNotification( Request $request ) {
        return CoreService::seenNotification( $request );
    }
}
