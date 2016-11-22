<?php

namespace Krak\Mw\Http;

use Psr\Http\Message\ServerRequestInterface;

/** this takes a controller result and turns it into a response */
interface MarshalResponse {
    public function __invoke($tup, $next);
}

/** checks if int is within http status code ranges */
function _isStatusCode($code) {
    return $code >= 100 && $code < 600;
}

/** determines if response matches an http tuple, if so, it will pass the body along
    to be marshalled and updates the response with the status and headers set.
    Results can be either a 2-tuple or 3-tuple of [status_code, body] or
    [status_code, headers, body]. Downstream marshalers will only receive the body
    as the result
*/
function httpTupleMarshalResponse() {
    return function($tup, $next) {
        list($res, $req, $rf) = $tup;

        $valid_http_tuple = is_array($res) &&
            is_int($res[0]) &&
            _isStatusCode($res[0]) &&
            (
                count($res) == 2 ||
                (count($res == 3) && is_array($res[1]))
            );

        if (!$valid_http_tuple) {
            return $next($tup);
        }

        if (count($res) == 2) {
            $headers = [];
            list($status, $body) = $res;
        } else {
            list($status, $headers, $body) = $res;
        }

        $resp = $next([
            $body,
            $req,
            $rf
        ]);

        $resp = $resp->withStatus($status);

        foreach ($headers as $name => $value) {
            $resp = $resp->withHeader($name, $value);
        }

        return $resp;
    };
}

function stringMarshalResponse($html = true) {
    return function($tup, $next) use ($html) {
        list($res, $req, $rf) = $tup;

        $headers = $html
            ? ['Content-Type' => 'text/html']
            : ['Content-Type' => 'text/plain'];

        return $rf(200, $headers, $res);
    };
}

function jsonMarshalResponse($opts = 0) {
    return function($tup, $next) use ($opts) {
        list($res, $req, $rf) = $tup;
        return $rf(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($res, $opts)
        );
    };
}
