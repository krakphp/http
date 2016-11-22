<?php

use Krak\Mw;

$serve = Mw\Http\server();

$rf = Mw\Http\jsonResponseFactory();

// mw\compose will compose the middlewares into a handler
$serve(mw\compose([
    function($req, $next) use ($rf) {
        return $rf(200, [], [
            'message' => 'Congrats!'
        ]);
    },
    // perform authentication
    function($req, $next) use ($rf) {
        if (!$req->getHeader('X-Token')) {
            return $rf(400, [], [
                'error' => 'no_auth'
            ]);
        }

        return $next($req);
    }
]));
