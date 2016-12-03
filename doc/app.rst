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
    This needs to be a service defined as ``Krak\Mw\MwStack``
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
