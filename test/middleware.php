<?php

use Krak\Mw;
use Krak\Http;

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
