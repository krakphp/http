<?php

namespace Krak\Mw\Http\Package\Std;

use Krak\Mw\Http;

class StdFreezer implements Http\Freezer
{
    public function freezeApp(Http\App $app) {
        $mws = $app['stacks.http'];
        $dispatcher_factory = $app['dispatcher_factory'];
        $dispatcher = $dispatcher_factory($app['routes']);

        $mws->push(
            Http\catchException($app['stacks.exception_handler']->compose()),
            0,
            'catch_exception'
        );
        $mws->unshift(
            Http\injectRoutingMiddleware(
                $dispatcher,
                $app['stacks.not_found_handler']->compose()
            ),
            0,
            'routing'
        );
        $mws->unshift(Http\injectRouteMiddleware(), 0, 'routes');
        $mws->unshift(
            Http\invokeRoutingAction(
                $app['stacks.invoke_action']->compose(),
                $app['stacks.marshal_response']->compose(),
                $app['response_factory']
            ),
            0,
            'invoke'
        );
    }
}
