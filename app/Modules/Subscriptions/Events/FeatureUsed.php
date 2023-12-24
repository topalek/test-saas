<?php

namespace App\Modules\Subscriptions\Events;

use App\Modules\Subscriptions\Models\Feature;
use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeatureUsed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;


    public function __construct(
        public Subscription $subscription,
        public Feature $feature,
        public float $used,
    ) {
    }
}
