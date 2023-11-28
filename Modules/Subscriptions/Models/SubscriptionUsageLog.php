<?php

namespace App\Modules\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SubscriptionUsageLog extends Model
{
    protected $table = 'plan_usage_logs';

    protected $fillable = ['subscription_id'];

    public static function getTodayUsage($subscriptionId)
    {
        return SubscriptionUsageLog::where('subscription_id', $subscriptionId)->whereDate(
            'created_at',
            Carbon::today()
        )->count();
    }
}
