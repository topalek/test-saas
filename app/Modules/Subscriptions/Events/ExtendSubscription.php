<?php

namespace App\Modules\Subscriptions\Events;

use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class ExtendSubscription
{
    use SerializesModels;

    public function __construct(
        public Model $model,
        public Subscription $subscription,
        public bool $startFromNow,
        public ?Subscription $newSubscription = null
    ) {
    }
}
