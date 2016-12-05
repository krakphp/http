<?php

namespace Krak\Mw\Http\Package\REST;

use Krak\Mw\Http;

class RESTPackage implements Http\Package
{
    private $error;
    private $json_opts;

    public function __construct($error = null, $json_opts = 0) {
        $this->error = $error ?: _error();
        $this->json_opts = $json_opts;
    }

    public function with(Http\App $app) {
        $app['rest.error'] = $app->protect($this->error);
        $app['rest.response_factory'] = $app->protect(http\jsonResponseFactory(
            $app['response_factory'],
            $this->json_opts
        ));

        $app->push(parseJson($app['rest.response_factory'], $app['rest.error']));

        $app['stacks.exception_handler']->push(restExceptionHandler(
            $app['rest.response_factory'],
            $app['rest.error']
        ));
        $app['stacks.not_found_handler']->push(restNotFoundHandler(
            $app['rest.response_factory'],
            $app['rest.error']
        ));
        $app['stacks.marshal_response']
            ->push(jsonMarshalResponse($this->json_opts));
    }
}
