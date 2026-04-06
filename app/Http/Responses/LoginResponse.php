<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * @param  $request
     * @return mixed
     */
    public function toResponse( $request ) {

        $home = '/';

        if( request()->is( config( 'services.url.admin_path' ) . '/*' ) ) {
            $home = config( 'services.url.admin_path' ) . '/dashboard';
        }

        return redirect()->intended( $home );
    }
}