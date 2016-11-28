<?php

namespace Krak\Mw\Http;

use Psr\Http\Message\ServerRequestInterface,
    Psr\Http\Message\ResponseInterface;

interface InvokeAction {
    public function __invoke(ServerRequestInterface $req, $action, $params);
}

interface InvokeActionMiddleware {
    public function __invoke(ServerRequestInterface $req, $action, $params, \Closure $next);
}
