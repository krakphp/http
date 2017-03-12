<?php

namespace Krak\Http;

interface DispatcherFactory {
    /** @return Dispatcher */
    public function createDispatcher($routes);
}
