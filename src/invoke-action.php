<?php

namespace Krak\Mw\Http;

use Psr\Http\Message\ServerRequestInterface,
    Psr\Http\Message\ResponseInterface;

interface InvokeAction {
    public function __invoke(ServerRequestInterface $req, $action, $params, $next);
}

function callableInvokeAction($pass_params = true) {
    return function(ServerRequestInterface $req, $action, $params) use ($pass_params) {
        if (!is_callable($action)) {
            throw new \InvalidArgumentException('The action given was not a callable');
        }

        if ($pass_params) {
            return $action($req, $params);
        }

        return $action($req);
    };
}

/** this will inject parameters into the callable based on reflection */
function paramInjectCallableInvokeAction() {
    return function(ServerRequestInterface $req, $action, $params) {
        // TODO: implement
    };
}

/** try to get the action out of a pimple container and then delegate the calling to the next
    invoker. If you pass in null for $method_sep, then it won't try and split the method from
    the action string.

        class Controller {
            public function getIndexAction($req) {
                return 'response';
            }
        }
        $container['namespace.prefix.controller'] = function() {
            return new Controller();
        };
        $invoke = pimpleInvokeAction(
            callableInvokeAction(),
            $container,
            'namespace.prefix.',
        );
        assert($invoke($req, 'controller@getIndexAction', []) == 'response');
*/
function pimpleInvokeAction(\Pimple\Container $app, $prefix = '', $method_sep = '@') {
    return function($req, $action, $params, $next) use ($app, $prefix, $method_sep) {
        if (!is_string($action)) {
            return $next($req, $action, $params);
        }

        if (isset($app[$prefix . $action])) {
            return $next($req, $app[$prefix . $action], $params);
        }
        if ($method_sep && strpos($action, $method_sep) !== false) {
            list($controller, $method) = explode($method_sep, $action);
            return $next(
                $req,
                [$app[$prefix . $controller], $method],
                $params
            );
        }

        return $next($req, $action, $params);
    };
}
