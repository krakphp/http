<?php

use Krak\Mw\Http;

require_once __DIR__ . '/../vendor/autoload.php';

class Controller {
    public function getAction($req, $params) {
        $c = $req->getAttribute('pimple');
        return [200, [1,2,3, $c['param'], (int) $params['num']]];
    }
}

$c = new Pimple\Container();
$c['controller'] = function() {
    return new Controller();
};
$c['param'] = 4;

$plates = new League\Plates\Engine(__DIR__ . '/views', 'phtml');

$app = new Http\App();
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
$app->with(Http\Package\std());
$app->with(Http\Package\rest(null, JSON_PRETTY_PRINT));
$app->with(Http\Package\pimple($c));

$app1 = new Http\App();
$app1->with(Http\Package\std());
$app1->with(Http\Package\plates($plates));

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
$app->mws()->push(
    http\mount('/admin', $app1)
);
$app->serve();
