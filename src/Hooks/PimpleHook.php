<?php

namespace Krak\Mw\Http\Hooks;

use Krak\Mw\Http,
    Pimple\Container;


class PimpleHook extends Http\AbstractHook
{
    private $container;
    private $config;

    public function __construct(Container $container, $config = []) {
        $this->container = $container;
        $this->config = [
            'prefix' => '',
            'method_sep' => '@',
            'name' => 'container',
        ] + $config;
    }

    public function with(Http\App $app) {
        $app->invokeAction()->push(
            pimpleInvokeAction($this->container, $this->config['prefix'], $this->config['method_sep'])
        );
        $app->mws->push(injectPimple($this->container, $this->config['name']));
    }
}

/** injects pimple into the request */
function injectPimple(Container $container, $name = 'container') {
    return function($req, $next) use ($container, $name) {
        return $next($req->withAttribute($name, $container));
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
    return function(ServerRequestInterface $req, $action, $params) use ($invoke, $app, $prefix, $method_sep) {
        if (!is_string($action)) {
            return $invoke($req, $action, $params);
        }

        if (isset($app[$prefix . $action])) {
            return $invoke($req, $app[$prefix . $action], $params);
        }
        if ($method_sep && strpos($action, $method_sep) !== false) {
            list($controller, $method) = explode($method_sep, $action);
            return $invoke(
                $req,
                [$app[$prefix . $controller], $method],
                $params
            );
        }

        return $invoke($req, $action, $params);
    };
}
