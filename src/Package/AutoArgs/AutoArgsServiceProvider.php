<?php

namespace Krak\Mw\Http\Package\AutoArgs;

use Krak\Mw,
    Pimple;

class AutoArgsServiceProvider implements Pimple\ServiceProviderInterface
{
    public function register(Pimple\Container $app) {
        $app->extend('freezer', function($freezer) {
            return new AutoArgsFreezer($freezer);
        });
    }
}
