<?php

namespace Krak\Http\Route;

use Krak\Mw;

trait RouteAttributesTrait
{
    private $attributes;
    private $parent;

    /** accepts either a key and value, or an array of key values to set for the attributes */
    public function with($key, $value = null) {
        if (is_array($key)) {
            $this->attributes = array_merge($this->attributes, $key);
            return $this;
        }

        $this->attributes[$key] = $value;
        return $this;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function getParent() {
        return $this->parent;
    }
}
