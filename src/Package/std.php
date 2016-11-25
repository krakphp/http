<?php

namespace Krak\Mw\Http\Package;

use Evenement\EventEmitter,
    Krak\Mw,
    Krak\Mw\Http,
    Pimple;

class StdPackage implements Http\Package
{
    private $config;

    public function __construct(array $config = []) {
        $this->config = $config + [
            'controller_prefix' => '',
            'controller_method_separater' => '@',
            'request_attribute_name' => 'app',
        ];
    }

    public function with(Http\App $app) {
        $app->register(new StdServiceProvider());

        $app->exceptionHandler()
            ->push(stdExceptionHandler($app['response_factory']));
        $app->notFoundHandler()
            ->push(stdNotFoundHandler($app['response_factory']));
        $app->marshalResponse()
            ->push(Http\stringMarshalResponse())
            ->unshift(Http\redirectMarshalResponse(), 1)
            ->unshift(Http\httpTupleMarshalResponse(), 1);
        $app->invokeAction()
            ->push(Http\callableInvokeAction())
            ->push(Http\pimpleInvokeAction(
                $app->getContainer(),
                $this->config['controller_prefix'],
                $this->config['controller_method_separater']
            ));

        $app->push(Http\injectRequestAttribute(
            $this->config['request_attribute_name'],
            $app
        ), 1);

        $app->invokeAction()->push(
            Http\pimpleInvokeAction(
                $app->getContainer(),
                $this->config['controller_prefix'],
                $this->config['controller_method_separater']
            )
        );
    }
}

class StdServiceProvider implements Pimple\ServiceProviderInterface
{
    public function register(Pimple\Container $app) {
        $app['routes'] = new Http\RouteGroup();
        $app['dispatcher_factory'] = function() {
            return Http\dispatcherFactory();
        };
        $app['response_factory'] = function() {
            return Http\responseFactory();
        };
        $app['event_emitter'] = function() {
            return new EventEmitter();
        };
        $app['freezer'] = function() {
            return stdFreezer();
        };
        $app['stacks.exception_handler'] = function() { return Mw\stack(); };
        $app['stacks.invoke_action'] = function() { return Mw\stack(); };
        $app['stacks.not_found_handler'] = function() { return Mw\stack(); };
        $app['stacks.marshal_response'] = function() { return Mw\stack(); };
        $app['stacks.http'] = function() { return Mw\stack(); };
    }
}


function std(array $config = []) {
    return new StdPackage($config);
}

function stdExceptionHandler($rf) {
    return function($req, $exception) use ($rf) {
        return $rf(500, ['Content-Type' => 'text/plain'], (string) $exception);
    };
}

function stdNotFoundHandler($rf) {
    return function($req, $result) use ($rf) {
        $headers = ['Content-Type' => 'text/plain'];
        if ($result->status_code == 405) {
            $headers['Allow'] = implode(", ", $result->allowed_methods);
        }

        return $rf(
            $result->status_code,
            $headers,
            $result->status_code == 405 ? '405 Method Not Allowed' : '404 Not Found'
        );
    };
}

function stdFreezer() {
    return function($app) {
        $mws = $app['stacks.http'];
        $dispatcher_factory = $app['dispatcher_factory'];
        $dispatcher = $dispatcher_factory($app['routes']);

        $mws->push(
            Http\catchException($app['stacks.exception_handler']->compose()),
            0,
            'catch_exception'
        );
        $mws->unshift(
            Http\injectRoutingMiddleware(
                $dispatcher,
                $app['stacks.not_found_handler']->compose()
            ),
            0,
            'routing'
        );
        $mws->unshift(Http\injectRouteMiddleware(), 0, 'routes');
        $mws->unshift(
            Http\invokeRoutingAction(
                $app['stacks.invoke_action']->compose(),
                $app['stacks.marshal_response']->compose(),
                $app['response_factory']
            ),
            0,
            'invoke'
        );
    };
}
