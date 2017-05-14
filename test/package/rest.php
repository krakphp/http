<?php

use Krak\Mw\Http,
    GuzzleHttp\Psr7\ServerRequest;
use Krak\Mw;
beforeEach(function() {
    $this->app = new Http\App();
    $this->app->with(Http\Package\std());
    $this->app->with(Http\Package\rest());
});
it('Returns 415 if content-type is not JSON', function() {
    $app = $this->app;
    $handler = Mw\compose([$app]);
    assert($handler(new ServerRequest('POST', '/a', [], '[1]'))->getStatusCode() == 415);
});
