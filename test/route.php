<?php

use Krak\Http\Route;

describe("Route", function() {
    describe('->withPath', function() {
        it('clones a new route with a path', function() {
            $route = new Route\Route(['GET'], '/', '');
            assert($route->withPath('/a')->path == '/a');
        });
    });
    describe('->withHandler', function() {
        it('clones a enw route with a handler', function() {
            $route = new Route\Route(['GET'], '/', '');
            assert($route->withHandler('handler')->handler == 'handler');
        });
    });
});
describe('RouteAttributes', function() {
    beforeEach(function() {
        $this->route = new Route\Route(['GET'], '/', '');
    });
    describe('->with', function() {
        it('adds an attribute', function() {
            $this->route->with('a', 1);
            assert($this->route->getAttributes()['a'] == 1);
        });
        it('merges attributes with an array', function() {
            $this->route->with('a', 1);
            $this->route->with([
                'a' => 2,
            ]);
            assert($this->route->getAttributes()['a'] == 2);
        });
    });
    describe('->getParent', function() {
        it('returns the parent', function() {
            $route = new Route\Route(['GET'], '/', '', $this->route);
            assert(
                $route->getParent() === $this->route &&
                $this->route->getParent() === null
            );
        });
    });
});
describe('RecursiveRouteCompiler', function() {
    it('compiles a route group', function() {
        $group = new Route\RouteGroup('');
        $group->get('/g', 'get');
        $group->post('/p', 'post');
        $group->group('/r', function($r) {
            $r->delete('/d', 'delete');
        });
        $compiler = new Route\RecursiveRouteCompiler();
        $routes = $compiler->compileRoutes($group, '/');
        $routes = iter\toArray($routes);
        assert(
            count($routes) == 3 &&
            $routes[0]->path == '/g' &&
            $routes[1]->path == '/p' &&
            $routes[2]->path == '/r/d'
        );
    });
});
