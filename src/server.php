<?php

namespace Krak\Mw\Http;

use Zend\Diactoros;

/** An Server is responsible for taking a handler and generating the request,
    running the kernel, and then emitting the response */
interface Server {
    /** @param callable */
    public function __invoke($handler);
}

/** Run the app using Diactoros PSR7 system */
function diactorosServer(
    Diactoros\Response\EmitterInterface $emitter = null,
    callable $req_factory = null
) {
    $emitter = $emitter ?: new Diactoros\Response\SapiEmitter();
    $req_factory = $req_factory ?: function() {
        return Diactoros\ServerRequestFactory::fromGlobals();
    };

    return function($handler) use ($emitter, $req_factory) {
        $resp = $handler($req_factory());
        $emitter->emit($resp);
    };
}

function server() {
    return diactorosServer();
}
