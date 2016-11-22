<?php

namespace Krak\Mw\Http\Routing;

use Psr\Http\Message\ServerRequestInterface;

interface Dispatcher {
    /** @return DispatchResult */
    public function __invoke(ServerRequestInterface $req);
}

interface DispatcherFactory {
    public function __invoke(RouteGroup $routes);
}

function fastRouteDispatcher(\FastRoute\Dispatcher $dispatcher) {
    return function(ServerRequestInterface $req) use ($dispatcher) {
        $method = $req->getMethod();
        $uri = $req->getUri()->getPath();

        $res = $dispatcher->dispatch($method, $uri);
        if ($res[0] == \FastRoute\Dispatcher::FOUND) {
            return DispatchResult::create200(new MatchedRoute($res[2], $res[1]));
        }
        if ($res[0] == \FastRoute\Dispatcher::NOT_FOUND) {
            return DispatchResult::create404();
        }
        if ($res[0] == \FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            return DispatchResult::create405($res[1]);
        }
    };
}

function fastRouteDispatcherFactory() {
    return function(RouteGroup $routes) {
        $dispatcher = \FastRoute\simpleDispatcher(function($route_collector) use ($routes) {
            foreach ($routes->getRoutes() as $r) {
                $route_collector->addRoute($r->getMethods(), $r->getUri(), $r);
            }
        });

        return fastRouteDispatcher($dispatcher);
    };
}
