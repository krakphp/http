<?php

namespace Krak\Mw\Http;

use Psr\Http\Message\ServerRequestInterface;

interface Dispatcher {
    /** @return DispatchResult */
    public function __invoke(ServerRequestInterface $req);
}

interface DispatcherFactory {
    public function __invoke(RouteGroup $routes);
}

class DispatchResult
{
    public $status_code;
    public $matched_route;
    /** used for 405 responses */
    public $allowed_methods;

    public function __construct($status_code) {
        $this->status_code = $status_code;
    }

    public static function create200(MatchedRoute $matched_route) {
        $res = new self(200);
        $res->matched_route = $matched_route;
        return $res;
    }

    public static function create404() {
        return new self(404);
    }

    public static function create405($allowed_methods = []) {
        $res = new self(405);
        $res->allowed_methods = $allowed_methods;
        return $res;
    }
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
                $route_collector->addRoute($r->getMethods(), $r->getPath(), $r);
            }
        });

        return fastRouteDispatcher($dispatcher);
    };
}

function dispatcherFactory() {
    return fastRouteDispatcherFactory();
}
