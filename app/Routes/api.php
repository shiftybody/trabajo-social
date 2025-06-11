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

  // USERS
  $router->group(array('middleware' => 'Permission:users.manage|users.view'), function ($router) {
    $router->get('/users', 'UserController@getAllUsers');
  });

  $router->group(array('middleware' => 'Permission:users.manage|users.create|users.edit|roles.view | patients.view |patients.create' ), function ($router) {
    $router->get('/roles', 'RoleController@getAllRoles');
    $router->post('/users', 'UserController@store');
    $router->post('/pacientes', 'PatientController@store');

  });

  $router->group(array('middleware' => 'Permission:users.manage|users.edit'), function ($router) {
    $router->get('/users/:id', 'UserController@getUserById');
    $router->post('/users/:id', 'UserController@update');
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

  $router->group(['middleware' => 'Permission:roles.edit'], function ($router) {
    $router->post('/roles/:id', 'RoleController@update');
    $router->get('/roles/:id/permissions', 'RoleController@getRolePermissions');
    $router->post('/roles/:id/permissions', 'RoleController@updateRolePermissions');
  });

  $router->group(['middleware' => 'Permission:roles.delete'], function ($router) {
    $router->delete('/roles/:id', 'RoleController@delete');
  });

  // PACIENTES
  $router->group(['middleware' => 'Permission:patients.manage|patients.view'], function ($router) {
    $router->get('/pacientes', 'PatientController@getAll');
  });

  $router->group(['middleware' => 'Permission:patients.create'], function ($router) {
    $router->post('/pacientes', 'PatientController@store');
  });

  $router->get('/pacientes/:id', 'PatientController@getById');
  $router->post('/pacientes/:id', 'PatientController@update');
  $router->delete('/pacientes/:id', 'PatientController@delete');

  $router->group(['middleware' => 'Permission:patients.create'], function ($router) {
  $router->post('/pacientes/store', 'PatientController@store');
});

  // PERMISOS (para cargar en formularios)
  $router->group(['middleware' => 'Permission:roles.edit|roles.create'], function ($router) {
    $router->get('/permissions', 'RoleController@getAllPermissions');
  });
});

return $router;
