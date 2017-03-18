<?php

namespace Krak\Http\Dispatcher\FastRoute;

use Krak\Http;
use Krak\Http\Dispatcher\DispatchResult;
use Krak\Http\Dispatcher\MatchedRoute;
use Psr\Http\Message\ServerRequestInterface;
use FastRoute;

class FastRouteDispatcher implements Http\Dispatcher
{
    private $dispatcher;

    public function __construct(FastRoute\Dispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    public function dispatch(ServerRequestInterface $req) {
        $method = $req->getMethod();
        $uri = $req->getUri()->getPath();

        $res = $this->dispatcher->dispatch($method, $uri);
        if ($res[0] == FastRoute\Dispatcher::FOUND) {
            return DispatchResult::create200(new MatchedRoute($res[2], $res[1]));
        }
        if ($res[0] == FastRoute\Dispatcher::NOT_FOUND) {
            return DispatchResult::create404();
        }
        if ($res[0] == FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            return DispatchResult::create405($res[1]);
        }
    }
}
