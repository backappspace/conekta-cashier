<?php

namespace UvealSnow\ConektaCashier;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
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
    protected $dates = ['valid_from', 'valid_until', 'created_at', 'updated_at'];

    /**
     * Get the carts related to the coupon.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function carts()
    {
        return $this->belongsToMany(Cart::class)->withPivot('user_id')->withTimestamps();
    }
}
