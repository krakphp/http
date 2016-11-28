<?php

namespace Krak\Mw\Http\Package\AutoArgs;

use Psr\Http\Message\ServerRequestInterface;

/** this will inject parameters into the callable based on reflection */
function resolveArgumentsCallableInvokeInvokeAction($resolve_arg) {
    return function(ServerRequestInterface $req, $action, $params) use ($resolve_arg) {
        if (!is_callable($action)) {
            throw new \InvalidArgumentException('The action given was not a callable');
        }

        if (is_array($action)) {
            $rf = new \ReflectionMethod($action[0], $action[1]);
        } else {
            $rf = new \ReflectionFunction($action);
        }

        $args = [];
        foreach ($rf->getParameters() as $i => $arg_meta) {
            $arg = $resolve_arg($arg_meta, $req, $params);
            if (!count($arg)) {
                throw new \RuntimeException(sprintf('Action argument %d is unable to be resolved.', $i));
            }

            $args[] = $arg[0];
        }

        return $action(...$args);
    };
}
