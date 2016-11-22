<?php

use Krak\Mw;

$serve = Mw\Http\server();

$rf = Mw\Http\textResponseFactory();

// mw\compose will compose the middlewares into a handler
$serve(mw\compose([
    function() use ($rf) {
        // no matches... return 404
        return $rf(404, [], 'page not found');
    },
    Mw\Http\on('GET', '/a', function() use ($rf) {
        return $rf(200, [], 'a');
    }),
    Mw\Http\on('GET', '/b', function() use ($rf) {
        return $rf(200, [], 'b');
    })
]));
