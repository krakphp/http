<?php

namespace Krak\Http\Route;

use Closure;

class RouteGroup implements RouteAttributes
{
    use RouteAttributesTrait;
    use RouteMatch;

    private $routes;

    public function __construct($prefix, RouteAttributes $parent = null) {
        $this->attributes = [
            'path_prefix' => $prefix,
        ];
        $this->parent = $parent;
        $this->routes = [];
    }

    public function match($method, $path, $handler) {
        if (!is_array($method)) {
            $method = [$method];
        }

        $route = new Route(
            $method,
            $path,
            $handler,
            $this
        );
        $this->routes[] = $route;
        return $route;
    }

    public function group($path, Closure $add_routes) {
        $group = new self($path, $this);
        $add_routes($group);
        $this->routes[] = $group;
        return $group;
    }

    public function getRoutes() {
        return $this->routes;
    }

    public function getPathPrefix() {
        return $this->attributes['path_prefix'];
    }
}
