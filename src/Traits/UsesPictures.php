<?php

namespace UvealSnow\ConektaCashier\Traits;

use Image;
use UvealSnow\ConektaCashier\Picture;
use Illuminate\Support\Facades\Storage;

trait UsesPictures
{
    /**
     * Morphs many pictures
     *
     * @return UvealSnow\ConektaCashier\Picture
     */
    public function pictures()
    {
        return $this->morphMany(Picture::class, 'pictureable');
    }

    /**
     * Checks if the model has any pictures attached
     *
     * @return boolean
     */
    public function hasPictures()
    {
        return $this->pictures->count() > 0;
    }

    /**
     * Adds a picture to the model and stores it in the Filesystem
     *
     * @param Image $picture
     * @param boolean $main = false
     * @return string $url
     */
    public function addPicture($picture, $main = false)
    {
        $new_img = Image::make($picture);
        $file_name = uniqid(strtolower(class_basename($this)).'-', true);

        $new_img->resize(400, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        $new_img->save("./tmp/$file_name.jpg");
        $new_img->destroy();

        $img = file_get_contents("./tmp/$file_name.jpg");
        $url = strtolower(class_basename($this))."/$file_name.jpg";

        Storage::disk('s3')->getDriver()->put(
            $url,
            $img,
            ['visibility' => 'public', 'CacheControl' => 'max_age=2592000']
        );

        if ($main && $this->hasMainPicture()) {
            $this->removeMainPicture();
        }

        $this->pictures()->create([
            'path' => $url,
            'main' => $main,
        ]);

        return $url;
    }

    /**
     * Checks if the model has a picture marked as main
     *
     * @return boolean
     */
    public function hasMainPicture() : bool
    {
        return $this->pictures()->where('main', true)->get() > 0;
    }

    /**
     * Gets the picture marked as main for his model
     *
     * @param boolean $as_url
     * @return Picture|false
     */
    public function getMainPicture($as_url = false)
    {
        if ($this->hasPictures() && $this->hasMainPicture()) {
            if ($as_url) {
                return Storage::url($this->pictures()->where('main', true)->first()->path);
            }

            return $this->pictures()->where('main', true)->first();
        }

        return false;
    }

    /**
     * Modifies the picture model to remove the main trait, optionally can delete the picture.
     *
     * @param boolean $remove = false
     * @return void
     */
    public function removeMainPicture($remove = false)
    {
        if ($remove) {
            $this->removePicture($this->getMainPicture());
        } else {
            $main = $this->getMainPicture();

            $main->main = false;

            $main->save();
        }
    }

    /**
     * Removes a specific picture from storage
     *
     * @param Picture $picture
     * @return void
     */
    public function removePicture(Picture $picture)
    {
        $picture->delete();
    }

    /**
     * Removes a bunch of pictures from storage
     *
     * @param array $pictures = []
     * @return void
     */
    public function removePictures($pictures = [])
    {
        if (count($pictures) < 1) {
            $pictures = $this->pictures();
        }

        foreach ($pictures as $picture) {
            $this->removePicture($picture);
        }
    }

    /**
     * Transforms the relationship to a collection for display
     *
     * @return Collection $collection
     */
    public function asPictureCollection()
    {
        $collection = collect();

        foreach ($this->pictures as $picture) {
            $collection->push([
                'main' => $picture->main,
                'path' => Storage::url($picture->path)
            ]);
        }

        return $collection;
    }
}
