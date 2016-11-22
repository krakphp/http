<?php

use Krak\Mw;

$app = new Mw\Http\App();
$app->get('/a', function($req) {
    return 'a';
});
$app->get('/b', function($req) {
    return 'b';
});

// you can optionally pass in a $server
$app->serve();
