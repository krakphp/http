<?php

namespace Krak\Mw\Http\Mw;

use Krak\Mw,
    Krak\Mw\Http;

class HttpAppLink extends Mw\Link
{
    public function getApp() {
        return $this->getContext()->getApp();
    }

    public function response($status, array $headers = [], $body = null) {
        return $this->getApp()->response($status, $headers, $body);
    }
}
