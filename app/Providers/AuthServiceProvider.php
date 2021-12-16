<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
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
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
        Schema::defaultStringLength(191);

        $this->app['auth']->viaRequest('api', function (Request $request) {
            //Sample authorization header looks like this
            //Authorization: Bearer your-api-token-here
            $header = $request->header('Authorization');
            if (!empty($header) && preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
                return \App\Models\Auth\User::where('api_token', $token)->first();
            }

            return null;
        });
    }
}
