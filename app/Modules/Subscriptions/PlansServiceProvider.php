<?php

namespace App\Modules\Subscriptions;

use Illuminate\Support\ServiceProvider;

class PlansServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . "/migrations" => database_path('migrations'),
        ], 'subscriptions');

        if (app()->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . "/migrations");
        }
    }
}
