<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Schema::defaultStringLength(191);

        // Make sure the directory for compiled views exist
        if (!is_dir(config('view.compiled'))) {
            mkdir(config('view.compiled'), 0755, true);
        }

        $this->app->singleton("mailer", function ($app) {
            $app->configure("services");
            return $app->loadComponent("mail", "Illuminate\Mail\MailServiceProvider", "mailer");
        });
    }
}
