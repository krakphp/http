<?php

use Krak\Mw\Http;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Http\App();
$app->with(Http\Package\std());
$app->with(Http\Package\autoArgs());

$app->get('/{id}', function(
    Http\App $app,
    Pimple\Container $container,
    $id,
    Psr\Http\Message\ServerRequestInterface $req
) {
    $fmt = <<<DATA
\$app instanceof Krak\Mw\Http\App == %d
\$container instanceof Pimple\Container == %d
\$req instanceof Psr\Http\Message\ServerRequestInterface == %d
\$id == $id
DATA;
    $body = sprintf(
        $fmt,
        $app instanceof Http\App,
        $container instanceof Pimple\Container,
        $req instanceof Psr\Http\Message\ServerRequestInterface
    );

    return [200, ['Content-Type' => 'text/plain'], $body];
});

$app->serve();
