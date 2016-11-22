<?php

$serve = Krak\Mw\Http\server();

$rf = Krak\Mw\Http\diactorosResponseFactory();
$rf = Krak\Mw\Http\jsonResponseFactory($rf);

$serve(function($req) use ($rf) {
    return $rf(200, [], [
        'id' => 1,
    ]);
});
