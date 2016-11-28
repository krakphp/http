<?php

namespace Krak\Mw\Http\Package\Plates;

use Pimple,
    League\Plates;

class PlatesServiceProvider implements Pimple\ServiceProviderInterface
{
    public function register(Pimple\Container $app) {
        $app['plates.views_path'] = null;
        $app['plates.ext'] = 'php';
        $app['plates'] = function($app) {
            return new Plates\Engine($app['plates.views_path'], $app['plates.ext']);
        };
    }
}
