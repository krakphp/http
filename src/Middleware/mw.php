<?php

namespace Krak\Http\Middleware;

use Krak\Mw,
    Psr\Http\Message\ServerRequestInterface,
    Psr\Http\Message\ResponseInterface;

function injectRequestAttribute($name, $value) {
    return function(ServerRequestInterface $req, $next) use ($name, $value) {
        return $next($req->withAttribute($name, $value));
    };
}

function mount($path, $mw) {
    $mw = mw\group([
        function(ServerRequestInterface $req, $next) {
            $stack = $req->getAttribute('original_uri_path');
            $path = $stack->pop();
            $req = $req->withUri($req->getUri()->withPath($path));
            if (!count($stack)) {
                $req = $req->withoutAttribute('original_uri_path');
            }
            return $next($req);
        },
        $mw,
        function(ServerRequestInterface $req, $next) use ($path) {
            $orig_path = $req->getUri()->getPath();
            $stack = $req->getAttribute('original_uri_path') ?: new \SplStack();
            $stack->push($orig_path);
            $new_path = substr($orig_path, strlen($path));
            if (!$new_path) {
                $new_path = '/';
            }
            $req = $req->withUri($req->getUri()->withPath($new_path))
                ->withAttribute('original_uri_path', $stack);
            return $next($req);
        },
    ]);

    return mw\filter($mw, function($req) use ($path) {
        return strpos($req->getUri()->getPath(), $path) === 0;
    });
}
