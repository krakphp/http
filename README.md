# Http

The Krak Http package is a set of utilities for building Http applications. It comes with an implementation agnostic routing system, PSR-7 Response Factories, PSR-7 Server implementation, and a handful of useful middleware for Http applications.

## Installation

Install via composer at `krak/http`

## Usage

### Response Factories

```php
<?php

interface ResponseFactory {
    public function createResponse($status = 200, array $headers = [], $body = null);
}
```

Every response factory must implement that interface.

```php
<?php

use Krak\Http\ResponseFactory;

$rf = new ResponseFactory\DiactorosResponseFactory();
$rf = new ResponseFactory\GuzzleResponseFactory();

// adds html content-type header
$html_rf = new ResponseFactory\HtmlResponseFactory($rf);
// json encodes the body and add json content-type header. Accepts json_encode_options as second parameter
$json_rf = new ResponseFactory\JsonResponseFactory($rf, JSON_PRETTY_PRINT);
// adds text content-type header
$text_rf = new ResponseFactory\TextResponseFactory($rf);

$json_rf->createResponse(200, [], [1,2,3]);
```

### Routes

```php
<?php

use Krak\Http\Route;

$routes = new Route\RouteGroup();
$routes->get('/', function() {})->with('attribute', 'value');
$routes->group('/foo', function($foo) {
    $sub->get('', 'handler');
    $sub->group('/bar', function($bar) {
        $bar->get('/baz', 'handler');
    });
});
$routes->with('attribute1', 'value');
```

### Compiling Routes

Once you've created a set of routes, you can then compile them with a route compiler. These will traverse the hierarchy of routes and flatten them into an iterator with normalized paths.

```php
<?php

use Krak\Http\Route;

$routes = new Route\RouteGroup();
// add routes to $routes

$compiler = new Route\RecursiveRouteCompiler();
// compile on a path
$routes = $compiler->compileRoutes($routes, '/');
```

### Dispatch

To dispatch a set of routes, you need to create dispatcher factory, which will create a dispatcher from a set of routes, then you can dispatch a PSR-7 request.

```php
<?php

use Krak\Http\Dispatcher;
$dispatch_factory = new Dispatcher\FastRoute\FastRouteDispatcherFactory();
$dispatch = $dispatch_factory->createDispatcher($routes);
$res = $dispatch->dispatch($req);

// $res->status_code
// $res->matched_route->route
// $res->matched_route->params
// $res->allowed_methods /* if status code is a 405 response */
```

### Server

The server is responsible for creating a request, and emitting a response. It's a simple interface:

```php
<?php

interface Server {
    /** @param $handler resolves the request into a response object */
    public function serve($handler);
}
```

```php
<?php

$server = new Krak\Http\Server\DiactorosServer();
$server->serve(function($req) {
    return new Zend\Diactoros\Response();
});
```

### Middleware

Here are several useful middleware to use within your own applications. Each middleware takes two arguments: A PSR-7 Server Request, and an HttpLink. If you want more documentation on how the Link's work, checkout the Krak\\Mw library.

```php
<?php

use Psr\Http\Message\ServerRequestInterface;
use Krak\Http\Middleware\HttpLink;

function myMiddleware() {
    return function(ServerRequestInterface $req, HttpLink $next) {
        if (certainCondition($req)) {
            return $next($req->withAttribute('a', '1'))->withStatusCode(404);
        }

        return $next->response(404, ['X-Header' => 'value'], 'Some body'); // can also use a php or psr-7 stream.
    };
}
```

#### injectRequestAttribute($name, $value)

This will automatically inject an attribute with a name and the given value.

#### wrap($psr7_middleware)

This will wrap PSR-7 style middleware that use the request and response in the middleware parameters.

```php
<?php

$mw = Krak\Http\Middleware\wrap(function($req, $resp, callable $next) {

});
```

#### serveStatic($root)

This will sit and will check if a file exists at the URI path. If it does, it will serve the file, else it will fall through to the next middleware.

#### mount($path, $mw)

Mounts a middleware on a path prefix. If the path prefix is matched, then the middleware is invoked.

```php
<?php

use function Krak\Http\Middleware\{mount, serveStatic};

$mw = mount('/assets', serveStatic(__DIR__ . '/path/to/assets'));
```

The above middleware will try to load files on the `/assets` uri. So `GET /assets/app.css` will return a css file content *if* `__DIR__ . /path/to/assets/app.css` exists in the filesystem.

## Tests and Examples

Run tests via:

```bash
make test
```
