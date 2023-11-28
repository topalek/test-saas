<?php

namespace App\Modules\Subscriptions\Events;

use App\Modules\Subscriptions\Models\Plan;
use Doctrine\Inflector\Rules\Substitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class UpgradeSubscription
{
    use SerializesModels;

    public function __construct(
        public Model $model,
        public Substitution $subscription,
        public bool $startFromNow,
        public ?Plan $oldPlan = null,
        public ?Plan $newPlan = null
    ) {
    }
}
