<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $data = [];

    public function __construct() {

        if( !app()->runningInConsole() ) {
            $routeArray = app( 'request' )->route()->getAction();
            list( $controller, $action ) = explode( '@', $routeArray['controller'] );
    
            $this->data['controller'] = $controller;
            $this->data['action'] = $action;
        }
    }
}
