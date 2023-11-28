<?php

namespace App\Modules\Subscriptions\Events;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use LucasDotVin\Soulbscription\Models\Subscription;

class NewSubscriptionUntil
{
    use SerializesModels;

    public function __construct(
        public Model $model,
        public Subscription $subscription,
        public Carbon $expiresOn
    ) {
    }
}
