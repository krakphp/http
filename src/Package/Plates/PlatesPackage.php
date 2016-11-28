<?php

namespace Krak\Mw\Http\Package\Plates;

use Krak\Mw\Http;

class PlatesPackage implements Http\Package
{
    private $ext;
    private $config;

    public function __construct(array $config = []) {
        $this->config = $config + [
            '404' => 'errors/404',
            '500' => 'errors/500',
        ];
    }

    public function with(Http\App $app) {
        $app->register(
            new PlatesServiceProvider(),
            Http\Util\arrayFromPrefix($this->config, 'plates.')
        );

        $app['stacks.not_found_handler']->push(platesNotFoundHandler(
            $app,
            $this->config['404']
        ));
        $app['stacks.exception_handler']->push(platesExceptionHandler(
            $app,
            $this->config['500']
        ));
        $app['stacks.marshal_response']->push(platesMarshalResponse($app));
        $app->push(injectPlatesRequest($app));
    }
}
