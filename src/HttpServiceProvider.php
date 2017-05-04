<?php

namespace Krak\Http;

use Krak\Cargo;
use Krak\Mw;
use Zend\Diactoros;
use Psr\Http\Message\ServerRequestInterface;

class HttpServiceProvider implements Cargo\ServiceProvider
{
    public function register(Cargo\Container $c) {
        $c[ResponseFactory::class] = function() {
            return new ResponseFactory\DiactorosResponseFactory();
        };
        $c[ResponseFactoryStore::class] = function($c) {
            $store = new ResponseFactoryStore();
            $rf = $c[ResponseFactory::class];
            $store->store('json', new ResponseFactory\JsonResponseFactory(
                $rf,
                $c['krak.http.response_factory.json_encode_options']
            ));
            $store->store('html', new ResponseFactory\HtmlResponseFactory($rf));
            $store->store('text', new ResponseFactory\TextResponseFactory($rf));

            return $store;
        };
        $c[RouteCompiler::class] = function() {
            return new Route\RecursiveRouteCompiler();
        };
        $c[DispatcherFactory::class] = function() {
            return new Dispatcher\FastRoute\FastRouteDispatcherFactory();
        };
        $c[Diactoros\Response\EmitterInterface::class] = function() {
            return new Diactoros\Response\SapiEmitter();
        };
        $c->factory(ServerRequestInterface::class, function() {
            return Diactoros\ServerRequestFactory::fromGlobals();
        });
        $c[Server::class] = function($app) {
            return new Server\DiactorosServer(
                $app[Diactoros\Response\EmitterInterface::class],
                function() use ($app) {
                    return $app[ServerRequestInterface::class];
                }
            );
        };
        $c[Route\RouteGroup::class] = function() {
            return new Route\RouteGroup('');
        };
        $c['krak.http.response_factory.json_encode_options'] = 0;
        $c['krak.http.compose'] = function($c) {
            return Mw\composer(
                new Mw\Context\ContainerContext($c->toInterop()),
                Middleware\HttpLink::class
            );
        };
        $c->alias(ServerRequestInterface::class, 'request');
    }
}
