<?php

namespace Krak\Mw\Http\Package\AutoArgs;

use Krak\Mw,
    Pimple;

class AutoArgsServiceProvider implements Pimple\ServiceProviderInterface
{
    public function register(Pimple\Container $app) {
        $app['stacks.resolve_argument'] = function() {
            return mw\stack('Resolve Argument');
        };
        $app->extend('freezer', function($freezer) {
            return new AutoArgsFreezer($freezer);
        });
    }
}
