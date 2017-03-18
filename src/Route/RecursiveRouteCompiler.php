<?php

namespace Krak\Http\Route;

use Krak\Http;
use function Krak\Http\Util\joinUri,
    iter\flatten,
    iter\toArray,
    iter\map;

class RecursiveRouteCompiler implements Http\RouteCompiler
{
    private $join_path;

    public function __construct($join_path = null) {
        $this->join_path = $join_path ?: rmDupSlashesJoinPath();
    }
    public function compileRoutes(RouteGroup $group, $prefix) {
        $join_path = $this->join_path;
        $prefix = $join_path($prefix, $group->getPathPrefix());

        return flatten(map(function($r) use ($join_path, $prefix) {
            if ($r instanceof Route) {
                return $r->withPath($join_path($prefix, $r->path));
            }

            // else we are a group
            return $this->compileRoutes($r, $prefix);
        }, $group->getRoutes()));
    }
}
