<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'cors'], static function ($routes) {
    $routes->get('health', 'HealthController::index');
    $routes->get('beneficiaries/import-template', 'BeneficiariesController::importTemplate');
    $routes->post('beneficiaries/import', 'BeneficiariesController::import');
    $routes->get('beneficiaries/export', 'BeneficiariesController::export');
    $routes->get('beneficiaries/(:num)/services', 'BeneficiaryServicesController::index/$1');
    $routes->post('beneficiaries/(:num)/services', 'BeneficiaryServicesController::create/$1');
    $routes->delete('beneficiaries/(:num)/services/(:num)', 'BeneficiaryServicesController::delete/$1/$2');
    $routes->resource('beneficiaries', [
        'controller' => 'BeneficiariesController',
        'only' => ['index', 'show', 'create', 'update', 'delete'],
    ]);
    $routes->resource('services', [
        'controller' => 'ServicesController',
        'only' => ['index', 'create', 'update', 'delete'],
    ]);
    $routes->options('(:any)', static fn () => service('response')->setStatusCode(204));
});
