<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('login', 'Auth::login');
$routes->get('profile', 'Auth::profile');
$routes->get('admin', 'Admin::index');
$routes->get('admin/report', 'Admin\\Report::index');
$routes->get('admin/tools/names', 'Admin\\Tools::names');
$routes->post('admin/tools/names', 'Admin\\Tools::updateNames', ['filter' => 'csrf']);
$routes->get('admin/tools/names_duplicate', 'Admin\\Tools::duplicateNames');
$routes->get('admin/person', 'Admin\\Person::index');
$routes->get('admin/person/inport', 'Admin\\Person::inport');
$routes->post('admin/person/inport', 'Admin\\Person::processInport', ['filter' => 'csrf']);
$routes->get('admin/person/join', 'Admin\\Person::joinNames');
$routes->post('admin/person/join', 'Admin\\Person::processJoin', ['filter' => 'csrf']);
$routes->get('admin/rdf/class', 'Admin\\RdfClass::index');
$routes->get('admin/rdf/class/new', 'Admin\\RdfClass::new');
$routes->post('admin/rdf/class', 'Admin\\RdfClass::create', ['filter' => 'csrf']);
$routes->get('admin/rdf/class/edit/(:num)', 'Admin\\RdfClass::edit/$1');
$routes->post('admin/rdf/class/edit/(:num)', 'Admin\\RdfClass::update/$1', ['filter' => 'csrf']);
$routes->post('admin/rdf/class/delete/(:num)', 'Admin\\RdfClass::delete/$1', ['filter' => 'csrf']);
$routes->post('login', 'Auth::authenticate', ['filter' => 'csrf']);
$routes->post('logout', 'Auth::logout', ['filter' => 'csrf']);
$routes->get('ppg', 'Ppg::index');
$routes->get('ppg/(:num)', 'Ppg::show/$1');
$routes->post('ppg/(:num)/linhas/(:num)/docentes', 'Ppg::adicionarDocente/$1/$2', ['filter' => 'csrf']);
$routes->get('person/(:num)', 'Docent::show/$1');
$routes->get('person/edit/(:num)', 'Docent::edit/$1');
$routes->post('person/edit/(:num)', 'Docent::editar/$1', ['filter' => 'csrf']);
$routes->post('person/(:num)/atualizar', 'Docent::atualizar/$1', ['filter' => 'csrf']);
$routes->post('person/(:num)/editar', 'Docent::editar/$1', ['filter' => 'csrf']);
$routes->post('person/(:num)/remissivas/(:num)/delete', 'Docent::deleteReference/$1/$2', ['filter' => 'csrf']);
