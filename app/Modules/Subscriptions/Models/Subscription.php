<?php

namespace App\Modules\Subscriptions\Models;

use App\Modules\Subscriptions\Enums\FeatureType;
use App\Modules\Subscriptions\Events\FeatureUsed;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Subscription extends Model
{
    protected $table    = 'subscriptions';
    protected $fillable = [
        'plan_id',
        'subscriber_type',
        'subscriber_id',
        'starts_on',
        'expires_on',
        'cancelled_on',
        'suppressed_at',
        'was_switched',
    ];

    protected $casts = [
        'starts_on'     => 'date',
        'expires_on'    => 'date',
        'cancelled_on'  => 'date',
        'suppressed_at' => 'date',
        'was_switched'  => 'boolean',
    ];
    protected $with  = ['plan'];

    public function subscriber(): MorphTo
    {
        return $this->morphTo();
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

    public function remainingDays(): int
    {
        if ($this->hasExpired()) {
            return 0;
        }

        return Carbon::now()->diffInDays(Carbon::parse($this->expires_on));
    }

    public function hasExpired(): bool
    {
        return Carbon::now()->greaterThan(Carbon::parse($this->expires_on));
    }

    public function isPendingCancellation(): bool
    {
        return ($this->isCancelled() && $this->isActive());
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_on != null;
    }

    public function isActive(): bool
    {
        return ($this->hasStarted() && !$this->hasExpired());
    }

    public function hasStarted(): bool
    {
        return Carbon::now()->greaterThanOrEqualTo(Carbon::parse($this->starts_on));
    }

    public function cancel(): self
    {
        $this->update([
            'cancelled_on' => Carbon::now(),
        ]);

        return $this;
    }

    public function useFeature(string $featureCode, float $amount): bool
    {
        $feature = $this->features()->code($featureCode)->first();

        if (!$feature || $feature->type != FeatureType::limit) {
            return false;
        }

        $usage = $this->usages()->code($featureCode)->first();

        if (!$usage) {
            $usage = $this->usages()->save(
                new FeatureUsage([
                    'code' => $featureCode,
                    'used' => 0,
                ])
            );
        }

        if (!$feature->isUnlimited() && $usage->used + $amount > $feature->limit) {
            return false;
        }

        $remaining = (float)($feature->isUnlimited()) ? -1 : $feature->limit - ($usage->used + $amount);

        event(new FeatureUsed($this, $feature, $amount));

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
        return $this->hasMany(FeatureUsage::class, 'subscription_id');
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

        $used = (float)($feature->isUnlimited()) ? ((($usage->used - $amount < 0) ? 0 : ($usage->used - $amount))) : ($usage->used - $amount);
        $remaining = (float)($feature->isUnlimited()) ? -1 : (($used > 0) ? ($feature->limit - $used) : $feature->limit);

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

    public function subscribeTo(Plan $plan, $expiration = null, $startDate = null): Subscription
    {
        if ($plan->periodicity) {
            $expiration = $expiration ?? $plan->calculateNextRecurrenceEnd($startDate);
        } else {
            $expiration = null;
        }

        return $this->subscription()
            ->make([
                'expired_at' => $expiration,
            ])
            ->plan()
            ->associate($plan)
            ->start($startDate)
        ;
    }

    public function hasSubscriptionTo(Plan $plan): bool
    {
        return $this->subscription()
            ->where('plan_id', $plan->id)
            ->exists()
        ;
    }

    public function isSubscribedTo(Plan $plan): bool
    {
        return $this->hasSubscriptionTo($plan);
    }

    public function missingSubscriptionTo(Plan $plan): bool
    {
        return !$this->hasSubscriptionTo($plan);
    }

    public function isNotSubscribedTo(Plan $plan): bool
    {
        return !$this->isSubscribedTo($plan);
    }

    public function switchTo(Plan $plan, $expiration = null, $immediately = true): Subscription
    {
        if ($immediately) {
            $this->subscription
                ->markAsSwitched()
                ->suppress()
                ->save()
            ;

            return $this->subscribeTo($plan, $expiration);
        }

        $this->subscription
            ->markAsSwitched()
            ->save()
        ;

        $startDate = $this->subscription->expired_at;
        $newSubscription = $this->subscribeTo($plan, startDate: $startDate);

        return $newSubscription;
    }

    public function expired()
    {
        return $this->expired_at->isPast();
    }

    public function notExpired()
    {
        return !$this->expired();
    }

    public function started()
    {
        if (empty($this->started_at)) {
            return false;
        }

        return $this->started_at->isPast();
    }

    public function notStarted()
    {
        return !$this->started();
    }

    public function suppressed()
    {
        if (empty($this->suppressed_at)) {
            return false;
        }

        return $this->suppressed_at->isPast();
    }

    public function notSuppressed()
    {
        return !$this->suppressed();
    }
}
