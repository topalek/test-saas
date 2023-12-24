<?php

namespace App\Modules\Subscriptions\Events;

use App\Modules\Subscriptions\Models\Feature;
use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Queue\SerializesModels;

class FeatureUnconsumed
{
    use SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public Feature $feature,
        public float $used,
        public float $remaining
    ) {
    }
}
