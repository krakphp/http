<?php

require_once __DIR__ . '/../vendor/autoload.php';

$serve = Krak\Mw\Http\server();
$serve(function($req) {
    return new GuzzleHttp\Psr7\Response(200, [], 'Hello World!');
});
