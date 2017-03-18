<?php

namespace Krak\Http\ResponseFactory;

use Krak\Http;
use GuzzleHttp\Psr7;

class GuzzleResponseFactory implements Http\ResponseFactory
{
    public function createResponse($status = 200, array $headers = [], $body = null) {
        return new Psr7\Response($status, $headers, $body);
    }
}
