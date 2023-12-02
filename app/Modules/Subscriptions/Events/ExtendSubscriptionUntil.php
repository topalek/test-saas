<?php

namespace App\Modules\Subscriptions\Events;

use App\Modules\Subscriptions\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class ExtendSubscriptionUntil
{
    use SerializesModels;

    public function __construct(
        public Model $model,
        public Subscription $subscription,
        public Carbon $expiresOn,
        public bool $startFromNow,
        public ?Subscription $newSubscription = null
    ) {
    }
}
