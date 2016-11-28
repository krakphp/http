<?php

namespace Krak\Mw\Http;

use Psr\Http\Message,
    ReflectionParameter;

interface ResolveArgument {
    /** @return array an array of results to feed in. The array should be of size one in most cases */
    public function __invoke(ReflectionParameter $arg_meta, Message\ServerRequestInterface $req, $params);
}
