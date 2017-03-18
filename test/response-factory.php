<?php

use Krak\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

$def_factory = function($name, $rf) {
    describe($name, function() use ($rf) {
        it('creates a response', function() use ($rf) {
            $resp = $rf->createResponse(500, ['X-A' => 'a'], 'body');
            assert(
                $resp instanceof ResponseInterface &&
                $resp->getStatusCode() === 500 &&
                $resp->getHeaderLine('X-A') == 'a' &&
                (string) $resp->getBody() == 'body'
            );
        });
    });
};

$def_factory('DiactorosResponseFactory', new ResponseFactory\DiactorosResponseFactory());
$def_factory('GuzzleResponseFactory', new ResponseFactory\GuzzleResponseFactory());
beforeEach(function() {
    $this->rf = new ResponseFactory\DiactorosResponseFactory();
});
describe('HtmlResponseFactory', function() {
    it('creates a html response', function() {
        $rf = new ResponseFactory\HtmlResponseFactory($this->rf);
        $resp = $rf->createResponse();
        assert($resp->getHeaderLine('Content-Type') == 'text/html');
    });
});
describe('TextResponseFactory', function() {
    it('creates a text response', function() {
        $rf = new ResponseFactory\TextResponseFactory($this->rf);
        $resp = $rf->createResponse();
        assert($resp->getHeaderLine('Content-Type') == 'text/plain');
    });
});
describe('JsonResponseFactory', function() {
    it('creates a text response', function() {
        $rf = new ResponseFactory\JsonResponseFactory($this->rf);
        $resp = $rf->createResponse(200, [], [1,2,3]);
        assert(
            (string) $resp->getBody() === '[1,2,3]' &&
            $resp->getHeaderLine('Content-Type') == 'application/json'
        );
    });
});
