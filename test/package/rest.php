<?php

use Krak\Mw\Http,
    Krak\Mw,
    GuzzleHttp\Psr7\ServerRequest;

beforeEach(function() {
    $this->app = new Http\App();
    $this->app->with(Http\Package\std());
    $this->app->with(Http\Package\rest());
});
it('Returns 415 if content-type is not JSON', function() {
    $app = $this->app;
    $handler = mw\compose([$app]);
    $resp = $handler(new ServerRequest('POST', '/a', [], '[1]'));
    assert($resp->getStatusCode() == 415);
});
