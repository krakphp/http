<?php

namespace Krak\Mw\Http;

use Psr\Http\Message\ServerRequestInterface;

/** this takes a controller result and turns it into a response */
interface MarshalResponse {
    public function __invoke($result, $rf, ServerRequestInterface $request);
}
interface MarshalResponseMiddleware {
    public function __invoke($result, $rf, ServerRequestInterface $request, \Closure $next);
}
