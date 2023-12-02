<?php

namespace App\Modules\Subscriptions\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use LucasDotVin\Soulbscription\Models\Subscription;

class NewSubscription
{
    use SerializesModels;

    public function __construct(public Model $model, public Subscription $subscription)
    {
    }
}
