<?php

namespace UvealSnow\ConektaCashier;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Picture extends Model
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

    /*
     * Get the model related to this picture
     *
     * @return Collection
    */
    public function pictureable()
    {
        return $this->morphTo();
    }

    /*
     * Steps to perform when model is deleted.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function (Picture $picture) {
            if (Storage::exists($picture->path)) {
                Storage::delete($picture->path);
            }
        });
    }
}
