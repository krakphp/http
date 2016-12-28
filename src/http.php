<?php

namespace Krak\Mw\Http;

use Krak\Mw,
    Psr\Http\Message\ServerRequestInterface,
    Psr\Http\Message\ResponseInterface;

function stdApp() {
    $app = new App();
    $app->with(Package\std());
    return $app;
}

function restApp() {
    $app = stdApp();
    $app->with(Package\rest());
    return $app;
}

function webApp() {
    $app = stdApp();
    $app->with(Package\plates());
    return $app;
}


function mount($path, $mw) {
    if ($mw instanceof App) {
        $mw = $mw->withRoutePrefix($path);
    }

    return mw\filter($mw, function($req) use ($path) {
        return strpos($req->getUri()->getPath(), $path) === 0;
    });
}

function injectRequestAttribute($name, $value) {
    return function($req, $next) use ($name, $value) {
        return $next($req->withAttribute($name, $value));
    };
}

function catchException($handler) {
    return function(ServerRequestInterface $req, $next) use ($handler) {
        try {
            return $next($req);
        } catch (\Exception $e) {
            return $handler($req, $e);
        }
    };
}
function injectRouteMiddleware($prefix = '_') {
    return function($req, $next) use ($prefix) {
        $attributes = $req->getAttribute('route.attributes');

        if (count($attributes->mws)) {
            $next = $next->chain($attributes->mws);
        }

        return $next($req);
    };
}

function injectRoutingMiddleware($router, $handle_error) {
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
function invokeRoutingAction($invoke_action, $marshal_response, $response_factory) {
    return function(ServerRequestInterface $req, $next) use ($invoke_action, $marshal_response, $response_factory) {
        $result = $invoke_action(
            $req,
            $req->getAttribute('route.handler'),
            $req->getAttribute('route.parameters')
        );

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        return $marshal_response($result, $response_factory, $req);
    };
}
