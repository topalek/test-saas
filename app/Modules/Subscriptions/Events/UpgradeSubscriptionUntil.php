<?php

namespace App\Modules\Subscriptions\Events;

use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class UpgradeSubscriptionUntil
{
    use SerializesModels;

    public function __construct(
        public Model $model,
        public Subscription $subscription,
        public Carbon $expiresOn,
        public bool $startFromNow,
        public ?Plan $oldPlan = null,
        public ?Plan $newPlan = null
    ) {
    }
}
