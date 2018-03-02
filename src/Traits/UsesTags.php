<?php

namespace UvealSnow\ConektaCashier\Traits;

trait UsesTags
{
    /**
     * Returns an array of the tags or the model
     *
     * @return array $tags
     */
    public function getTagArray()
    {
        return json_decode($this->tags);
    }

    /**
     * Transforms an array to JSON and sets them in the model
     *
     * @param array $tags
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = json_encode($tags);

        return $this;
    }
}
