<?php

namespace Krak\Http;

use Psr\Http\Message\ServerRequestInterface;
use SplStack;
use function iter\reduce;

/** returns the full path from a request that might have been mounted or not */
class RequestPath
{
    private $req;

    public function __construct(ServerRequestInterface $req) {
        $this->req = $req;
    }

    public function path($path = null) {
        $path = $path ?: $this->req->getUri()->getPath();
        $path_stack = $this->req->getAttribute('original_uri_path');
        if (!$path_stack) {
            return $path;
        }

        $path_stack = clone $path_stack;
        $path_stack->setIteratorMode(SplStack::IT_MODE_FIFO | SplStack::IT_MODE_KEEP);

        $base_path = reduce(function($acc, $tup) {
            return $acc . $tup[1];
        }, $path_stack, '');

        return $base_path . $path;
    }
}
