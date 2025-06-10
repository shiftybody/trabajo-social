<?php

use App\Core\Router;

$router = new Router();

// Rutas públicas
$router->post('/login', 'LoginController@login');
// LOGOUT
$router->post('/logout', 'LoginController@logout');

// Rutas protegidas (requieren autenticación)
$router->group(array('middleware' => 'Auth'), function ($router) {

  // SESSION
  $router->post('/session/refresh', 'SessionController@refresh');
  $router->get('/session/status', 'SessionController@status');

  // NAVIGATION
  $router->group(array('middleware' => 'Permission:search.view'), function ($router) {
    $router->get('/navigation-routes', 'RoleController@getNavigationRoutes');
  });

  // USERS
  $router->group(array('middleware' => 'Permission:users.manage|users.view'), function ($router) {
    $router->get('/users', 'UserController@getAllUsers');
  });

  $router->group(array('middleware' => 'Permission:users.manage|users.create|users.view|users.edit|roles.view'), function ($router) {
    $router->get('/roles', 'RoleController@getAllRoles');
    $router->post('/users', 'UserController@store');
    $router->get('/users/:id', 'UserController@getUserById');
    $router->post('/users/:id', 'UserController@update');
  });

  $router->group(array('middleware' => 'Permission:users.manage|users.edit'), function ($router) {
    $router->post('/users/:id/reset-password', 'UserController@resetPassword');
    $router->post('/users/:id/status', 'UserController@changeStatus');
  });

  $router->group(array('middleware' => 'Permission:users.manage|users.delete'), function ($router) {
    $router->delete('/users/:id', 'UserController@delete');
  });

  $router->get('/profile', 'ApiController@getProfile');

  // ROLES
  $router->group(['middleware' => 'Permission:roles.view'], function ($router) {
    $router->get('/roles/:id', 'RoleController@getRoleById');
  });

  $router->group(['middleware' => 'Permission:roles.create'], function ($router) {
    $router->post('/roles', 'RoleController@store');
  });
  // PERMISOS
  $router->group(['middleware' => 'Permission:permissions.view'], function ($router) {
    $router->get('/roles/:id/permissions', 'RoleController@getRolePermissions');
    $router->get('/permissions', 'RoleController@getAllPermissions');
  });

  $router->group(['middleware' => 'Permission:permissions.assign'], function ($router) {
    $router->post('/roles/:id', 'RoleController@update');
    $router->post('/roles/:id/permissions', 'RoleController@updateRolePermissions');
  });

  $router->group(['middleware' => 'Permission:roles.delete'], function ($router) {
    $router->delete('/roles/:id', 'RoleController@delete');
  });
});

return $router;
