<?php

use Krak\Mw\Http,
    GuzzleHttp\Psr7\ServerRequest;

use function Krak\Mw\compose;

function composeApp(Http\App $app) {
    $app->freeze();
    return $app['stacks.http']->compose();
}

describe('->mount', function() {
    it('mounts a middleware onto the app', function() {
        $app = new Http\App();
        $app->with(Http\Package\std());
        $app->mount('/a', function() {
            return 1;
        });
        $app->get('/a/1', function() { assert(false); });
        $req = new ServerRequest('GET', '/a/2');
        $handler = composeApp($app);
        assert($handler($req) == 1);
    });
    it('allows for recursive mounts', function() {
        $app1 = new Http\App();
        $app1->with(Http\Package\std());
        $app2 = new Http\App();
        $app2->with(Http\Package\std());
        $app3 = new Http\App();
        $app3->with(Http\Package\std());

        $app3->mount('/c', function() {
            return 4;
        });
        $app1->mount('/a', $app2);
        $app2->mount('/b', $app3);

        $app3->get('/c/1', function() { assert(false); });
        $req = new ServerRequest('GET', '/a/b/c/d');
        $handler = composeApp($app1);
        assert($handler($req) == 4);
    });
    it('allows for recursive mounted routes', function() {
        $app1 = new Http\App();
        $app1->with(Http\Package\std());
        $app2 = new Http\App();
        $app2->with(Http\Package\std());
        $app3 = new Http\App();
        $app3->with(Http\Package\std());

        $app2->mount('/b', $app3);
        $app1->mount('/a', $app2);

        $app3->get('/c/d', function() { return 2; });
        // allow the return of 2 instead of converting into a response
        $app3['stacks.marshal_response']->push(function($res) { return $res; });

        $req = new ServerRequest('GET', '/a/b/c/d');
        $handler = composeApp($app1);
        assert($handler($req) == 2);
    });
});
it('allows pimple aware routes', function() {
    $app = new Http\App();
    $app->with(Http\Package\Std());
    $app['attr_mw'] = $app->protect(function($req, $next) {
        return $next($req->withAttribute(
            'count',
            $req->getAttribute('count') + 1
        ));
    });
    $app->push('attr_mw');
    $app['routes']->push('attr_mw');
    $app->get('/', function($req) {
        return $req->getAttribute('count');
    });
    $app['stacks.marshal_response']->push(function($r) { return $r; });

    $req = new ServerRequest('GET', '/');
    $handler = composeApp($app);

    assert($handler($req) == 2);
});
