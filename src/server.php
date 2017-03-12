<?php

namespace Krak\Http;

/** An Server is responsible for taking a handler and generating the request,
    running the kernel, and then emitting the response */
interface Server {
    /** @param $handler resolves the request into a response object */
    public function serve($handler);
}
