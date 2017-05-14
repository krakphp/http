<?php

namespace Krak\Mw\Http;

use Krak\Mw,
    iter;

class RouteAttributes
{
    public $mws;
    public $parent;

    public function __construct(Mw\Stack $mws = null) {
        $this->mws = $mws ?: mw\stack();
    }

    public static function createChild(RouteAttributes $parent) {
        $attrs = new self();
        $attrs->parent = $parent;
        return $attrs;
    }

    /** merges to attributes together into a new route attributes */
    public function merge(RouteAttributes $attributes) {
        return new self(
            Mw\stack([$attributes->mws, $this->mws])
        );
    }

    /** consolidates/merges all of the parent attributes into this one */
    public function consolidate() {
        $attributes = $this->attributeList();
        return iter\reduce(function($acc, $attr) {
            if (!$acc) {
                return $attr;
            }
            return $acc->merge($attr);
        }, $attributes);
    }

    /** Creates a sequence of attributes from parent -> child */
    private function attributeList() {
        $attr = $this;

        $stack = new \SplStack();
        $stack->push($attr);
        while ($attr->parent) {
            $stack->push($attr->parent);
            $attr = $attr->parent;
        }

        while ($stack->count()) {
            yield $stack->pop();
        }
    }
}

trait AttributesAccessor {
    private $attributes;

    public function getAttributes() {
        return $this->attributes;
    }

    public function withAttributes(RouteAttributes $attributes) {
        $val = clone $this;
        $val->attributes = $attributes;
        return $val;
    }

    /* middleware helpers */
    public function push(...$params) {
        $this->attributes->mws->push(...$params);
        return $this;
    }
    public function unshift(...$params) {
        $this->attributes->mws->unshift(...$params);
        return $this;
    }
    public function pop(...$params) {
        $this->attributes->mws->pop(...$params);
        return $this;
    }
    public function shift(...$params) {
        $this->attributes->mws->shift(...$params);
        return $this;
    }
}

class RouteGroup
{
    use RouteMatch;
    use AttributesAccessor;

    private $prefix;
    private $routes;

    public function __construct($prefix = '/', RouteAttributes $attributes = null) {
        $this->prefix = $prefix;
        $this->routes = [];
        $this->attributes = $attributes ?: new RouteAttributes();
    }

    public static function createWithGroup($prefix, RouteGroup $child) {
        $group = new self($prefix);
        $group->routes[] = $child;
        return $group;
    }

    public function match($method, $path, $handler) {
        if (!is_array($method)) {
            $method = [$method];
        }

        $route = new Route(
            $method,
            $path,
            $handler,
            RouteAttributes::createChild($this->attributes)
        );
        $this->routes[] = $route;
        return $route;
    }

    public function group($path, $cb) {
        $group = new self($path, RouteAttributes::createChild($this->attributes));
        $cb($group);
        $this->routes[] = $group;
        return $group;
    }

    public function getRoutes($prefix = '/') {
        $prefix = Util\joinUri($prefix, $this->prefix);

        $routes = iter\flatten(iter\map(function($r) use ($prefix) {
            if ($r instanceof Route) {
                return $r->withPathPrefix($prefix);
            }

            // else we are a group
            return $r->getRoutes($prefix);
        }, $this->routes));

        return iter\toArray($routes);
    }

    public function getPrefix() {
        return $this->prefix;
    }
}

class Route {
    use AttributesAccessor;

    private $methods;
    private $path;
    private $handler;

    public function __construct(array $methods, $path, $handler, RouteAttributes $attributes) {
        $this->methods = $methods;
        $this->path = $path;
        $this->handler = $handler;
        $this->attributes = $attributes;
    }

    public function getMethods() {
        return $this->methods;
    }

    public function getPath() {
        return $this->path;
    }

    public function withPathPrefix($prefix) {
        return new self(
            $this->methods,
            Util\joinUri($prefix, $this->path),
            $this->handler,
            $this->attributes
        );
    }

    public function getHandler() {
        return $this->handler;
    }
}

class MatchedRoute {
    public $params;
    public $route;

    public function __construct($params, $route) {
        $this->params = $params;
        $this->route = $route;
    }
}

trait RouteMatch {
    public function get($uri, $handler) {
        return $this->match('GET', $uri, $handler);
    }
    public function post($uri, $handler) {
        return $this->match('POST', $uri, $handler);
    }
    public function put($uri, $handler) {
        return $this->match('PUT', $uri, $handler);
    }
    public function delete($uri, $handler) {
        return $this->match('DELETE', $uri, $handler);
    }
}
