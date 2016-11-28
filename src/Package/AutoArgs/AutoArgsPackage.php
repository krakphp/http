<?php

namespace Krak\Mw\Http\Package\AutoArgs;

use Krak\Mw\Http;

class AutoArgsPackage implements Http\Package
{
    public function with(Http\App $app) {
        $app->register(new AutoArgsServiceProvider());

        $app['stacks.resolve_argument']
            ->push(defaultValueResolveArgument(), -1)
            ->push(instanceResolveArgument(function() use ($app) { return $app; }))
            ->push(instanceResolveArgument(function() use ($app) { return $app->getContainer(); }))
            ->push(requestResolveArgument())
            ->push(routeParameterResolveArgument());
    }
}
