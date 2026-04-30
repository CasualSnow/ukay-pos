<?php
session_start();

require_once __DIR__ . '/../database/app/Core/Router.php';
require_once __DIR__ . '/../database/app/Core/Controller.php';
require_once __DIR__ . '/../database/app/Core/Model.php';

$router = new Router();

// Routes
$router->add('GET', '/', 'AuthController@showLogin');
$router->add('GET', '/login', 'AuthController@showLogin');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET', '/logout', 'AuthController@logout');

$router->add('GET', '/pos', 'PosController@index');
$router->add('GET', '/api/items', 'PosController@getItems');
$router->add('POST', '/api/checkout', 'PosController@checkout');

$router->add('GET', '/dashboard', 'DashboardController@index');
$router->add('GET', '/inventory', 'InventoryController@index');
$router->add('POST', '/inventory/add', 'InventoryController@add');
$router->add('POST', '/inventory/update', 'InventoryController@update');
$router->add('POST', '/inventory/delete', 'InventoryController@delete');

$router->add('GET', '/reports', 'ReportController@index');

$router->add('GET', '/users', 'UserController@index');
$router->add('POST', '/users/add', 'UserController@add');
$router->add('POST', '/users/update', 'UserController@update');
$router->add('POST', '/users/delete', 'UserController@delete');

$router->add('GET', '/reservations', 'ReservationController@index');
$router->add('POST', '/reservations/add', 'ReservationController@add');
$router->add('POST', '/reservations/delete', 'ReservationController@delete');
$router->add('POST', '/reservations/complete', 'ReservationController@complete');
$router->add('POST', '/reservations/cancel', 'ReservationController@cancel');
$router->add('POST', '/reservations/pay', 'ReservationController@pay');

$router->add('GET', '/returns', 'ReturnController@index');
$router->add('GET', '/api/sale-items/{id}', 'ReturnController@getSaleItems');
$router->add('POST', '/returns/process', 'ReturnController@process');

$router->run();
