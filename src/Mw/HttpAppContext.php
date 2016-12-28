<?php

namespace Krak\Mw\Http\Mw;

use Krak\Mw,
    Krak\Mw\Http;

class HttpAppContext implements Mw\Context
{
    private $app;
    private $invoke;

    public function __construct(Http\App $app) {
        $this->app = $app;
        $this->invoke = Mw\pimpleAwareInvoke($app->getContainer());
    }

    public function getApp() {
        return $this->app;
    }

    public function getInvoke() {
        return $this->invoke;
    }
}
