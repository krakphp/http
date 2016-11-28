<?php

namespace Krak\Mw\Http\Package {
    function rest($error = null, $json_opts = 0) {
        return new REST\RESTPackage($error, $json_opts);
    }
}

namespace Krak\Mw\Http\Package\REST {
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
}
