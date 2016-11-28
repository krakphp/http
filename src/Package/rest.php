<?php

namespace Krak\Mw\Http\Package;

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
        $app->mws()->push(parseJson($app->responseFactory(), $this->error));
        $rf = http\jsonResponseFactory(
            $app->responseFactory(),
            $this->json_opts
        );

        $app->exceptionHandler()->push(restExceptionHandler(
            $rf,
            $this->error
        ));
        $app->notFoundHandler()->push(restNotFoundHandler(
            $rf,
            $this->error
        ));
        $app->marshalResponse()
            ->push(Http\jsonMarshalResponse($this->json_opts));
    }
}

function rest(...$args) {
    return new RESTPackage(...$args);
}

function _error() {
    return function($code, $msg, $extra = []) {
        return [
            'code' => $code,
            'message' => $msg,
        ] + $extra;
    };
}

function parseJson($rf, $error) {
    return function($req, $next) use ($rf, $error) {
        if ($req->getMethod() == 'GET' || $req->getMethod() == 'DELETE') {
            return $next($req);
        }

        $ctype = $req->getHeader('Content-Type');
        if (!$ctype || $ctype[0] != 'application/json') {
            return $rf(415, [], $error('unsupported_media_type', 'Expected application/json'));
        }

        return $next($req->withParsedBody(json_decode($req->getBody(), true)));
    };
}

function restExceptionHandler($rf, $error) {
    return function($req, $exception) use ($rf, $error) {
        return $rf(500, [], $error('unhandled_exception', $exception->getMessage()));
    };
}

function restNotFoundHandler($rf, $error) {
    return function($req, $result, $next) use ($rf, $error) {
        $headers = [];
        if ($result->status_code == 405) {
            $headers['Allow'] = implode(", ", $result->allowed_methods);
        }
        return $rf(
            $result->status_code,
            $headers,
            $error(
                $result->status_code == 405 ? 'method_not_allowed' : 'not_found',
                $result->status_code == 405 ? 'Method Not Allowed' : 'Not Found'
            )
        );
    };
}
