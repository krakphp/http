<?php

namespace Krak\Mw\Http;

use Evenement,
    Krak\Mw;

/** HttpAppMw

    usage:

    ```
    $app = mw\http\restApp();
    $app->get('/', function() {
        return ['home', []];
    });
    $app->get('/a', 'service@getAAction');
    $app->with(Mw\Http\Package\std());

    $app->mws()->push(function($req, $next) {

    });

*/
class AppStacks
{
    public $exception_handler;
    public $invoke_action;
    public $not_found_handler;
    public $marshal_response;
    public $mws;

    public function __construct(
        Mw\MwStack $exception_handler = null,
        Mw\MwStack $invoke_action = null,
        Mw\MwStack $not_found_handler = null,
        Mw\MwStack $marshal_response = null,
        Mw\MwStack $mws = null
    ) {
        $this->exception_handler = $exception_handler ?: mw\stack();
        $this->invoke_action = $invoke_action ?: mw\stack();
        $this->not_found_handler = $not_found_handler ?: mw\stack();
        $this->marshal_response = $marshal_response ?: mw\stack();
        $this->mws = $mws ?: mw\stack();
    }
}

/** Http Application

    The app is the central point for an http application. It manages core services to build
    your application: Evenement event dispatcher, stacks of middleware,
    and routes.

    The app is just an interface into each of those separate components and also provides the glue
    to serve applications.
*/
class App
{
    use RouteMatch;

    /** middleware stacks */
    private $stacks;

    private $emitter;
    private $dispatcher_factory;
    private $response_factory;
    private $routes;

    private $packages;
    private $frozen;

    public function __construct(AppStacks $stacks = null, $dispatcher_factory = null, $response_factory = null, RouteGroup $routes = null, Evenement\EventEmitterInterface $emitter = null) {
        $this->stacks = $stacks ?: new AppStacks();
        $this->dispatcher_factory = $dispatcher_factory ?: dispatcherFactory();
        $this->response_factory = $response_factory ?: responseFactory();
        $this->routes = $routes ?: new RouteGroup();
        $this->emitter = $emitter ?: new Evenement\EventEmitter();
        $this->packages = [];
        $this->frozen = false;
    }

    /** forwards to the RouteGroup */
    public function match($method, $uri, $handler) {
        return $this->routes->match($method, $uri, $handler);
    }
    /** forwards to the RouteGroup */
    public function group($path, $cb) {
        return $this->routes->group($path, $cb);
    }

    /** returns the route group for this http app mw */
    public function routes() {
        return $this->routes;
    }

    public function eventEmitter() {
        return $this->emitter;
    }

    public function withPrefix($prefix) {
        $app = clone $this;
        $app->routes = RouteGroup::createWithGroup($prefix, $app->routes);
        return $app;
    }

    /** allows modifications to AppMw in a unified way. This  */
    public function with(Package $pkg) {
        $this->packages[] = $pkg;
        $pkg->with($this);
        return $this;
    }

    public function responseFactory() {
        return $this->response_factory;
    }

    /** returns the middleware stack of exception handlers */
    public function exceptionHandler() {
        return $this->stacks->exception_handler;
    }
    /** returns the invokeAction middleware exception handlers */
    public function invokeAction() {
        return $this->stacks->invoke_action;
    }
    /** returns the notFound middleware set */
    public function notFoundHandler() {
        return $this->stacks->not_found_handler;
    }
    /** returns the marshalResponse middleware set */
    public function marshalResponse() {
        return $this->stacks->marshal_response;
    }
    /** returns the main middleware stack */
    public function mws() {
        return $this->stacks->mws;
    }
    /** returns all middleware stacks */
    public function stacks() {
        return $this->stacks;
    }

    /** middleware interface */
    public function __invoke(...$params) {
        $this->freeze();
        $mws = $this->mws();
        return $mws(...$params);
    }

    /** serves an app with a default server if non is provided */
    public function serve($serve = null) {
        $serve = $serve ?: server();
        $this->freeze();
        $mws = $this->mws();

        $this->eventEmitter()->emit(Events::INIT, [$this]);
        $res = $serve($mws->compose());
        $this->eventEmitter()->emit(Events::FINISH, [$this]);
        return $res;
    }

    /** Composes all of the middleware together in the main mws stack */
    public function freeze() {
        if ($this->frozen) {
            return;
        }

        $mws = $this->stacks->mws;
        $dispatcher_factory = $this->dispatcher_factory;
        $dispatcher = $dispatcher_factory($this->routes);

        $mws->push(
            catchException($this->stacks->exception_handler->compose()),
            0,
            'catch_exception'
        );
        $mws->unshift(
            injectRoutingMiddleware(
                $dispatcher,
                $this->stacks->not_found_handler->compose()
            ),
            0,
            'routing'
        );
        $mws->unshift(injectRouteMiddleware(), 0, 'routes');
        $mws->unshift(
            invokeRoutingAction(
                $this->stacks->invoke_action->compose(),
                $this->stacks->marshal_response->compose(),
                $this->response_factory
            ),
            0,
            'invoke'
        );

        $this->eventEmitter()->emit(Events::FROZEN, [$this]);

        $this->frozen = true;
    }
}

function stdApp() {
    $app = new App();
    $app->with(Package\std());
    return $app;
}

function restApp() {
    $app = stdApp();
    $app->with(Package\rest());
    return $app;
}

function webApp() {
    $app = stdApp();
    $app->with(Package\plates());
    return $app;
}
