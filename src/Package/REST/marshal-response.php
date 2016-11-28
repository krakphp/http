<?php

namespace Krak\Mw\Http\Package\REST;

function jsonMarshalResponse($opts = 0) {
    return function($result, $rf, $req, $next) use ($opts) {
        return $rf(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($result, $opts)
        );
    };
}
