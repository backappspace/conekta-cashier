<?php

namespace UvealSnow\ConektaCashier;

class PaymentSource
{
    /**
     * The Stripe model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    /**
     * The Stripe card instance.
     *
     * @var \Stripe\Card
     */
    protected $paymentSource;

    /**
     * Create a new card instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @param  \Conekta\PaymentSource  $paymentSource
     * @return void
     */
    public function __construct($owner, $paymentSource)
    {
        $this->card = $paymentSource;
        $this->owner = $owner;
    }

    /**
     * Delete the card.
     *
     * @return \Stripe\Card
     */
    public function delete()
    {
        //
    }

    /**
     * Get the Conekta payment source instance.
     *
     * @return \Conekta\PaymentSource
     */
    public function asConektaPaymentSource()
    {
        //
    }

    /**
     * Dynamically get values from the Conekta payment source.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->paymentSource->{$key};
    }
}
