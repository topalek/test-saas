<?php

namespace App\Modules\Subscriptions\Providers;

use App\Modules\Subscriptions\Listener\PaymentUpdatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Vtlabs\Payment\Events\PaymentUpdated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        PaymentUpdated::class => [PaymentUpdatedListener::class]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        //
    }
}
