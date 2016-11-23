<?php

namespace Krak\Mw\Http;

use Psr\Http\Message\ServerRequestInterface;

/** this takes a controller result and turns it into a response */
interface MarshalResponse {
    public function __invoke($result, $rf, $request, $next);
}

/** checks if int is within http status code ranges */
function _isStatusCode($code) {
    return $code >= 100 && $code < 600;
}

function isTuple($tuple, ...$types) {
    if (!is_array($tuple) || count($tuple) != count($types)) {
        return false;
    }


    foreach ($types as $i => $type) {
        if (
            !isset($tuple[$i]) ||
            !($type == "any" || gettype($tuple[$i]) == $type)
        ) {
            return false;
        }
    }

    return true;
}


/** determines if response matches an http tuple, if so, it will pass the body along
    to be marshalled and updates the response with the status and headers set.
    Results can be either a 2-tuple or 3-tuple of [status_code, body] or
    [status_code, headers, body]. Downstream marshalers will only receive the body
    as the result
*/
function httpTupleMarshalResponse() {
    return function($res, $rf, $req, $next) {

        $valid_http_tuple = isTuple($res, 'integer', 'any') || isTuple($res, "integer", "array", "any");

        if (!$valid_http_tuple) {
            return $next($res, $rf, $req);
        }

        if (count($res) == 2) {
            $headers = [];
            list($status, $body) = $res;
        } else {
            list($status, $headers, $body) = $res;
        }

        $resp = $next(
            $body,
            $rf,
            $req
        );

        $resp = $resp->withStatus($status);

        foreach ($headers as $name => $value) {
            $resp = $resp->withHeader($name, $value);
        }

        return $resp;
    };
}

function redirectMarshalResponse($valid_redirects = [300, 301, 302, 303, 304, 305, 307, 308]) {
    return function($result, $rf, $req, $next) use ($valid_redirects) {
        $is_redirect = isTuple($result, "integer", "string");

        if (!$is_redirect) {
            return $next($result, $rf, $req, $next);
        }

        list($status, $uri) = $result;
        return $rf($status, ['Location' => $uri]);
    };
}

function stringMarshalResponse($html = true) {
    return function($result, $rf, $req, $next) use ($html) {
        $headers = $html
            ? ['Content-Type' => 'text/html']
            : ['Content-Type' => 'text/plain'];

        return $rf(200, $headers, $result);
    };
}

function jsonMarshalResponse($opts = 0) {
    return function($result, $rf, $req, $next) use ($opts) {
        return $rf(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($result, $opts)
        );
    };
}
