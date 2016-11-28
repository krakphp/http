<?php

namespace Krak\Mw\Http;

use Pimple,
    Evenement\EventEmitterInterface,
    Krak\Mw;

class App implements \ArrayAccess, EventEmitterInterface
{
    use RouteMatch;

    const VERSION = '0.2.0';

    private $container;
    private $frozen;

    public function __construct(Pimple\Container $container = null) {
        $this->container = $container ?: new Pimple\Container();
        $this->frozen = false;
    }

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
    public function push(callable $mw, $sort = 0, $name = null) {
        return $this['stacks.http']->push($mw, $sort, $name);
    }
    /** Forward to main http stack */
    public function pop($sort = 0) {
        return $this['stacks.http']->push($sort);
    }
    /** Forward to main http stack */
    public function unshift(callable $mw, $sort = 0, $name = null) {
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
    public function serve($serve = null) {
        $serve = $serve ?: server();
        $this->freeze();
        $mws = $this['stacks.http'];

        $this->emit(Events::INIT, [$this]);
        $res = $serve($mws->compose());
        $this->emit(Events::FINISH, [$this]);
        return $res;
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
}
