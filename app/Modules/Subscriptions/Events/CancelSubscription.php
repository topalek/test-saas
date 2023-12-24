<?php

namespace App\Modules\Subscriptions\Events;

use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class CancelSubscription
{
    use SerializesModels;

    public function __construct(
        public Model $model,
        public Subscription $subscription
    ) {
    }
}
