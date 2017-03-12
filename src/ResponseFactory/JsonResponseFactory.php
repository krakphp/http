<?php

namespace Krak\Http\ResponseFactory;

use Krak\Http;

class JsonResponseFactory implements Http\ResponseFactory
{
    private $rf;
    private $options;

    public function __construct(Http\ResponseFactory $rf, $options = 0) {
        $this->rf = $rf;
        $this->options = $options;
    }

    public function createResponse($status = 200, array $headers = [], $body = null) {
        return $this->rf->createResponse($status, $headers, json_encode($body, $this->options))
            ->withHeader('Content-Type', 'application/json');
    }
}
