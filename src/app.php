<?php

namespace Krak\Mw\Http;

/** HttpAppMw

    usage:

    ```
    $app = mw\http\restApp();
    $app->get('/', function() {
        return ['home', []];
    });
    $app->get('/a', 'service@getAAction');
    $app->with(pimpleProvider());
    $app->with(platesProvider());

    $app->mws()->push(function($req, $next) {

    });

*/

interface Hook {
    public function with(App $app);
    public function withPostFreeze(App $app);
}

abstract class AbtractHook implements Hook {
    public function with(App $app) {}
    public function withPostFreeze(App $app) {}
}

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

class App
{
    use Routing\RouteMatch;

    private $dispatch_factory;
    private $response_factory;
    private $routes;

    /** middleware stacks */
    private $stacks;

    private $hooks;
    private $frozen;

    public function __construct(AppStacks $stacks = null, $dispatch_factory = null, $response_factory = null, Routing\RouteGroup $routes = null) {
        $this->dispatch_factory = $dispatch_factory ?: Routing\fastRouteDispatchFactory();
        $this->response_factory = $response_factory ?: diactorosResponseFactory();
        $this->routes = $routes ?: new Routing\RouteGroup();
        $this->stacks = $stacks ?: mw\stack();
        $this->hooks = [];
        $this->frozen = false;
    }

    /** forwards to the RouteGroup */
    public function match($method, $uri, $handler) {
        return $this->routes($method, $uri, $handler);
    }
    /** forwards to the RouteGroup */
    public function group($path, $cb) {
        return $this->routes->group($path, $cb);
    }

    /** returns the route group for this http app mw */
    public function routes() {
        return $this->routes;
    }

    /** allows modifications to AppMw in a unified way. This  */
    public function with(Hook $hook) {
        $this->hooks[] = $hook;
        $hook->with($this);
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
        return $serve($mws->compose());
    }

    /** Composes all of the middleware together in the main mws stack */
    public function freeze() {
        if ($this->frozen) {
            return;
        }

        $mws = $this->stacks->mws;
        $dispatcher_factory = $this->dispatcher_factory;
        $dispatcher = $dispatcher_factory($this->routes);

        $this->stacks->invoke_action->push(
            Routing\marshalResponseInvokeAction()
        );
        $mws->push(
            catchException($this->stacks->exception_handler->compose()),
            0,
            'catch_exception'
        );
        $mws->unshift(
            Routing\routingInjectMw($dispatcher, $this->stacks->not_found_handler->compose()),
            0,
            'routing'
        );
        $mws->unshift(Routing\injectRouteMiddlewareMw(), 0, 'routes');
        $mws->unshift(
            Routing\invokeActionMw(
                $this->stacks->invoke_action->compose()
            ),
            0,
            'invoke'
        );

        foreach ($this->hooks as $hook) {
            $hook->withPostFreeze($this);
        }

        $this->frozen = true;
    }
}

// function myHttpAppMw() {
//     $app = new RESTHttpApp();
//
//     // define any routes
//     require __DIR__ . '/routes.php';
//     require __DIR__ . '/middleware.php';
//
//     return $app->compose();
// }
//
// mw\composeMwSet([
//
// ]);
//
// mw\compose();
// mw\group();
//
// $serve = Krak\Mw\Http\server();
//
// $serve(function($req) {
//     return $resp;
// });
