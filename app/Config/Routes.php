<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('login', 'Auth::login');
$routes->get('profile', 'Auth::profile');
$routes->get('admin', 'Admin::index');
$routes->get('admin/person', 'Admin\\Person::index');
$routes->get('admin/person/inport', 'Admin\\Person::inport');
$routes->post('admin/person/inport', 'Admin\\Person::processInport', ['filter' => 'csrf']);
$routes->post('login', 'Auth::authenticate', ['filter' => 'csrf']);
$routes->post('logout', 'Auth::logout', ['filter' => 'csrf']);
$routes->get('ppg', 'Ppg::index');
$routes->get('ppg/(:num)', 'Ppg::show/$1');
$routes->post('ppg/(:num)/linhas/(:num)/docentes', 'Ppg::adicionarDocente/$1/$2', ['filter' => 'csrf']);
$routes->get('docent/(:num)', 'Docent::show/$1');
$routes->post('docent/(:num)/atualizar', 'Docent::atualizar/$1', ['filter' => 'csrf']);
$routes->post('docent/(:num)/editar', 'Docent::editar/$1', ['filter' => 'csrf']);
