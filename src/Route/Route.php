<?php

namespace Krak\Http\Route;

class Route implements RouteAttributes
{
    use RouteAttributesTrait;

    public $methods;
    public $path;
    public $handler;

    public function __construct(array $methods, $path, $handler, RouteAttributes $parent = null) {
        $this->methods = $methods;
        $this->path = $path;
        $this->handler = $handler;
        $this->attributes = [];
        $this->parent = $parent;
    }

    public function withPath($path) {
        $route = clone $this;
        $route->path = $path;
        return $route;
    }

    public function withHandler($handler) {
        $route = clone $this;
        $route->handler = $handler;
        return $route;
    }
}
