<?php

use Krak\Mw,
    Krak\Mw\Http;

describe('Mw Http', function() {
    describe('#mount', function() {
        it('mounts an mw on url prefix', function() {
            $mw = http\mount('/api', function($req) {
                return $req->getUri()->getPath();
            });
            $handler = mw\compose([$mw]);
            assert('/api/user' == $handler(new GuzzleHttp\Psr7\ServerRequest('GET', '/api/user')));
        });
    });
    describe('Resolve Argument', function() {
        require_once __DIR__ . '/resolve-argument.php';
    });
    describe('App', function() {
        require_once __DIR__ . '/app.php';
    });
    describe('Package', function() {
        describe('REST', function() {
            require_once __DIR__ . '/package/rest.php';
        });
    });
});
