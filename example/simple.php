<?php

$serve = Krak\Mw\Http\server();
$serve(function($req) {
    return new GuzzleHttp\Response(200, [], 'Hello World!');
});
