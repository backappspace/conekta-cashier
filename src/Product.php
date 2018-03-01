<?php

namespace UvealSnow\ConektaCashier;

use Illuminate\Database\Eloquent\Model;
use UvealSnow\ConektaCashier\Traits\UsesPictures;

class Product extends Model
{
    use UsesPictures;

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
    protected $dates = ['visible_from', 'visible_until', 'created_at', 'updated_at'];

    /*
     * The carts this product is included in.
     *
     * @return Collection
     */
    public function carts()
    {
        return $this->belongsToMany(Cart::class)->withPivot(
            'quantity',
            'unit_price',
            'details'
        );
    }

    /*
     * The orders this product is included in.
     *
     * @return Collection
     */
    public function orders()
    {
        return $this->morphToMany(Order::class, 'orderable')->withPivot(
            'quantity',
            'unit_price',
            'details'
        );
    }

    /*
     * Steps to perform when model is deleted.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function (Product $product) {
            if($product->hasPictures()) {
                $product->picture
            }
        });
    }
}
