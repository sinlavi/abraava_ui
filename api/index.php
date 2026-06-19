<?php
require_once __DIR__ . '/core/Router.php';

$router = new Router();

// Auth routes
$router->add('POST', '/auth/signup', 'AuthController@signup');
$router->add('POST', '/auth/login', 'AuthController@login');
$router->add('POST', '/auth/logout', 'AuthController@logout');
$router->add('GET', '/auth/status', 'AuthController@status');

// User routes
$router->add('GET', '/users', 'UserController@list');
$router->add('DELETE', '/users', 'UserController@delete');

// iTunes routes (to be implemented)
$router->add('GET', '/search', 'iTunesController@search');
$router->add('GET', '/lookup', 'iTunesController@lookup');

// Download routes (to be implemented)
$router->add('POST', '/download/add', 'DownloadController@add');
$router->add('GET', '/download/queue', 'DownloadController@queue');
$router->add('POST', '/download/update', 'DownloadController@update');
$router->add('DELETE', '/download/delete', 'DownloadController@delete');

// Stats and DB management
$router->add('GET', '/stats', 'DatabaseController@stats');
$router->add('POST', '/manage-data', 'DatabaseController@manageData');

$router->handleRequest();
