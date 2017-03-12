<?php

namespace Krak\Http\Dispatcher;

use Krak\Http\Route\Route;

class MatchedRoute
{
    public $params;
    public $route;

    public function __construct($params, Route $route) {
        $this->params = $params;
        $this->route = $route;
    }

    public function withRoute(Route $route) {
        $matched = clone $this;
        $matched->route = $route;
        return $matched;
    }
}
