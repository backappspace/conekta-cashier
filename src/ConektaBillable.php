<?php

namespace UvealSnow\ConektaCashier;

use Exception;
use Conekta\Conekta;
use Conekta\Customer;
use Conekta\Order as ConektaOrder;
use UvealSnow\ConektaCashier\Cart;
use UvealSnow\ConektaCashier\Order;
use UvealSnow\ConektaCashier\Subscription;

trait ConektaBillable
{
    /**
     * The Stripe API key.
     *
     * @var string
     */
    protected static $conektaKey;

    /**
     * The Conekta Customer
     *
     * @var Conekta\Customer
     */
    protected $conektaCustomer;

    /**
     * Initializes the Conekta library with the Conekta Private Key
     *
     * @return void
     */
    public function initConekta()
    {
        Conekta::setApiKey($this->getConektaKey());
    }

    /**
     * Get the cart for the Conekta model.
     *
     * @return \UvealSnow\ConektaCashier
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get or create a cart for the user
     *
     * @return \UvealSnow\ConektaCashier\Cart
    */
    public function getCart()
    {
        return $this->cart()->firstOrCreate([
            "amount" => 0,
        ]);
    }

    /**
     * Get all of the orders of the Conekta model.
     *
     * @return \UvealSnow\ConektaCashier\Order
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all of the subscriptions of the Conekta model.
     *
     * @return \UvealSnow\ConektaCashier\Subscription
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Retrieves the Conekta customer for the model.
     *
     * @return \Conekta\Customer
     */
    public function asConektaCustomer($refresh = false)
    {
        if (!$this->conekta_client_id) {
            $this->createAsConektaCustomer();
        }

        if (!$this->conektaCustomer instanceof Customer || $refresh) {
            $this->conektaCustomer = Customer::find($this->conekta_client_id);
        }

        return $this->conektaCustomer;
    }

    /**
     * Creates a Customer for the Model
     *
     * @return $this
     */
    public function createAsConektaCustomer()
    {
        $this->conektaCustomer = Customer::create([
            "name" => $this->name,
            "email" => $this->email,
        ]);

        $this->conekta_client_id = $this->conektaCustomer->id;

        $this->save();

        return $this;
    }

    /**
     * Make an order for the conekta user.
     *
     * @param  array  $lineItems
     * @param  array  $options|[]
     * @return \Conekta\Order
     *
     * @throws \Conekta\ProcessingError
     * @throws \Conekta\ParameterValidationError
     * @throws \Conekta\Handler

        $options = [
            'tax_lines' => [
                'description' => string,
                'amount' => integer|cents,
            ],
            'shipping_lines' => [
                'amount' => integer|cents,
                'tracking_number' => string,
                'carrier' => string,
                'method' => string|nullable,
            ],
            'discount_lines' => [
                'code' => string,
                'amount' => integer|cents,
                'type' => string|in:loyalty,campaign,coupon,sign,
            ]
        ];

     */
    public function createConektaOrder($lineItems, $options = [])
    {
        $customer = $this->asConektaCustomer();

        $order = [
            "currency" => config("services.conekta.currency", 'MXN'),
            "customer_info" => [
                "customer_id" => $customer->id,
            ],
            "line_items" => $lineItems,
        ];

        foreach ($options as $key => $value) {
            $order[$key] = $value;
        }

        return ConektaOrder::create($order);
    }

    /**
     * Charges a Conketa Order
     *
     * @param \Conekta\Order $order
     * @param array $paymentSource|null
     * @return Conekta\Charge
     *
     * @throws \InvalidArgumentException
     * @throws \ProcessingError
     * @throws \ParameterValidationError
     */
    public function chargeOrder($order, $paymentSource = null)
    {
        if ($paymentSource) {
            // type = card|oxxo_cash|spei
            $payment_method = [
                "type" => $paymentSource['type'],
            ];

            if ($paymentSource['type'] == 'card') {
                // If it's a new card
                if (isset($paymentSource['token_id'])) {
                    // Add new Card to customer.
                    $source = $customer->createPaymentSource([
                        "token_id" => $paymentSource['token_id'],
                        "type" => "card",
                    ]);
                } else {
                    $source = $this->getPaymentSourceById($paymentSource['source_id']);
                }

                // Add the card as the Charge payment method.
                $payment_method['payment_source_id'] = $source->id;
            } elseif ($paymentSource['type'] == 'oxxo_cash' || $paymentSource['type'] == 'spei') {
                // Add expiry date if specified.
                if (isset($paymentSource['expires_at'])) {
                    $payment_method['expires_at'] = $paymentSource['expires_at'];
                }
            }
        } else {
            $payment_method = ["type" => "default"];
        }

        $charge = $order->createCharge([
            "payment_method" => $payment_method,
        ]);

        // Delete the card as payment source if saving is not specified.
        if ($paymentSource['type'] == 'card' && (!isset($paymentSource['save']) || !$paymentSource['save'])) {
            $source->delete();
        }

        return $charge;
    }

    /**
     * Gets the Customer's payment sources.
     *
     * @return array $paymentSources
     */
    public function getPaymentSources()
    {
        $customer = $this->asConektaCustomer();

        return  $customer->payment_sources;
    }

    /**
     * Get a payment source by id
     *
     * @return \Conekta\PaymentSource|void
     *
     * @throws \UvealSnow\ValidationExeption
     */
    public function getPaymentSourceById($id)
    {
        $customer = $this->asConektaCustomer();

        $sources = $this->getPaymentSources();

        foreach ($sources as $source) {
            if ($id == $source->id) {
                return $source;
            }
        }

        // throw new ValidationExeption("No hay método de pago especificado", 1);
    }

    /**
     * Get the Conekta API key.
     *
     * @return string
     */
    public static function getConektaKey()
    {
        if (static::$conektaKey) {
            return static::$conektaKey;
        }

        if ($key = getenv('CONEKTA_PRIVATE')) {
            return $key;
        }

        return config('services.conekta.private');
    }

    /**
     * Returns true if user is currently on generic trial
     *
     * @return bool
     */
    public function onGenericTrial()
    {
        return (bool) $this->trial_ends_at && now()->lt($this->trial_ends_at);
    }

    /**
     * Returns an array with the subscriptions as line items
     *
     * @return array $lineItems
     */
    public function subscriptionsAsLineItems()
    {
        $lineItems = [];

        foreach ($this->subscriptions as $subscription) {
            $lineItems[] = $subscription->asLineItem();
        }

        return $lineItems;
    }
}
