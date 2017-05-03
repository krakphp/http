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

/** wraps psr-7 middleware */
function wrap($middleware) {
    return function($req, $next) use ($middleware) {
        return $middleware($req, $next->response(200), function($req, $resp) use ($next) {
            return $next($req);
        });
    };
}

/** Serves static files over a directory. The $root is a path to physical directory
    to where the static files lie. */
function serveStatic($root) {
    return function($req, $next) use ($root) {
        if ($req->getMethod() != 'GET') {
            return $next($req);
        }

        $path = $req->getUri()->getPath();
        $file_path = $root . $path;
        if (!is_file($file_path)) {
            return $next($req);
        }

        return $next->response(200, [
            'Content-Type' => mime_content_type($file_path),
        ], fopen($file_path, "r"));
    };
}

function mount($path, $mw) {
    $mw = mw\group([
        function(ServerRequestInterface $req, $next) {
            $stack = $req->getAttribute('original_uri_path');
            list($path) = $stack->pop();
            $req = $req->withUri($req->getUri()->withPath($path));
            if (!count($stack)) {
                $req = $req->withoutAttribute('original_uri_path');
            }
            return $next($req);
        },
        $mw,
        function(ServerRequestInterface $req, $next) use ($path) {
            $orig_path = $req->getUri()->getPath();
            $stack = $req->getAttribute('original_uri_path') ?: new \SplDoublyLinkedList();
            $stack->push([$orig_path, $path]);
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
