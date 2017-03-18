<?php

namespace Krak\Http\ResponseFactory;

use Krak\Http;

class TextResponseFactory implements Http\ResponseFactory
{
    private $rf;

    public function __construct(Http\ResponseFactory $rf) {
        $this->rf = $rf;
    }

    public function createResponse($status = 200, array $headers = [], $body = null) {
        return $this->rf->createResponse($status, $headers, $body)
            ->withHeader('Content-Type', 'text/plain');
    }
}
