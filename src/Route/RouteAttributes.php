<?php

namespace Krak\Http\Route;

interface RouteAttributes
{
    /** accepts either a key and value, or an array of key values to set for the attributes */
    public function with($key, $value = null);
    public function getAttributes();
    public function getParent();
}
