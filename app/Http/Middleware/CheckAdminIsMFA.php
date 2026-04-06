<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Models\{
    Admin
};

class CheckAdminIsMFA
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle( Request $request, Closure $next )
    {
        if ( config( 'services.mfa.enabled' ) ) {

            if ( empty( auth()->user()->mfa_secret ) ) {
                return redirect()->route( 'admin.first_setup' );
            }
        }

        return $next( $request );
    }
}
