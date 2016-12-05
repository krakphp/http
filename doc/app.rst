Application
===========

The app is the central point for an http application. It manages core services to build
your application: Pimple Container, Evenement event dispatcher, stacks of middleware,
and routes.

The app is just an interface into each of those separate components and also provides the glue
to serve applications. The packages themselves have the ability to define how the app will function.
This is done by registering services into the app as they would with pimple, and then modifying those services.

These are the following services that are required to be set before the app can properly function:

``$app['routes']``
    This needs to be **parameter** (not a service) set to an instance ``Krak\Mw\Http\RouteGroup``.
``$app['event_emitter']``
    This needs to be a service defined as ``Evenement\EventEmitterInterface``
``$app['stacks.http']``
    This needs to be a **parameter** defined as ``Krak\Mw\MwStack``
``$app['freezer']``
    This needs to be a service defined as ``Krak\Mw\Http\Freezer``
``$app['server']``
    This needs to be a service defined as ``Krak\Mw\Http\Sever``. Any normal callable will work as well as long
    as the function signature matches the Server signature.

usage:

.. code-block:: php

    <?php

    use Krak\Mw\Http;

    $app = new Http\App();
    $app->with(Http\Package\std());
    $app->get('/', function() {
        return "Hello World!";
    });
    $app->serve();

The App at its core manages a stack of http middleware. When the ``serve()`` function is called, it freezes the middleware into the http stack and then executes it.

The App also servers as a callable middleware that you can use in any place you would use a normal middleware. This is very useful for things like Mounting.

Mounting
--------

Http Apps allow the mounting of middleware onto the app itself. You mount the middleware on a prefix. This is similar to adding middleware onto route groups, but it is different because these middleware are executed before the routing phase. This fact is crucial because it allows you to perform authentication before routing and mount full featured apps without having to go through the routing process of the base app.

example:

.. code-block:: php

    <?php

    use Krak\Mw\Http;

    $app = new Http\App();
    $app->with(Http\Package\std());

    $app->get('/', function() {
        return 'Home';
    });

    $api = new Http\App();
    $api->with(Http\Package\std());
    $api->with(Http\Package\rest());
    $api->get('/users', function() {
        return [
            ['id' => 1],
            ['id' => 2]
        ];
    });
    $api->mount('/users', function($req, $next) use ($api) {
        // basic auth with user `foo` and password `bar`
        $rf = http\jsonResponseFactory($api['response_factory']);

        if (!$req->hasHeader('Authorization')) {
            return $rf(401, ['WWW-Authenticate' => 'Basic realm="/"'], ['code' => 'unauthorized']);
        }
        if ($req->getHeader('Authorization')[0] != 'Basic Zm9vOmJhcg==') {
            return $rf(403, [], ['code' => 'forbidden']);
        }

        return $next($req);
    });

    $app->mount('/api', $api);

    $app->serve();

In this example, all calls to the ``/api*`` will be handled via the ``$api`` application instead of the base ``$app``. Every call to ``/api/users*`` will now have to go through Basic authentication before the routing starts.
