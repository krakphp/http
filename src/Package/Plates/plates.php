<?php

namespace Krak\Mw\Http\Package {
    function plates(array $config = []) {
        return new Plates\PlatesPackage($config);
    }
}

namespace Krak\Mw\Http\Package\Plates {
    use function Krak\Mw\Http\Util\isTuple;

    /** injects pimple into the request */
    function injectPlatesRequest($app) {
        return function($req, $next) use ($app) {
            $app['plates']->addData([
                'request' => $req,
                'app' => $app,
            ]);
            return $next($req);
        };
    }

    /** Allows the returning of a 2-tuple of path and data */
    function platesMarshalResponse($app) {
        return function($result, $rf, $req, $next) use ($app) {
            $matches = isTuple($result, "string", "array");
            if (!$matches) {
                return $next($result, $rf, $req, $next);
            }

            list($template, $data) = $result;
            return $rf(200, [], $app['plates']->render($template, $data));
        };
    }

    function platesNotFoundHandler($app, $path) {
        return function($req, $result, $next) use ($app, $path) {
            if (!$app['plates']->exists($path)) {
                return $next($req, $result);
            }

            $rf = $app['response_factory'];
            return $rf(404, [], $app['plates']->render($path, [
                'dispatch_result' => $result,
            ]));
        };
    }
    function platesExceptionHandler($app, $path) {
        return function($req, $ex, $next) use ($app, $path) {
            if (!$app['plates']->exists($path)) {
                return $next($req, $ex);
            }

            $rf = $app['response_factory'];
            return $rf(500, [], $app['plates']->render($path, [
                'exception' => $ex,
            ]));
        };
    }
}
