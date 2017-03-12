<?php

namespace Krak\Http\Exception;

class HttpException extends \RuntimeException
{
    private $status_code;
    private $headers;

    public function __construct($status_code, $message = null, array $headers = [], \Exception $previous = null, $code = 0) {
        $this->status_code = $status_code;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode() {
        return $this->status_code;
    }

    public function getHeaders() {
        return $this->headers;
    }
}
