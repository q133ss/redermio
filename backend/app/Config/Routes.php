<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'cors'], static function ($routes) {
    $routes->get('health', 'HealthController::index');
    $routes->get('services', 'ServicesController::index');
    $routes->options('(:any)', static fn () => service('response')->setStatusCode(204));
});
