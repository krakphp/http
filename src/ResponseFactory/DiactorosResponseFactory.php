<?php

namespace Krak\Http\ResponseFactory;

use Krak\Http;
use Zend\Diactoros;

class DiactorosResponseFactory implements Http\ResponseFactory
{
    public function createResponse($status = 200, array $headers = [], $body = null) {
        // slightly copied from the GuzzleHttp source
        if (is_string($body)) {
            $stream = new Diactoros\Stream('php://temp', 'r+');
            if ($body !== '') {
                $stream->write($body);
                $stream->rewind();
            }
            $body = $stream;
        }
        $body = $body === null ? 'php://memory' : $body;
        return new Diactoros\Response($body, $status, $headers);
    }
}
