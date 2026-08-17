<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('ppg', 'Ppg::index');
$routes->get('ppg/(:num)', 'Ppg::show/$1');
