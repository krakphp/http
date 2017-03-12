<?php

namespace Krak\Http;

interface RouteCompiler
{
    public function compileRoutes(Route\RouteGroup $routes, $prefix);
}
