<?php

namespace Krak\Mw\Http;

interface Package {
    public function with(App $app);
    public function withPostFreeze(App $app);
}

abstract class AbstractPackage implements Package {
    public function with(App $app) {}
    public function withPostFreeze(App $app) {}
}
