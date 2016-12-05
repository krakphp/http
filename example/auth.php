<?php

// php -S localhost:8000 example/auth.php
use Krak\Mw\Http;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Http\App();
$app->with(Http\Package\std());

$app->get('/', function() {
    return 'Home';
});

$api = new Http\App();
$api->with(Http\Package\std());
$api->with(Http\Package\rest());
$api->get('/users', function() {
    return [
        ['id' => 1],
        ['id' => 2]
    ];
});
$api->mount('/users', function($req, $next) use ($api) {
    // basic auth with user `foo` and password `bar`
    $rf = http\jsonResponseFactory($api['response_factory']);

    if (!$req->hasHeader('Authorization')) {
        return $rf(401, ['WWW-Authenticate' => 'Basic realm="/"'], ['code' => 'unauthorized']);
    }
    if ($req->getHeader('Authorization')[0] != 'Basic Zm9vOmJhcg==') {
        return $rf(403, [], ['code' => 'forbidden']);
    }

    return $next($req);
});

$app->mount('/api', $api);

$app->serve();
