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

// iTunes routes
$router->add('GET', '/search', 'iTunesController@search');
$router->add('GET', '/lookup', 'iTunesController@lookup');

// Download routes
$router->add('POST', '/download/add', 'DownloadController@add');
$router->add('GET', '/download/queue', 'DownloadController@queue');
$router->add('POST', '/download/update', 'DownloadController@update');
$router->add('DELETE', '/download/delete', 'DownloadController@delete');

// Playlist routes
$router->add('POST', '/playlists', 'PlaylistController@create');
$router->add('GET', '/playlists', 'PlaylistController@list');
$router->add('POST', '/playlists/tracks', 'PlaylistController@addTrack');
$router->add('GET', '/playlists/tracks', 'PlaylistController@getTracks');
$router->add('DELETE', '/playlists', 'PlaylistController@delete');

// Comment routes
$router->add('POST', '/comments', 'CommentController@add');
$router->add('GET', '/comments', 'CommentController@list');

// Stats and DB management
$router->add('GET', '/stats', 'DatabaseController@stats');

$router->handleRequest();
