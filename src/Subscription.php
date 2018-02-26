<?php

namespace UvealSnow\ConektaCashier;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'starts_at', 'ends_at', 'created_at', 'updated_at',
    ];

    /**
     * Indicates if the plan change should be prorated.
     *
     * @var bool
     */
    protected $prorate = true;

    /**
     * The date on which the billing cycle should be anchored.
     *
     * @var string|null
     */
    protected $billingCycleAnchor = null;

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->owner();
    }

    /**
     * Get the model related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        $model = getenv('CONEKTA_MODEL') ?: config('services.conekta.model', 'App\\User');
        $model = new $model;
        return $this->belongsTo(get_class($model), $model->getForeignKey());
    }

    /**
     * Gets the Orders related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }

    /**
     * Determine if the subscription is active, on trial, or within its grace period.
     *
     * @return bool
     */
    public function valid()
    {
        return (bool) $this->active() || $this->onTrial() || $this->onGracePeriod();
    }

    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function active()
    {
        return (bool) now()->lessThan($this->ends_at) && $this->status === 'active';
    }

    /**
     * Determine if the subscription is no longer active.
     *
     * @return bool
     */
    public function cancelled()
    {
        return (bool) now()->greaterThanOrEqualTo($this->ends_at) || $this->status === 'cancelled';
    }

    /**
     * Determine if the subscription is within its trial period.
     *
     * @return bool
     */
    public function onTrial()
    {
        return (bool) now()->lessThan($this->starts_at) && $this->status === 'active';
    }

    /**
     * Determine if the subscription is within its grace period after cancellation.
     *
     * @return bool
     */
    public function onGracePeriod()
    {
        return (bool) now()->lessThan($this->ends_at) && $this->status === 'cancelled';
    }

    /**
     * Increment the quantity of the subscription.
     *
     * @param  int  $count
     * @return $this
     */
    public function incrementQuantity($count = 1)
    {
        $this->quantity += $count;
        return $this;
    }

    /**
     * Decrement the quantity of the subscription.
     *
     * @param  int  $count
     * @return $this
     */
    public function decrementQuantity($count = 1)
    {
        $this->quantity -= $count;
        return $this;
    }

    /**
     * Update the quantity of the subscription.
     *
     * @param  int  $quantity
     * @param  \Stripe\Customer|null  $customer
     * @return $this
     */
    public function updateQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Indicate that the plan change should not be prorated.
     *
     * @return $this
     */
    public function noProrate()
    {
        $this->prorated = false;
        return $this;
    }

    /**
     * Change the billing cycle anchor on a plan change.
     *
     * @param  \DateTimeInterface|int|string  $date
     * @return $this
     */
    public function anchorBillingCycleOn($date = 'now')
    {
        //
    }

    /**
     * Force the trial to end immediately.
     *
     * This method must be combined with swap, resume, etc.
     *
     * @return $this
     */
    public function skipTrial()
    {
        $this->starts_at = now();
        return $this;
    }

    /**
     * Swap the subscription to a new Conekta plan.
     *
     * @param  string  $plan
     * @return $this
     */
    public function swap($plan)
    {
        //
    }

    /**
     * Cancel the subscription at the end of the billing period.
     *
     * @return $this
     */
    public function cancel()
    {
        $this->status = 'cancelled';

        if ($this->onTrial()) {
            $this->ends_at = $this->trial_ends_at;
        }

        $this->save();

        return $this;
    }

    /**
     * Cancel the subscription immediately.
     *
     * @return $this
     */
    public function cancelNow()
    {
        $this->fill([
            'status' => 'cancelled',
            'ends_at' => now(),
        ]);

        $this->save();

        return $this;
    }

    /**
     * Resume the cancelled subscription.
     *
     * @return $this
     *
     * @throws \LogicException
     */
    public function resume()
    {
        if (!$this->onGracePeriod()) {
            // throw LogicException
        }

        $this->fill([
            'status' => 'active',
            'trial_ends_at' => now(),
        ]);
    }

    /*
     * Marks the subscription as unpaid
     *
     * @return $this;
    */
    public function markAsUnpaid()
    {
        $this->fill([
            'status' => 'unpaid',
            'rejected_payments' => $this->rejected_payments++
        ]);

        if ($this->rejected_payments >= 3) {
            $this->cancel();
        } else {
            $this->save();
        }

        return $this;
    }

     /**
     * Creates a lineItem array for the current subscription
     *
     * @return array
     */
    public function asLineItem()
    {
        return [
            "name" => $this->name,
            "unit_price" => intval($this->unit_price),
            "quantity" => intval($this->quantity),
        ];
    }

     /**
     * Checks if the subscription is still valid
     *
     * @return bool
     */
    public function isValid()
    {
        return (bool) ($this->onTrial() || $this->onGracePeriod()) ||
            ($this->status === 'active' &&
            now()->lt($this->ends_at->addDays(config('services.conekta.days_of_tolerance', 5))));
    }

    /**
    * Checks if the subscription should be charged
    *
    * @return bool
    */
    public function shouldBeCharged() : bool
    {
        return $this->status == 'active' && now()->gte($this->ends_at) && !$this->alreadyCharged();
    }

    /**
     * Checks if the subscription has been charged for the given period
     *
     * @param Carbon\Carbon $date = null
     * @return boolean
     */
    public function alreadyCharged($date = null)
    {
        if (!$date) {
            $date = $this->ends_at;
        }

        return (bool) $this->orders()
                        ->whereBetween('created_at', [$date, $this->getNextEndDate(false, $date)])
                        ->get()
                        ->count() > 0;
    }

    /**
     * Returns the next ends_at date for this subscription
     *
     * @param boolean $now = true
     * @return Carbon $ends_at
     */
    public function getNextEndDate($now = true, $special = null)
    {
        if ($now) {
            $date = now();
        } elseif ($special) {
            $date = $special;
        } else {
            $date = $this->ends_at;
        }

        if ($this->period_unit === 'day') {
            $ends_at = $date->addDays($this->period_amount);
        } elseif ($this->period_unit === 'week') {
            $ends_at = $date->addWeeks($this->period_amount);
        } elseif ($this->period_unit === 'month') {
            $ends_at = $date->addMonths($this->period_amount);
        } elseif ($this->period_unit === 'year') {
            $ends_at = $date->addYears($this->period_amount);
        }

        return $ends_at;
    }
}
