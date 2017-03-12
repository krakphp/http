<?php

namespace Krak\Http\Server;

use Krak\Http;
use Zend\Diactoros;

class DiactorosServer implements Http\Server
{
    private $emitter;
    private $req_factory;

    public function __construct(Diactoros\Response\EmitterInterface $emitter = null, callable $req_factory = null) {
        $this->emitter = $emitter ?: new Diactoros\Response\SapiEmitter();
        $this->req_factory = $req_factory ?: function() {
            return Diactoros\ServerRequestFactory::fromGlobals();
        };
    }

    public function serve($handler) {
        $req = call_user_func($this->req_factory);
        $this->emitter->emit($handler($req));
    }
}
