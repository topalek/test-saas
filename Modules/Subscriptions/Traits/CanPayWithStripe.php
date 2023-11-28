<?php

namespace App\Modules\Subscriptions\Traits;

use App\Modules\Subscriptions\Helpers\StripeHelper;
use Carbon\Carbon;
use Stripe\Charge as StripeCharge;
use Stripe\Customer as StripeCustomer;
use Stripe\Stripe;

trait CanPayWithStripe
{
    protected $subscriptionPaymentMethod = null;
    protected $chargingPrice             = null;
    protected $chargingCurrency          = null;
    protected $stripeToken               = null;

    /**
     * Delete the local Stripe Customer.
     *
     * @return bool
     */
    public function deleteStripeCustomer()
    {
        if (!$this->isStripeCustomer()) {
            return false;
        }

        return (bool)$this->stripeCustomer()->delete();
    }

    /**
     * Check if the model is already stored as a customer.
     *
     * @return bool
     */
    public function isStripeCustomer()
    {
        return (bool)($this->stripeCustomer()->count() == 1);
    }

    /**
     * Get the Stripe Customer relationship.
     *
     * @return morphOne The relationship.
     */
    public function stripeCustomer()
    {
        return $this->morphOne(config('plans.models.stripeCustomer'), 'model');
    }

    /**
     * Set Stripe as payment method.
     *
     * @return void
     */
    public function withStripe()
    {
        $this->subscriptionPaymentMethod = 'stripe';

        return $this;
    }

    /**
     * Set Stripe token.
     *
     * @return self
     */
    public function withStripeToken(string $stripeToken = null)
    {
        $this->stripeToken = $stripeToken;

        return $this;
    }

    /**
     * Change the price on demand for subscriptions.
     *
     * @return self
     */
    public function setChargingPriceTo(float $chargingPrice, string $chargingCurrency)
    {
        $this->chargingPrice = $chargingPrice;
        $this->chargingCurrency = $chargingCurrency;

        return $this;
    }

    /**
     * Charge the user for the last due subscription and renew on succesful payment.
     *
     * @return bool|PlanSubscriptionModel
     */
    public function chargeForLastDueSubscription($callback = null)
    {
        $lastDueSubscription = $this->lastDueSubscription();

        if (!$lastDueSubscription) {
            return false;
        }

        $lastDueSubscription->load(['plan']);
        $plan = $lastDueSubscription->plan;

        if (!is_callable($callback)) {
            try {
                $stripeCharge = $this->chargeWithStripe(
                    ($this->chargingPrice) ?: $plan->price,
                    ($this->chargingCurrency) ?: $plan->currency
                );

                $lastDueSubscription->update([
                    'is_paid'    => true,
                    'starts_on'  => Carbon::now(),
                    'expires_on' => Carbon::now()->addDays($lastDueSubscription->recurring_each_days),
                ]);

                event(
                    new \App\Modules\Subscriptions\Events\Stripe\DueSubscriptionChargeSuccess(
                        $this,
                        $lastDueSubscription,
                        $stripeCharge
                    )
                );
            } catch (\Exception $exception) {
                event(
                    new \App\Modules\Subscriptions\Events\Stripe\DueSubscriptionChargeFailed(
                        $this,
                        $lastDueSubscription,
                        $exception
                    )
                );

                return false;
            }
        }

        if (is_callable($callback)) {
            return call_user_func($callback, $lastDueSubscription);
        }

        return $lastDueSubscription;
    }

    /**
     * Initiate a charge with Stripe.
     *
     * @param float  $amount      The amount charged.
     * @param string $currency    The currency code.
     * @param string $description The description of the payment. (optional)
     *
     * @return Stripe\Charge
     */
    public function chargeWithStripe(float $amount, string $currency, string $description = null)
    {
        $customer = $this->getStripeCustomer();

        if (!$customer) {
            $customer = $this->createStripeCustomer();
        }

        $this->initiateStripeAPI();

        return StripeCharge::create([
            'amount'      => StripeHelper::fromRealAmountToStripe($amount, $currency),
            'currency'    => $currency,
            'description' => $description,
            'source'      => $this->stripeToken,
        ]);
    }

    /**
     * Get the local Stripe Customer instance.
     *
     * @return null|StripeCustomerModel The Stripe Customer instance.
     */
    public function getStripeCustomer()
    {
        if (!$this->isStripeCustomer()) {
            return;
        }

        return $this->stripeCustomer()->first();
    }

    /**
     * Create a local Stripe Customer instance.
     *
     * @return bool|StripeCustomerModel Fresh Stripe Customer instance, or false on error.
     */
    public function createStripeCustomer()
    {
        if ($this->isStripeCustomer()) {
            return $this->getStripeCustomer();
        }

        $this->initiateStripeAPI();

        try {
            $customer = StripeCustomer::create([]);
        } catch (\Exception $e) {
            return false;
        }

        $model = config('plans.models.stripeCustomer');

        return $this->stripeCustomer()->save(
            new $model([
                'customer_id' => $customer->id,
            ])
        );
    }

    /**
     * Initiate the Stripe API key.
     *
     * @return Stripe
     */
    public function initiateStripeAPI()
    {
        if (getenv('STRIPE_SECRET')) {
            return Stripe::setApiKey(getenv('STRIPE_SECRET'));
        }

        return Stripe::setApiKey((env('STRIPE_SECRET')) ?: config('services.stripe.secret'));
    }
}
