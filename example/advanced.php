<?php

use Krak\Mw\Http;

require_once __DIR__ . '/../vendor/autoload.php';

class Controller {
    public function getAction($req, $params) {
        $app = $req->getAttribute('app');
        return [200, [1,2,3, $app['param'], (int) $params['num']]];
    }
}

// $plates = new League\Plates\Engine(__DIR__ . '/views', 'phtml');
$app = new Http\App();
$app->with(Http\Package\std([
    'controller_prefix' => 'krak.'
]));
$app->with(Http\Package\rest(null, JSON_PRETTY_PRINT));

$app->get('/', function($req) {
    return [
        'a' => 1,
        'x' => $req->getAttribute('x'),
    ];
})->push(function($req, $next) {
    return $next($req->withAttribute('x', 'y'));
});
$app->get('/bad', function() {
    throw new Exception('Bad!');
});
$app->get('/controller/{num}', 'controller@getAction');

$app['param'] = 4;
$app['krak.controller'] = function() {
    return new Controller();
};

$app1 = new Http\App();
$app1->with(Http\Package\std());
$app1->with(Http\Package\plates([
    'plates.views_path' => __DIR__ . '/views',
    'plates.ext' => 'phtml'
]));

$app1->get('/', function() {
    return 'Hi!';
});
$app1->get('/a', function() {
    return 'a!';
});
$app1->get('/view', function() {
    return ['test', ['var' => 'Hello World!']];
});
$app1->get('/view-http', function() {
    return [
        201,
        ['test', ['var' => 'Check the http status']]
    ];
});
$app1->get('/ex', function() {
    throw new \Exception('Somthing Bad Happened!');
});

$app->push(Http\mount('/admin', $app1));
$app->serve();
