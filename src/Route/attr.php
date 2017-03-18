<?php

namespace Krak\Http\Route;

use function iter\reduce;

/** traverse the tree up */
function traverseRouteAttributesTree(RouteAttributes $attrs) {
    do {
        yield $attrs;
    } while ($attrs = $attrs->getParent());
}

/** get all attributes from an tree */
function attributesTreeAllAttributes(RouteAttributes $attrs, $key) {
    return reduce(function($acc, $attr) use ($key) {
        $attributes = $attr->getAttributes();
        if (isset($attributes[$key])) {
            $acc[] = $attributes[$key];
        }
        return $acc;
    }, traverseRouteAttributesTree($attrs), []);
}

function attributesTreeFirstAttribute(RouteAttributes $attrs, $key) {
    $tree = traverseRouteAttributesTree($attrs);
    foreach ($tree as $route) {
        $set = $route->getAttributes();
        if (isset($set[$key])) {
            return $set[$key];
        }
    }
}
