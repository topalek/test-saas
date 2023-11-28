<?php

namespace App\Modules\Subscriptions\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LucasDotVin\Soulbscription\Models\Subscription;

class SubscriptionUsage extends Model
{
    protected $table   = 'plans_usages';
    protected $guarded = [];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function scopeCode(Builder $query, string $code): void
    {
        $query->where('code', $code);
    }
}
