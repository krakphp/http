<?php

namespace Krak\Http\Route;

trait RouteMatch {
    public function get($path, $handler) {
        return $this->match('GET', $path, $handler);
    }
    public function post($path, $handler) {
        return $this->match('POST', $path, $handler);
    }
    public function put($path, $handler) {
        return $this->match('PUT', $path, $handler);
    }
    public function delete($path, $handler) {
        return $this->match('DELETE', $path, $handler);
    }
}
