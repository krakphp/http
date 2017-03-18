<?php

namespace Krak\Http\Dispatcher\FastRoute;

use Krak\Http;
use FastRoute;

class FastRouteDispatcherFactory implements Http\DispatcherFactory
{
    public function createDispatcher($routes) {
        $dispatcher = FastRoute\simpleDispatcher(function($route_collector) use ($routes) {
            foreach ($routes as $r) {
                $route_collector->addRoute($r->methods, $r->path, $r);
            }
        });

        return new FastRouteDispatcher($dispatcher);
    }
}
