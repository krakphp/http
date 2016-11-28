<?php

namespace Krak\Mw\Http\Package;

use Krak\Mw\Http,
    League\Plates;

class PlatesPackage implements Http\Package
{
    private $plates;
    private $config;

    public function __construct(Plates\Engine $plates, $config = []) {
        $this->plates = $plates;
        $this->config = $config + [
            'name' => 'plates',
            'error_paths' => [
                '404' => 'errors/404',
                '500' => 'errors/500',
            ]
        ];
    }

    public function with(Http\App $app) {
        $rf = $app->responseFactory();
        $app->notFoundHandler()->push(platesNotFoundHandler(
            $app->responseFactory(),
            $this->plates,
            $this->config['error_paths']['404']
        ));
        $app->exceptionHandler()->push(platesExceptionHandler(
            $app->responseFactory(),
            $this->plates,
            $this->config['error_paths']['500']
        ));
        $app->marshalResponse()->push(platesMarshalResponse($this->plates));
        $app->mws()->push(injectPlates($this->plates, $this->config['name']));
    }
}

function plates(...$args) {
    return new PlatesPackage(...$args);
}

/** injects pimple into the request */
function injectPlates(Plates\Engine $plates, $name = 'plates') {
    return function($req, $next) use ($plates, $name) {
        $plates->addData(['request' => $req]);
        return $next($req->withAttribute($name, $plates));
    };
}

/** Allows the returning of a 2-tuple of path and data */
function platesMarshalResponse(Plates\Engine $plates) {
    return function($result, $rf, $req, $next) use ($plates) {
        $matches = Http\isTuple($result, "string", "array");
        if (!$matches) {
            return $next($result, $rf, $req, $next);
        }

        list($template, $data) = $result;
        return $rf(200, [], $plates->render($template, $data));
    };
}

function platesNotFoundHandler($rf, Plates\Engine $plates, $path) {
    return function($req, $result, $next) use ($rf, $plates, $path) {
        if (!$plates->exists($path)) {
            return $next($req, $result);
        }

        return $rf(404, [], $plates->render($path, [
            'dispatch_result' => $result,
        ]));
    };
}
function platesExceptionHandler($rf, Plates\Engine $plates, $path) {
    return function($req, $ex, $next) use ($rf, $plates, $path) {
        if (!$plates->exists($path)) {
            return $next($req, $ex);
        }

        return $rf(500, [], $plates->render($path, [
            'exception' => $ex,
        ]));
    };
}
