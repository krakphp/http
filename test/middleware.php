<?php

use Krak\Mw;
use Krak\Http;
use Psr\Http\Message;

beforeEach(function() {
    $this->container = Krak\Cargo\container();
    $this->container->register(new Http\HttpServiceProvider());
});
describe('#serveStatic', function() {
    it('serves static files from a directory', function() {
        $serve_static = Http\Middleware\serveStatic(__DIR__ . '/resources');
        $compose = $this->container['krak.http.compose'];

        $handler = $compose([
            function($req, $next) { return $next->response(404); },
            $serve_static
        ]);

        $req = $this->container['request'];
        $req = $req->withUri(
            $req->getUri()->withPath('/foo.txt')
        )->withMethod('GET');
        $resp = $handler($req);
        assert($resp->getStatusCode() == 200 && (string) $resp->getBody() == "bar\n");
    });
    it('falls through if no match is found', function() {
        $serve_static = Http\Middleware\serveStatic(__DIR__ . '/resources');
        $compose = $this->container['krak.http.compose'];

        $handler = $compose([
            function($req, $next) { return $next->response(404); },
            $serve_static
        ]);

        $resp = $handler($this->container['request']);
        assert($resp->getStatusCode() == 404);
    });
});
describe('#mount', function() {
    it('mounts a middleware on a specific path', function() {
        $mw = function($req, $next) {
            $path = new Http\RequestPath($req);
            return $next->response(200, [], $path->path('/assets/app.css'));
        };

        $compose = $this->container['krak.http.compose'];
        $handler = $compose([
            Http\Middleware\mount(
                '/admin',
                Http\Middleware\mount('/module', $mw)
            )
        ]);

        $req = $this->container['request'];
        $req = $req->withUri(
            $req->getUri()->withPath('/admin/module')
        )->withMethod('GET');
        $resp = $handler($req);
        assert($resp->getStatusCode() == 200 && (string) $resp->getBody() == "/admin/module/assets/app.css");
    });
});
describe('#wrap', function() {
    it('wraps a psr-7 style middleware', function() {
        $psr7_mw = function(Message\ServerRequestInterface $req, Message\ResponseInterface $resp, callable $next) {
            return $next($req->withAttribute('foo', 'bar'), $resp)->withHeader('A', '1');
        };
        $compose = $this->container['krak.http.compose'];
        $handler = $compose([
            function($req, $next) {
                return $next->response(201, [], $req->getAttribute('foo'));
            },
            Http\Middleware\wrap($psr7_mw)
        ]);

        $resp = $handler($this->container['request']);
        assert($resp->getStatusCode() == 201 && (string) $resp->getBody() == 'bar' && $resp->getHeaderLine('A') == '1');
    });
});
