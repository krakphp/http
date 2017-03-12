<?php

namespace Krak\Http;

use Psr\Http\Message\ServerRequestInterface;

interface Dispatcher {
    /** @return Dispatcher\DispatchResult */
    public function dispatch(ServerRequestInterface $req);
}
