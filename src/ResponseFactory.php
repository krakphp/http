<?php

namespace Krak\Http;

interface ResponseFactory {
    public function createResponse($status = 200, array $headers = [], $body = null);
}
