<?php

namespace Krak\Mw\Http\Package\Std;

use Krak\Mw\Http;
use Krak\Mw;

class StdFreezer implements Http\Freezer
{
    public function freezeApp(Http\App $app) {
        $mws = $app['stacks.http'];
        $dispatcher_factory = $app['dispatcher_factory'];
        $dispatcher = $dispatcher_factory($app['routes']);

        $mws->push(
            Http\catchException(Mw\compose([$app['stacks.exception_handler']])),
            0,
            'catch_exception'
        );
        $mws->unshift(
            Http\injectRoutingMiddleware(
                $dispatcher,
                Mw\compose([$app['stacks.not_found_handler']])
            ),
            0,
            'routing'
        );
        $mws->unshift(Http\injectRouteMiddleware(), 0, 'routes');
        $mws->unshift(
            Http\invokeRoutingAction(
                Mw\compose([$app['stacks.invoke_action']]),
                Mw\compose([$app['stacks.marshal_response']]),
                $app['response_factory']
            ),
            0,
            'invoke'
        );
    }
}
