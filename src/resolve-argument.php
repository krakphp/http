<?php

namespace Krak\Mw\Http;

use Psr\Http\Message,
    ReflectionParameter;

interface ResolveArgument
{
    /** @return array an array of results to feed in. The array should be of size one in most cases */
    public function __invoke(ReflectionParameter $arg_meta, Message\ServerRequestInterface $req, $params);
}

function _isSubclassOf($inst_class_name, $class) {
    return $class && ($inst_class_name == $class->getName() || is_subclass_of($class->getName(), $inst_class_name));
}


function routeParameterResolveArgument() {
    return function(ReflectionParameter $arg_meta, $req, $params, $next) {
        if (array_key_exists($arg_meta->getName(), $params)) {
            return [$params[$arg_meta->getName()]];
        }

        return $next($arg_meta, $req, $params);
    };
}

function requestResolveArgument() {
    return function($arg_meta, $req, $params, $next) {
        $class = $arg_meta->getClass();
        $name = $class->getName();

        if (_isSubclassOf(Message\ServerRequestInterface::class, $class)) {
            return [$req];
        }

        return $next($arg_meta, $req, $params);
    };
}

/** checks if the arg is instance or subclass of the class instance, if so, then injects the
    object. Very useful for allowing normal classes being passed into the parameters */
function instanceResolveArgument($get_instance) {
    return function($arg_meta, $req, $params, $next) use ($get_instance) {
        $class = $arg_meta->getClass();

        $instance = $get_instance();
        if (_isSubclassOf(get_class($instance), $class)) {
            return [$instance];
        }

        return $next($arg_meta, $req, $params);
    };
}

function defaultValueResolveArgument() {
    return function($arg_meta, $req, $params, $next) {
        if (!$arg_meta->isDefaultValueAvailable()) {
            return $next($arg_meta, $req, $params);
        }

        return [$arg_meta->getDefaultValue()];
    };
}
