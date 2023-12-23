<?php

namespace App\Modules\Subscriptions\Traits;

use App\Modules\Subscriptions\Events\CancelSubscription;
use App\Modules\Subscriptions\Events\ExtendSubscription;
use App\Modules\Subscriptions\Events\ExtendSubscriptionUntil;
use App\Modules\Subscriptions\Events\NewSubscription;
use App\Modules\Subscriptions\Events\NewSubscriptionUntil;
use App\Modules\Subscriptions\Events\UpgradeSubscription;
use App\Modules\Subscriptions\Events\UpgradeSubscriptionUntil;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Models\Subscription;
use Carbon\Carbon;

trait HasPlans
{

    public function upgradeCurrentPlanTo(
        $newPlan,
        int $duration = 30,
        bool $startFromNow = true,
        bool $isRecurring = true
    ): Subscription|false {
        if (!$this->hasActiveSubscription()) {
            return $this->subscribeTo($newPlan, $duration, $isRecurring);
        }

        if ($duration < 1) {
            return false;
        }

        $activeSubscription = $this->activeSubscription();
        $activeSubscription->load(['plan']);

        $subscription = $this->extendCurrentSubscriptionWith($duration, $startFromNow, $isRecurring);
        $oldPlan = $activeSubscription->plan;

        if ($subscription->plan_id != $newPlan->id) {
            $subscription->update([
                'plan_id' => $newPlan->id,
            ]);
        }

        event(
            new UpgradeSubscription(
                $this,
                $subscription,
                $startFromNow,
                $oldPlan,
                $newPlan
            )
        );

        return $subscription;
    }

    public function hasActiveSubscription(): bool
    {
        return (bool)$this->activeSubscription();
    }

    public function activeSubscription()
    {
        return $this->currentSubscription()->notCancelled()->first();
    }

    public function currentSubscription()
    {
        return $this->subscriptions()
            ->where('starts_on', '<', Carbon::now())
            ->where('expires_on', '>', Carbon::now());
    }

    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'subscriber');
    }

    public function subscribeTo($plan, int $duration = 30, bool $isRecurring = true): Subscription|false
    {

        if ($duration < 1 || $this->hasActiveSubscription()) {
            return false;
        }

        if ($this->hasDueSubscription()) {
            $this->lastDueSubscription()->delete();
        }

        $subscription = $this->subscriptions()->save(
            new Subscription([
                'plan_id'             => $plan->id,
                'starts_on'           => Carbon::now()->subSeconds(1),
                'expires_on'          => Carbon::now()->addDays($duration),
                'cancelled_on'        => null,
            ])
        );

        event(new NewSubscription($this, $subscription));

        return $subscription;
    }

    public function hasDueSubscription(): bool
    {
        return (bool)$this->lastDueSubscription();
    }

    public function lastDueSubscription(): Subscription|false
    {
        if (!$this->hasSubscriptions()) {
            return false;
        }

        if ($this->hasActiveSubscription()) {
            return false;
        }

        $lastActiveSubscription = $this->lastActiveSubscription();

        if (!$lastActiveSubscription) {
            return $this->lastUnpaidSubscription();
        }

        $lastSubscription = $this->lastSubscription();

        if ($lastActiveSubscription->is($lastSubscription)) {
            return false;
        }

        return $this->lastUnpaidSubscription();
    }

    public function hasSubscriptions(): bool
    {
        return $this->subscriptions()->count() > 0;
    }

    public function lastActiveSubscription(): Subscription|false
    {
        if (!$this->hasSubscriptions()) {
            return false;
        }

        if ($this->hasActiveSubscription()) {
            return $this->activeSubscription();
        }

        return $this->subscriptions()->latest('starts_on')->paid()->notCancelled()->first();
    }

    public function lastUnpaidSubscription(): ?Subscription
    {
        return $this->subscriptions()->latest('starts_on')->notCancelled()->unpaid()->first();
    }

    public function lastSubscription(): Subscription|false
    {
        if (!$this->hasSubscriptions()) {
            return false;
        }

        if ($this->hasActiveSubscription()) {
            return $this->activeSubscription();
        }

        return $this->subscriptions()->latest('starts_on')->first();
    }

    public function extendCurrentSubscriptionWith(
        int $duration = 30,
        bool $startFromNow = true,
        bool $isRecurring = true
    ): Subscription|false {
        if (!$this->hasActiveSubscription()) {
            if ($this->hasSubscriptions()) {
                $lastActiveSubscription = $this->lastActiveSubscription();
                $lastActiveSubscription->load(['plan']);

                return $this->subscribeTo($lastActiveSubscription->plan, $duration, $isRecurring);
            }

            return $this->subscribeTo(Plan::first(), $duration, $isRecurring);
        }

        if ($duration < 1) {
            return false;
        }

        $activeSubscription = $this->activeSubscription();

        if ($startFromNow) {
            $activeSubscription->update([
                'expires_on' => Carbon::parse($activeSubscription->expires_on)->addDays($duration),
            ]);

            event(
                new ExtendSubscription(
                    $this, $activeSubscription, $startFromNow, null
                )
            );

            return $activeSubscription;
        }

        $subscriptionModel = ('plans.models.subscription');

        $subscription = $this->subscriptions()->save(
            new $subscriptionModel([
                'plan_id'             => $activeSubscription->plan_id,
                'starts_on'           => Carbon::parse($activeSubscription->expires_on),
                'expires_on'          => Carbon::parse($activeSubscription->expires_on)->addDays($duration),
                'cancelled_on'        => null,
                'payment_method'      => ($this->subscriptionPaymentMethod) ?: null,
                'is_recurring'        => $isRecurring,
                'recurring_each_days' => $duration,
            ])
        );

        event(
            new ExtendSubscription(
                $this,
                $activeSubscription,
                $startFromNow,
                $subscription
            )
        );

        return $subscription;
    }

    public function upgradeCurrentPlanToUntil($newPlan, $date, bool $startFromNow = true, bool $isRecurring = true)
    {
        if (!$this->hasActiveSubscription()) {
            return $this->subscribeToUntil($newPlan, $date, $isRecurring);
        }

        $activeSubscription = $this->activeSubscription();
        $activeSubscription->load(['plan']);

        $subscription = $this->extendCurrentSubscriptionUntil($date, $startFromNow, $isRecurring);
        $oldPlan = $activeSubscription->plan;

        $date = Carbon::parse($date);

        if ($startFromNow) {
            if ($date->lessThanOrEqualTo(Carbon::now())) {
                return false;
            }
        }

        if (Carbon::parse($subscription->expires_on)->greaterThan($date)) {
            return false;
        }

        if ($subscription->plan_id != $newPlan->id) {
            $subscription->update([
                'plan_id' => $newPlan->id,
            ]);
        }

        event(
            new UpgradeSubscriptionUntil(
                $this,
                $subscription,
                $date,
                $startFromNow,
                $oldPlan,
                $newPlan
            )
        );

        return $subscription;
    }

    public function subscribeToUntil($plan, $date, bool $isRecurring = true)
    {

        $date = Carbon::parse($date);

        if ($date->lessThanOrEqualTo(Carbon::now()) || $this->hasActiveSubscription()) {
            return false;
        }

        if ($this->hasDueSubscription()) {
            $this->lastDueSubscription()->delete();
        }

        $subscription = $this->subscriptions()->save(
            new Subscription([
                'plan_id'             => $plan->id,
                'starts_on'           => Carbon::now()->subSeconds(1),
                'expires_on'          => $date,
                'cancelled_on'        => null,
                'payment_method'      => ($this->subscriptionPaymentMethod) ?: null,
                'is_paid'             => (bool)($this->subscriptionPaymentMethod) ? false : true,
                'charging_price'      => ($this->chargingPrice) ?: $plan->price,
                'charging_currency'   => ($this->chargingCurrency) ?: $plan->currency,
                'is_recurring'        => $isRecurring,
                'recurring_each_days' => Carbon::now()->subSeconds(1)->diffInDays($date),
            ])
        );


        event(new NewSubscriptionUntil($this, $subscription, $date));

        return $subscription;
    }

    public function extendCurrentSubscriptionUntil($date, bool $startFromNow = true, bool $isRecurring = true)
    {
        if (!$this->hasActiveSubscription()) {
            if ($this->hasSubscriptions()) {
                $lastActiveSubscription = $this->lastActiveSubscription();
                $lastActiveSubscription->load(['plan']);

                return $this->subscribeToUntil($lastActiveSubscription->plan, $date, $isRecurring);
            }

            return $this->subscribeToUntil(Plan::first(), $date, $isRecurring);
        }

        $date = Carbon::parse($date);
        $activeSubscription = $this->activeSubscription();

        if ($startFromNow) {
            if ($date->lessThanOrEqualTo(Carbon::now())) {
                return false;
            }

            $activeSubscription->update([
                'expires_on' => $date,
            ]);

            event(
                new ExtendSubscriptionUntil(
                    $this,
                    $activeSubscription,
                    $date,
                    $startFromNow,
                    null
                )
            );

            return $activeSubscription;
        }

        if (Carbon::parse($activeSubscription->expires_on)->greaterThan($date)) {
            return false;
        }

        $subscriptionModel = ('plans.models.subscription');

        $subscription = $this->subscriptions()->save(
            new $subscriptionModel([
                'plan_id'             => $activeSubscription->plan_id,
                'starts_on'           => Carbon::parse($activeSubscription->expires_on),
                'expires_on'          => $date,
                'cancelled_on'        => null,
                'payment_method'      => ($this->subscriptionPaymentMethod) ?: null,
                'is_recurring'        => $isRecurring,
                'recurring_each_days' => Carbon::now()->subSeconds(1)->diffInDays($date),
            ])
        );

        event(
            new ExtendSubscriptionUntil(
                $this,
                $activeSubscription,
                $date,
                $startFromNow,
                $subscription
            )
        );

        return $subscription;
    }

    public function cancelCurrentSubscription()
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        $activeSubscription = $this->activeSubscription();

        if ($activeSubscription->isCancelled() || $activeSubscription->isPendingCancellation()) {
            return false;
        }

        $activeSubscription->update([
            'cancelled_on' => Carbon::now(),
            'is_recurring' => false,
        ]);

        event(new CancelSubscription($this, $activeSubscription));

        return $activeSubscription;
    }

    public function renewSubscription()
    {
        if (!$this->hasSubscriptions()) {
            return false;
        }

        if ($this->hasActiveSubscription()) {
            return false;
        }

        if ($this->hasDueSubscription()) {
            return $this->chargeForLastDueSubscription();
        }

        $lastActiveSubscription = $this->lastActiveSubscription();

        if (!$lastActiveSubscription) {
            return false;
        }

        if (!$lastActiveSubscription->is_recurring || $lastActiveSubscription->isCancelled()) {
            return false;
        }

        $lastActiveSubscription->load(['plan']);
        $plan = $lastActiveSubscription->plan;
        $recurringEachDays = $lastActiveSubscription->recurring_each_days;

        if ($lastActiveSubscription->payment_method) {
            if (!$lastActiveSubscription->is_paid) {
                return false;
            }
        }

        return $this->subscribeTo($plan, $recurringEachDays);
    }

    public function expired()
    {
        return $this->expired_at->isPast();
    }

    public function notExpired()
    {
        return !$this->expired();
    }

}
