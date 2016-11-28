<?php

use Krak\Mw\Http;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Http\App();
$app->with(Http\Package\std());

$app->get('/', function() {
    return '/';
});

$app->on(Http\Events::FROZEN, function() {
    error_log("App Froze");
});
$app->on(Http\Events::INIT, function($app) {
    error_log("App Init");

    $app->emit('test', [1]);
});
$app->on(Http\Events::FINISH, function() {
    error_log("App Finish");
});

$app->on('test', function() {
    error_log("Test received..");
});

$app->serve();
