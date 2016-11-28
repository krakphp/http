<?php

use Krak\Mw\Http\Package\AutoArgs,
    GuzzleHttp\Psr7\ServerRequest;

beforeEach(function() {
    $this->req = new ServerRequest('GET', '/');
    $f = function($a = 2, InvalidArgumentException $e, Psr\Http\Message\ServerRequestInterface $req) {

    };
    $this->params = (new ReflectionFunction($f))->getParameters();
    $this->die = function() { assert(false); };
});

describe('#instanceResolveArgument', function() {
    it('only resolves the argument if it is instance of object', function() {
        $ex = new Exception();
        $resolve = AutoArgs\instanceResolveArgument(function() use ($ex) { return $ex; });

        $arg = $resolve($this->params[1], $this->req, [], $this->die);
        assert($arg[0] === $ex);
    });
});
describe('#requestResolveArgument', function() {
    it('resolves the argument if it is typehinted as a psr request', function() {
        $resolve = AutoArgs\requestResolveArgument();
        $arg = $resolve($this->params[2], $this->req, [], $this->die);
        assert($arg[0] === $this->req);
    });
});
describe('#routeParameterResolveArgument', function() {
    it('resolves the argument if the name matches the route parameter name', function() {
        $resolve = AutoArgs\routeParameterResolveArgument();
        $arg = $resolve($this->params[0], $this->req, ['a' => 1], $this->die);
        assert($arg[0] === 1);
    });
});
describe('#defaultValueResolveArgument', function() {
    it('resolves the argument with the default value if it has one', function() {
        $resolve = AutoArgs\defaultValueResolveArgument();
        $arg = $resolve($this->params[0], $this->req, ['b' => 1], $this->die);
            assert($arg[0] === 2);
    });
});
