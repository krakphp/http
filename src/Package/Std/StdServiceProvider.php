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
        $app['dispatcher_factory'] = function() {
            return Http\dispatcherFactory();
        };
        $app['response_factory'] = function() {
            return Http\responseFactory();
        };
        $app['event_emitter'] = function() {
            return new EventEmitter();
        };
        $app['freezer'] = function() {
            return new StdFreezer();
        };
        $app['stacks.exception_handler'] = function() {
            return Mw\stack('Exception Handler');
        };
        $app['stacks.invoke_action'] = function() {
            return Mw\stack('Invoke Action');
        };
        $app['stacks.not_found_handler'] = function() {
            return Mw\stack('Not Found Handler');
        };
        $app['stacks.marshal_response'] = function() {
            return Mw\stack('Marshal Response');
        };
        $app['stacks.http'] = function() {
            return Mw\stack('Http');
        };
    }
}
