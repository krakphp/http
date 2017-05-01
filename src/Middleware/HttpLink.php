<?php

namespace Krak\Http\Middleware;

use Krak\Mw;
use Krak\Http;

class HttpLink extends Mw\Link\ContainerLink
{
    use Http\Concerns\Response;
}
