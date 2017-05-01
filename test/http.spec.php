<?php

use Krak\Mw,
    Krak\Mw\Http;

describe('Krak Http', function() {
    describe('ResponseFactory', function() {
        require_once __DIR__ . '/response-factory.php';
    });
    describe('Route', function() {
        require_once __DIR__ . '/route.php';
    });
    describe('Middleware', function() {
        require_once __DIR__ . '/middleware.php';
    });
});
