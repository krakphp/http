<?php

namespace Krak\Mw\Http\Package\Std;

use Evenement\EventEmitter,
    Krak\Mw,
    Krak\Mw\Http,
    Pimple;

class StdServiceProvider implements Pimple\ServiceProviderInterface
{
    public function register(Pimple\Container $app) {
        $app['routes'] = new Http\RouteGroup();
        $app['response_factory'] = $app->protect(Http\responseFactory());

        $app['dispatcher_factory'] = function() {
            return Http\dispatcherFactory();
        };
        $app['event_emitter'] = function() {
            return new EventEmitter();
        };
        $app['freezer'] = function() {
            return new StdFreezer();
        };
        $app['server'] = function() {
            return Http\server();
        };
    }
}
