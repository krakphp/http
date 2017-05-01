<?php

namespace Krak\Http\Concerns;

use Krak\Http;

trait Response
{
    public function response(...$args) {
        if (count($args) == 0) {
            return $this[Http\ResponseFactoryStore::class];
        }

        return $this[Http\ResponseFactory::class]->createResponse(...$args);
    }
}
