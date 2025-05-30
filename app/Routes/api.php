<?php

/**
 * Configuración de rutas API
 */

use App\Core\Router;

$router = new Router();

// Rutas públicas
$router->post('/auth/login', 'LoginController@login');

// Rutas protegidas (requieren autenticación)
$router->group(array('middleware' => 'Auth'), function ($router) {

  // LOGOUT Y SESSION
  $router->post('/auth/logout', 'LoginController@logout');
  $router->post('/session/refresh', 'SessionController@refresh');
  $router->get('/session/status', 'SessionController@status');

  // USERS
  $router->group(array('middleware' => 'Permission:users.manage|users.view'), function ($router) {
    $router->get('/users', 'UserController@getAllUsers');
  });

  $router->group(array('middleware' => 'Permission:users.manage|users.create'), function ($router) {
    $router->post('/users', 'UserController@store');
  });

  $router->group(array('middleware' => 'Permission:users.manage|users.edit|roles.view'), function ($router) {
    $router->get('/roles', 'RoleController@getAllRoles');
    $router->get('/users/:id', 'UserController@getUserById');
  });

  $router->group(array('middleware' => 'Permission:users.manage|users.edit'), function ($router) {
    $router->post('/users/:id', 'UserController@update');
    $router->post('/users/:id/reset-password', 'UserController@resetPassword');
    $router->post('/users/:id/status', 'UserController@changeStatus');
  });

  $router->group(array('middleware' => 'Permission:users.manage|users.delete'), function ($router) {
    $router->delete('/users/:id', 'UserController@delete');
  });

  $router->get('/profile', 'ApiController@getProfile');
});

return $router;
