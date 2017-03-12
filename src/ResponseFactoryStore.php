<?php

namespace Krak\Http;

class ResponseFactoryStore
{
    private $factories;

    public function __construct() {
        $this->factories = [];
    }

    public function store($name, ResponseFactory $rf) {
        $this->factories[$name] = $rf;
    }

    public function get($name) {
        if (isset($this->factories[$name])) {
            return $this->factories[$name];
        }
    }

    public function __call($method, $arguments) {
        $factory = $this->get($method);
        if (!$factory) {
            throw new \RuntimeException("Response Factory '$method' is not registered");
        }

        return $factory->createResponse(...$arguments);
    }
}
