<?php

namespace App\Modules\Subscriptions\Listener;

use App\Modules\Subscriptions\Models\Plan;
use Vtlabs\Core\Models\User\User;
use Vtlabs\Payment\Events\PaymentUpdated;

class PaymentUpdatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param Registered $event
     *
     * @return void
     */
    public function handle(PaymentUpdated $event)
    {
        $payment = $event->payment;

        // we need to subscribe plan according to payment status
        if ($payment->payable_type == 'App\Modules\Subscriptions\Models\Plan' && $payment->payer_type == User::class) {
            if ($payment->status == 'paid') {
                $plan = Plan::find($payment->payable_id);
                $user = User::find($payment->payer_id);
                if ($user->hasActiveSubscription()) {
                    $user->cancelCurrentSubscription();
                }
                $user->subscribeTo($plan, $plan->duration);
            }
        }
        return true;
    }
}
