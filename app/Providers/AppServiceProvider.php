<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Spatie\Activitylog\Models\Activity;

require_once( 'BrowserDetection.php' );

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        if( !$this->app->environment('local') ) {
            \URL::forceScheme('https');
        }

        $browser = new \Wolfcast\BrowserDetection();

        Activity::saving( function( Activity $activity ) use ( $browser ) {

            $activity->properties = $activity->properties->put( 'agent', [
                'ip' => request()->ip(),
                'user_agent' => $browser->getUserAgent(),
                'browserName' => $browser->getName() . ' ' . $browser->getVersion(),
                'os' => $browser->getPlatformVersion() . ' ' . $browser->getPlatformVersion( true ),
            ] );
        } );

        if( $this->app->environment( 'production' ) ) {
            \URL::forceScheme( 'https' );
        }
    }
}
