<?php

namespace Krak\Http\ResponseFactory;

use Krak\Http,
    Psr\Http\Message\ResponseInterface;

class DefaultResponseFactory implements Http\ResponseFactory
{
    private $resp;

    public function __construct(ResponseInterface $resp) {
        $this->resp = $resp;
    }

    public function createResponse($status = 200, array $headers = [], $body = null) {
        return $resp;
    }
}
