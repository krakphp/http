<?php

use Krak\Mw;

$api = new Mw\Http\RESTApp();
$api->get('/users', function() {
    return [
        ['id' => 1]
    ];
});
$api->get('/posts', function() {
    return [
        ['id' => 2]
    ];
});
$api->exceptionHandler();
$api->routingErrorHandler();

$api->mws->push(someAuthMw());

$web = new Mw\Http\WebApp();
$web->get('/products/{code}', function($req) {
    $code = $req->getAttribute('params')['code'];

    return ['products/index', [
        'code' => $code
    ]];
});
$web->get('/', function() {
    return ['home'];
});

$serve = Krak\Mw\Http\server([
    'emitter' => null,
    'request_factory' => null,
]);


$serve(mw\compose([
    mw\http\mount('/api', $api),
    $web
]));
