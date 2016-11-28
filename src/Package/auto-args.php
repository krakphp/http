<?php

namespace Krak\Mw\Http\Package;

use Krak\Mw\Http,
    Krak\Mw,
    Pimple;

class AutoArgsPackage implements Http\Package
{
    public function with(Http\App $app) {
        $app->register(new AutoArgsServiceProvider());

        $app['stacks.resolve_argument']
            ->push(Http\defaultValueResolveArgument(), -1)
            ->push(Http\instanceResolveArgument(function() use ($app) { return $app; }))
            ->push(Http\instanceResolveArgument(function() use ($app) { return $app->getContainer(); }))
            ->push(Http\requestResolveArgument())
            ->push(Http\routeParameterResolveArgument());
    }
}

class AutoArgsServiceProvider implements Pimple\ServiceProviderInterface
{
    public function register(Pimple\Container $app) {
        $app['stacks.resolve_argument'] = function() {
            return mw\stack('Resolve Argument');
        };
        $app->extend('freezer', function($freezer) {
            return autoArgsFreezer($freezer);
        });
    }
}

function autoArgs() {
    return new AutoArgsPackage();
}

function autoArgsFreezer($freezer) {
    return function($app) use ($freezer) {
        $resolve_arg = $app['stacks.resolve_argument']->compose();
        $app['stacks.invoke_action']->push(
            Http\resolveArgumentsCallableInvokeInvokeAction($resolve_arg),
            0,
            'invoke'
        );
        return $freezer($app);
    };
}
