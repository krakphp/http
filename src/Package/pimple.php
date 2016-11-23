<?php

namespace Krak\Mw\Http\Package;

use Krak\Mw\Http,
    Pimple\Container;

class PimplePackage extends Http\AbstractPackage
{
    private $container;
    private $config;

    public function __construct(Container $container, $config = []) {
        $this->container = $container;
        $this->config = $config + [
            'prefix' => '',
            'method_sep' => '@',
            'name' => 'pimple',
        ];
    }

    public function with(Http\App $app) {
        $app->invokeAction()->push(
            pimpleInvokeAction($this->container, $this->config['prefix'], $this->config['method_sep'])
        );
        $app->mws()->push(injectPimple($this->container, $this->config['name']));
    }
}

function pimple(...$args) {
    return new PimplePackage(...$args);
}

/** injects pimple into the request */
function injectPimple(Container $container, $name = 'pimple') {
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
