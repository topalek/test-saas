<?php

namespace App\Modules\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PlanSubscriptionModel extends Model
{
    protected $table   = 'subscriptions';
    protected $guarded = [];
    protected $dates   = [
        'starts_on',
        'expires_on',
        'cancelled_on',
    ];
    protected $casts   = [
        'is_paid'      => 'boolean',
        'is_recurring' => 'boolean',
    ];
    protected $with    = ['plan'];

    public function subscriber(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopePaid(Builder $query)
    {
        $query->where('is_paid', true);
    }

    public function scopeUnpaid(Builder $query)
    {
        $query->where('is_paid', false);
    }

    public function scopeExpired(Builder $query)
    {
        $query->where('expires_on', '<', Carbon::now()->toDateTimeString());
    }

    public function scopeRecurring(Builder $query)
    {
        $query->where('is_recurring', true);
    }

    public function scopeCancelled(Builder $query)
    {
        $query->whereNotNull('cancelled_on');
    }

    public function scopeNotCancelled(Builder $query)
    {
        $query->whereNull('cancelled_on');
    }

    public function scopeStripe(Builder $query)
    {
        $query->where('payment_method', 'stripe');
    }

    /**
     * Get the remaining days in this subscription.
     *
     * @return int
     */
    public function remainingDays(): int
    {
        if ($this->hasExpired()) {
            return 0;
        }

        return Carbon::now()->diffInDays(Carbon::parse($this->expires_on));
    }

    /**
     * Checks if the current subscription has expired.
     *
     * @return bool
     */
    public function hasExpired(): bool
    {
        return Carbon::now()->greaterThan(Carbon::parse($this->expires_on));
    }

    /**
     * Checks if the current subscription is pending cancellation.
     *
     * @return bool
     */
    public function isPendingCancellation(): bool
    {
        return ($this->isCancelled() && $this->isActive());
    }

    /**
     * Checks if the current subscription is cancelled (expiration date is in the past & the subscription is cancelled).
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->cancelled_on != null;
    }

    /**
     * Checks if the current subscription is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return ($this->hasStarted() && !$this->hasExpired());
    }

    /**
     * Checks if the current subscription has started.
     *
     * @return bool
     */
    public function hasStarted(): bool
    {
        return Carbon::now()->greaterThanOrEqualTo(Carbon::parse($this->starts_on));
    }

    /**
     * Cancel this subscription.
     *
     * @return self $this
     */
    public function cancel(): self
    {
        $this->update([
            'cancelled_on' => Carbon::now(),
        ]);

        return $this;
    }

    /**
     * Consume a feature, if it is 'limit' type.
     *
     * @param string $featureCode The feature code. This feature has to be 'limit' type.
     * @param float  $amount      The amount consumed.
     *
     * @return bool Wether the feature was consumed successfully or not.
     */
    public function consumeFeature(string $featureCode, float $amount): bool
    {
        $usageModel = new SubscriptionUsage();

        $feature = $this->features()->code($featureCode)->first();

        if (!$feature || $feature->type != 'limit') {
            return false;
        }

        $usage = $this->usages()->code($featureCode)->first();

        if (!$usage) {
            $usage = $this->usages()->save(
                new $usageModel([
                    'code' => $featureCode,
                    'used' => 0,
                ])
            );
        }

        if (!$feature->isUnlimited() && $usage->used + $amount > $feature->limit) {
            return false;
        }

        $remaining = (float)($feature->isUnlimited()) ? -1 : $feature->limit - ($usage->used + $amount);

        event(new \App\Modules\Subscriptions\Events\FeatureConsumed($this, $feature, $amount, $remaining));

        return $usage->update([
            'used' => (float)($usage->used + $amount),
        ]);
    }

    public function features(): HasMany
    {
        return $this->plan()->first()->features();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function usages()
    {
        return $this->hasMany(SubscriptionUsage::class, 'subscription_id');
    }

    /**
     * Reverse of the consume a feature method, if it is 'limit' type.
     *
     * @param string $featureCode The feature code. This feature has to be 'limit' type.
     * @param float  $amount      The amount consumed.
     *
     * @return bool Wether the feature was consumed successfully or not.
     */
    public function unconsumeFeature(string $featureCode, float $amount)
    {
        $usageModel = config('plans.models.usage');

        $feature = $this->features()->code($featureCode)->first();

        if (!$feature || $feature->type != 'limit') {
            return false;
        }

        $usage = $this->usages()->code($featureCode)->first();

        if (!$usage) {
            $usage = $this->usages()->save(
                new $usageModel([
                    'code' => $featureCode,
                    'used' => 0,
                ])
            );
        }

        $used = (float)($feature->isUnlimited(
        )) ? ((($usage->used - $amount < 0) ? 0 : ($usage->used - $amount))) : ($usage->used - $amount);
        $remaining = (float)($feature->isUnlimited(
        )) ? -1 : (($used > 0) ? ($feature->limit - $used) : $feature->limit);

        event(new \App\Modules\Subscriptions\Events\FeatureUnconsumed($this, $feature, $amount, $remaining));

        return $usage->update([
            'used' => $used,
        ]);
    }

    /**
     * Get the amount used for a limit.
     *
     * @param string $featureCode The feature code. This feature has to be 'limit' type.
     *
     * @return null|float Null if doesn't exist, integer with the usage.
     */
    public function getUsageOf(string $featureCode)
    {
        $usage = $this->usages()->code($featureCode)->first();
        $feature = $this->features()->code($featureCode)->first();

        if (!$feature || $feature->type != 'limit') {
            return;
        }

        if (!$usage) {
            return 0;
        }

        return (float)$usage->used;
    }

    /**
     * Get the amount remaining for a feature.
     *
     * @param string $featureCode The feature code. This feature has to be 'limit' type.
     *
     * @return float The amount remaining.
     */
    public function getRemainingOf(string $featureCode)
    {
        $usage = $this->usages()->code($featureCode)->first();
        $feature = $this->features()->code($featureCode)->first();

        if (!$feature || $feature->type != 'limit') {
            return 0;
        }

        if (!$usage) {
            return (float)($feature->isUnlimited()) ? -1 : $feature->limit;
        }

        return (float)($feature->isUnlimited()) ? -1 : ($feature->limit - $usage->used);
    }
}
