<?php

namespace UvealSnow\ConektaCashier;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
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
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Get the model related to the cart.
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
     * Get the coupons related to the cart.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class)->withPivot('user_id')->withTimestamps();
    }

    /**
     * Get the coupons related to the cart.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot(
            'quantity',
            'unit_price',
            'details'
        );
    }

    /**
     * Adds the given Product to the cart.
     *
     * @param integer $product_id
     * @param integer $quantity|null
     * @return void
     */
    public function addProductToCart($product_id, $quantity = 1)
    {
        if ($this->products->contains('id', $product_id)) {
            $product = $this->products->where('id', $product_id)->first();

            return $this->products()->updateExistingPivot($product_id, [
                "unit_price" => $product->unit_price,
                "quantity" => $product->pivot->quantity + $quantity,
            ]);
        }

        $product = Product::findOrFail($product_id);

        return $this->products()->attach($product_id, [
            "unit_price" => $product->unit_price,
            "quantity" => $quantity,
        ]);
    }

    /**
     * Removes the given Product from the cart.
     *
     * @param integer $product_id
     * @param integer $quantity|null
     * @return void
     */
    public function removeProductFromCart($product_id, $quantity = null)
    {
        if ($this->products->contains('id', $product_id)) {
            $product = $this->products->where('id', $product_id)->first();

            if ($quantity && $quantity < $product->pivot->quantity) {
                return $this->products()->updateExistingPivot($product_id, [
                    "quantity" => $product->pivot->quantity - $quantity,
                ]);
            }

            return $this->products()->detach($product_id);
        }
    }

    /**
     * Updates quantities for the cart.
     *
     * @return $this
     */
    public function updateCartAmounts()
    {
        $this->updateLineItemsTotal();
        $this->updateTaxLinesTotal();
        // $this->updateDiscountLinesTotal();
        // $this->updateShippingLinesTotal();

        $this->save();

        return $this;
    }

    /**
     * Updates quantities for the cart.
     *
     * @return $this
     */
    public function updateLineItemsTotal()
    {
        $this->load('products');

        $products = $this->products;

        $this->amount = $products->sum(function ($product) {
            return $product->pivot->quantity * $product->pivot->unit_price;
        });

        return $this->amount;
    }

    /**
     * Updates quantities for the cart.
     *
     * @return $this
     */
    public function updateTaxLinesTotal()
    {
        $this->load('products');

        $products = $this->products;

        $this->tax = $products->sum(function ($product) {
            return intval(
                $product->pivot->quantity * $product->pivot->unit_price * config('services.conekta.tax', .16)
            );
        });

        return $this->tax;
    }

    /**
     * Updates quantities for the cart.
     *
     * @return $this
     */
    public function updateDiscountLinesTotal()
    {
        $this->load('products');

        $products = $this->products;

        $this->amount = $products->sum(function ($product) {
            return $product->pivot->quantity * $product->pivot->unit_price;
        });

        return $this->amount;
    }

    /**
     * Updates quantities for the cart.
     *
     * @return $this
     */
    public function updateShippingLinesTotal()
    {
        $this->load('products');

        $products = $this->products;

        $this->amount = $products->sum(function ($product) {
            return $product->pivot->quantity * $product->pivot->unit_price;
        });

        return $this->amount;
    }

    /**
     * Converts all the products related to the cart to a line_items array.
     *
     * @return array
     */
    public function productsToConektaFormat()
    {
        $line_items = [];

        foreach ($this->products as $product) {
            $line_items[] = [
                "name" => $product->name,
                "unit_price" => $product->pivot->unit_price,
                "quantity" => $product->pivot->quantity
            ];
        }

        return $line_items;
    }

    /*
     * Steps to perform when model is deleted.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function (Cart $cart) {
            $cart->products()->detach();
            $cart->coupons()->detach();
        });
    }
}
