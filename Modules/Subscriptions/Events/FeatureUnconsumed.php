<?php

namespace App\Modules\Subscriptions\Events;

use App\Modules\Subscriptions\Models\Feature;
use Illuminate\Queue\SerializesModels;
use LucasDotVin\Soulbscription\Models\Subscription;

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
