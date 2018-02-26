<?php

namespace UvealSnow\ConektaCashier;

use Conekta\Order as ConektaOrder;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
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
    protected $dates = ['estimated_delivery', 'created_at', 'updated_at'];

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->owner();
    }

    /**
     * Get the model related to the order.
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
     * Get the products related to the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        $model = getenv('CONEKTA_PRODUCT') ?: config('services.conekta.product', 'Product');

        $model = new $model;

        // return $this->hasMany(get_class($model), $model->getForeignKey());
        return $this->morphedByMany(get_class($model), $model->getForeignKey());
    }

    /**
     * Get the products related to the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions()
    {
        $model = getenv('CONEKTA_SUBSCRIPTION') ?: config('services.conekta.subscription', 'Subscription');

        $model = new $model;

        // return $this->hasMany(get_class($model), $model->getForeignKey());
        return $this->morphedByMany(get_class($model), $model->getForeignKey());
    }

    /**
     * Creates the Conekta Order linked to this model
     *
     * @return \Conekta\Order
     */
    // public function createAsConektaOrder()
    // {
    //     $customer = $this->owner->asConektaCustomer();

    //     $order = ConektaOrder::create([
    //         "currency" => config("services.conekta.currency", 'MXN'),
    //         "customer_info" => [
    //             "customer_id" => $customer->id,
    //         ],
    //         "line_items" => $this->productsAsLineItems(),
    //     ]);

    //     $this->conekta_order = $order->id;

    //     $this->save();

    //     return $order;
    // }

    /**
     * Returns the Conekta Order linked to this model
     *
     * @return \Conekta\Order|void
     */
    public function asConektaOrder()
    {
        return ConektaOrder::find($this->conekta_order);
    }

    /**
     * Fills the a Conekta Order to be saved to the Database
     *
     * @param Conekta\Order $order
     * @return void
     */
    public function fillOrder(ConektaOrder $order)
    {
        $this->fill([
            'conekta_order' => $order->id,
            'currency' => config('services.conekta.currency', 'MXN'),
            'amount' => $order->amount,
            'tax' => $order->tax_lines->total,
            'shipping_cost' => $order->shipping_lines->total,
            'discount' => $order->discount_lines->total,
            'monthly_installments' => count($order->charges) > 0 ? $order->charges->monthly_installments : 0,
            'payment_method' => count($order->charges) > 0 ? $order->charges[0]->payment_method->type : 'default',
            'status' => count($order->charges) > 0 ? $order->charges[0]->status : 'unknown',
            'tracking_number' => null,
            'estimated_delivery' => null,
        ]);

        return $this;
    }
}
