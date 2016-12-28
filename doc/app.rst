===
App
===

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
========

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

Pimple Aware Middleware
=======================

API
===

class App implements \\ArrayAccess, Evenement\\EventEmitterInterface
--------------------------------------------------------------------

__construct(Pimple\\Container $container = null)
    Entry point into creating the app. You can optionally pass in a container if you'd like. Else one will be created.
createStack($name, array $entries = [])
    Creates a Pimple aware ``Krak\Mw\MwStack`` with the app's Pimple Container.

    .. code-block:: php

        <?php

        $app = new Krak\Mw\Http\App();
        $stack = $app->createStack('Stack');
        $stack->push('pimple_service_name');

    If the ``pimple_service_name`` is defined in the container, then it will use that for the middleware.

defineStack($key, $name, array $entries = [])
    Defines a Pimple aware stack as a parameter into the pimple container. This is just a convenience method for defining stacks on the container because each stack needs to be protected via ``$container->protect($stack)``.

    .. code-block:: php

        <?php

        $app = new Krak\Mw\Http\App();
        $app->defineStack('my_stack', 'Stack');
        $app['my_stack']->push(function() {});

withRoutePrefix($prefix)
    

The App class also implements every method in the ``Pimple\Container`` class and forwards it to the appropriate container instance in the application.

    /** forwards to the RouteGroup */
    public function match(...$args) {
        return $this['routes']->match(...$args);
    }
    /** forwards to the RouteGroup */
    public function group(...$args) {
        return $this['routes']->group(...$args);
    }

    /** utility for creating a new app on a new route prefix */
    public function withRoutePrefix($prefix) {
        $app = clone $this;
        $app['routes'] = RouteGroup::createWithGroup($prefix, $app['routes']);
        return $app;
    }

    /** mounts a middleware onto the stack at uri prefix. The mounted middleware is
        pushed on the main http stack and will be executed before the standard routing
        middleware. This is useful for mounting other applications or authentication
        middleware */
    public function mount($prefix, $mw) {
        $mw = function(...$all_params) use ($prefix, $mw) {
            list($req, $next, $invoke) = $all_params;
            if (!$mw instanceof self) {
                return $invoke($mw, ...$all_params);
            }

            $prefix = Util\joinUri($this['routes']->getPrefix(), $prefix);
            $mw = $mw->withRoutePrefix($prefix);
            return $invoke($mw, ...$all_params);
        };

        $mw = mw\filter($mw, function($req) use ($prefix, $mw) {
            $prefix = Util\joinUri($this['routes']->getPrefix(), $prefix);
            return strpos($req->getUri()->getPath(), $prefix) === 0;
        });

        return $this->push($mw);
    }

    /** forward to the Event Emitter */
    public function on($event, callable $listener) {
        return $this['event_emitter']->on($event, $listener);
    }
    public function once($event, callable $listener) {
        return $this['event_emitter']->once($event, $listener);
    }
    public function removeListener($event, callable $listener) {
        return $this['event_emitter']->removeListener($event, $listener);
    }
    public function removeAllListeners($event = null) {
        return $this['event_emitter']->removeAllListeners($event);
    }
    public function listeners($event) {
        return $this['event_emitter']->listeners($event);
    }
    public function emit($event, array $arguments = []) {
        return $this['event_emitter']->emit($event, $arguments);
    }

    /** allows modifications to App in a unified way. This  */
    public function with(Package $pkg) {
        $pkg->with($this);
        return $this;
    }

    /** Forward to main http stack */
    public function push($mw, $sort = 0, $name = null) {
        return $this['stacks.http']->push($mw, $sort, $name);
    }
    /** Forward to main http stack */
    public function pop($sort = 0) {
        return $this['stacks.http']->push($sort);
    }
    /** Forward to main http stack */
    public function unshift($mw, $sort = 0, $name = null) {
        return $this['stacks.http']->unshift($mw, $sort, $name);
    }
    /** Forward to main http stack */
    public function shift($sort = 0) {
        return $this['stacks.http']->shift($sort);
    }

    /** forward to Pimple */
    public function offsetExists($offset) {
        return $this->container->offsetExists($offset);
    }
    /** forward to Pimple */
    public function offsetGet($offset) {
        return $this->container->offsetGet($offset);
    }
    /** forward to Pimple */
    public function offsetSet($offset, $value) {
        return $this->container->offsetSet($offset, $value);
    }
    /** forward to Pimple */
    public function offsetUnset($offset) {
        return $this->container->offsetUnset($offset);
    }
    public function factory($callable) {
        return $this->container->factory($callable);
    }
    public function protect($callable) {
        return $this->container->protect($callable);
    }
    public function raw($id) {
        return $this->container->raw($id);
    }
    public function extend($id, $callable) {
        return $this->container->extend($id, $callable);
    }
    public function keys() {
        return $this->container->keys();
    }
    /** forward to Pimple */
    public function register(Pimple\ServiceProviderInterface $provider, array $values = []) {
        return $this->container->register($provider, $values);
    }
    /** return the pimple container */
    public function getContainer() {
        return $this->container;
    }

    /** middleware interface */
    public function __invoke(...$params) {
        $this->freeze();
        $http = $this['stacks.http'];
        return $http(...$params);
    }

    /** serves an app with a default server if non is provided */
    public function serve() {
        $serve = $this['server'];
        $this->freeze();
        $mws = $this['stacks.http'];

        $this->emit(Events::INIT, [$this]);
        $serve($mws->compose());
        $this->emit(Events::FINISH, [$this]);
    }

    /** Composes all of the middleware together in the main mws stack */
    public function freeze() {
        if ($this->frozen) {
            return;
        }

        $freezer = $this->container['freezer'];
        $freezer->freezeApp($this);
        $this->frozen = true;

        $this->emit(Events::FROZEN, [$this]);
    }
