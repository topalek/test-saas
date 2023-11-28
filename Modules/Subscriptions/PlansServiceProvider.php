<?php

namespace App\Modules\Subscriptions;

use Illuminate\Support\ServiceProvider;

class PlansServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . "/migrations" => database_path('migrations'),
        ], 'subscriptions');

        if (app()->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . "/migrations");
        }
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
