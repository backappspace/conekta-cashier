<?php

namespace UvealSnow\ConektaCashier\Traits;

use Image;
use UvealSnow\ConektaCashier\Picture;
use Illuminate\Support\Facades\Storage;

trait UsesPicture
{
    /**
     * Morphs a picture
     *
     * @return Picture
     */
    public function picture()
    {
        return $this->morphOne(Picture::class, 'pictureable');
    }

    /**
     * Adds a picture to the Model
     *
     * @param Image $picture
     * @return string $url
     * @throws TransferException
     */
    public function addPicture($picture)
    {
        if ($this->picture) {
            $this->removePicture();
        }

        $new_img = Image::make($picture);
        $file_name = uniqid(strtolower(class_basename($this)).'-', true);

        $new_img->resize(400, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        $new_img->save("./tmp/$file_name.jpg");
        $new_img->destroy();

        $img = file_get_contents("./tmp/$file_name.jpg");
        $url = strtolower(class_basename($this))."/$file_name.jpg";

        try {
            Storage::disk('s3')->getDriver()->put(
                $url,
                $img,
                ['visibility' => 'public', 'CacheControl' => 'max_age=2592000']
            );

            return $url;
        } catch (Exception $e) {
            // Throws Transfer Exception
        }
    }

    /**
     * Determines if Model has a Picture
     *
     * @return boolean
     */
    public function hasPicture()
    {
        return (bool) $this->picture && Storage::exists($this->picture->path)
    }

    /**
     * Retrieves the picture URL from Storage
     *
     * @return string
     */
    public function getPicture()
    {
        if ($this->hasPicture()) {
            return Storage::url($this->picture->path);
        } else {
            return Storage::url(strtolower(class_basename($this)).'/placeholder.png');
        }
    }

    /**
     * Removes the picture from storage
     *
     * @return void
     */
    public function removePicture()
    {
        if ($this->hasPicture()) {
            $this->picture->delete();
        }
    }
}
