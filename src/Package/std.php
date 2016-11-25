<?php

namespace Krak\Mw\Http\Package;

use Krak\Mw\Http;

class StdPackage implements Http\Package
{
    public function with(Http\App $app) {
        $app->exceptionHandler()
            ->push(stdExceptionHandler($app->responseFactory()));
        $app->notFoundHandler()
            ->push(stdNotFoundHandler($app->responseFactory()));
        $app->marshalResponse()
            ->push(Http\stringMarshalResponse())
            ->unshift(Http\redirectMarshalResponse(), 1)
            ->unshift(Http\httpTupleMarshalResponse(), 1);
        $app->invokeAction()
            ->push(Http\callableInvokeAction());
    }
}

function std(...$args) {
    return new StdPackage(...$args);
}

function stdExceptionHandler($rf) {
    return function($req, $exception) use ($rf) {
        return $rf(500, ['Content-Type' => 'text/plain'], (string) $exception);
    };
}

function stdNotFoundHandler($rf) {
    return function($req, $result) use ($rf) {
        $headers = ['Content-Type' => 'text/plain'];
        if ($result->status_code == 405) {
            $headers['Allow'] = implode(", ", $result->allowed_methods);
        }

        return $rf(
            $result->status_code,
            $headers,
            $result->status_code == 405 ? '405 Method Not Allowed' : '404 Not Found'
        );
    };
}
