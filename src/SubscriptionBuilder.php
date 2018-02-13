<?php

namespace UvealSnow\ConektaCashier;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SubscriptionBuilder
{
    /**
     * The model that is subscribing.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    /**
     * The name of the subscription.
     *
     * @var string
     */
    protected $name;

    /**
     * The name of the plan being subscribed to.
     *
     * @var string
     */
    protected $plan;

    /**
     * The quantity of the subscription.
     *
     * @var int
     */
    protected $quantity = 1;

    /**
     * The date and time the trial will expire.
     *
     * @var \Carbon\Carbon
     */
    protected $trialExpires;

    /**
     * Indicates that the trial should end immediately.
     *
     * @var bool
     */
    protected $skipTrial = false;

    /**
     * The discount code being applied to the customer.
     *
     * @var string|null
     */
    protected $discount;

    /**
     * The metadata to apply to the subscription.
     *
     * @var array|null
     */
    protected $metadata;

    /**
     * Create a new subscription builder instance.
     *
     * @param  mixed  $owner
     * @param  string  $name
     * @param  string  $plan
     * @return void
     */
    public function __construct($owner, $name, $plan)
    {
        $this->name = $name;
        $this->plan = $plan;
        $this->owner = $owner;
    }

    /**
     * Specify the quantity of the subscription.
     *
     * @param  int  $quantity
     * @return $this
     */
    public function quantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Specify the number of days of the trial.
     *
     * @param  int  $trialDays
     * @return $this
     */
    public function trialDays($trialDays)
    {
        $this->trialExpires = Carbon::now()->addDays($trialDays);
        return $this;
    }

    /**
     * Specify the ending date of the trial.
     *
     * @param  \Carbon\Carbon  $trialUntil
     * @return $this
     */
    public function trialUntil(Carbon $trialUntil)
    {
        $this->trialExpires = $trialUntil;
        return $this;
    }

    /**
     * Force the trial to end immediately.
     *
     * @return $this
     */
    public function skipTrial()
    {
        $this->skipTrial = true;
        return $this;
    }

    /**
     * The discount to apply to a new subscription.
     *
     * @param  string  $coupon
     * @return $this
     */
    public function withDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * The metadata to apply to a new subscription.
     *
     * @param  array  $metadata
     * @return $this
     */
    public function withMetadata($metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Add a new Conekta subscription to the Conekta model.
     *
     * @param  array  $options
     * @return \UvealSnow\ConektaCashier\Subscription
     */
    public function add(array $options = [])
    {
        return $this->create(null, $options);
    }

    /**
     * Create a new Conekta subscription.
     *
     * @param  string|null  $token
     * @param  array  $options
     * @return \Laravel\Cashier\Subscription
     */
    public function create($token = null, array $options = [])
    {
        $customer = $this->getConektaCustomer($token, $options);
    }

    /**
     * Get the Conekta customer instance for the current user and token.
     *
     * @param  string|null  $token
     * @param  array  $options
     * @return \Conekta\Customer
     */
    protected function getConektaCustomer($token = null, array $options = [])
    {
        if (!$this->owner->customer_id) {
            $customer = $this->owner->createAsConektaCustomer($token, $options);
        } else {
            $customer = $this->owner->asConektaCustomer();

            if ($token) {
                // $this->owner->updateCard($token);
            }
        }

        return $customer;
    }

    /**
     * Build the payload for subscription creation.
     *
     * @return array
     */
    protected function buildPayload()
    {
        //
    }

    /**
     * Get the trial ending date for the Conekta payload.
     *
     * @return int|null
     */
    protected function getTrialEndForPayload()
    {
        //
    }

    /**
     * Get the tax percentage for the Conekta payload.
     *
     * @return int|null
     */
    protected function getTaxPercentageForPayload()
    {
        //
    }
}
