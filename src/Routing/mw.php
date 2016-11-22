<?php

namespace Krak\Mw\Http\Routing;

use Psr\Http\Message\ServerRequestInterface;

function injectRouteMiddlewareMw($prefix = '_') {
    return function($req, $next) use ($prefix) {
        $attributes = $req->getAttribute('route.attributes');

        if (!count($attributes->mws)) {
            return $next($req);
        }

        $next = $attributes->mws->composeSet($next);

        return $next($req);
    };
}

function routingInjectMw($router, $handle_error) {
    return function(ServerRequestInterface $req, $next) use ($router, $handle_error) {
        $res = $router($req);

        if ($res->status_code != 200) {
            return $handle_error($req, $res);
        }

        $matched = $res->matched_route;

        $attributes = $matched->route->getAttributes()->consolidate();
        return $next(
            $req->withAttribute('route.attributes', $attributes)
                ->withAttribute('route.parameters', $matched->params)
                ->withAttribute('route.handler', $matched->route->getHandler())
                ->withAttribute('route.raw', $matched->route)
        );
    };
}

/** simply invoke the action from the attributes set earlier */
function invokeActionMw($invoke_action) {
    return function(ServerRequestInterface $req, $next) use ($invoke_action, $prefix) {
        return $invoke_action(
            $req,
            $req->getAttribute('route.handler'),
            $req->getAttribute('route.params')
        );
    };
}
