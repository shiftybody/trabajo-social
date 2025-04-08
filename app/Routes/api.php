<?php

/**
 * Configuración de rutas API
 */

use App\Core\Router;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\PermissionMiddleware;
use App\Middlewares\RoleMiddleware;

// Crear instancia del router
$router = new Router();

// Rutas API públicas
$router->post('/auth/login', 'LoginController@login');
$router->post('/session/ping', 'SessionController@ping');

// Rutas API protegidas (requieren autenticación)
$router->group(array('middleware' => 'Auth'), function ($router) {
  $router->post('/auth/logout', 'LoginController@logout');

  // Verificar estado de sesión
  $router->get('/session/status', 'SessionController@status');

  // Usuarios
  $router->group(array('middleware' => 'Permission:users.view'), function ($router) {
    $router->get('/users', 'ApiController@getAllUsers');
    $router->get('/users/:id', 'ApiController@getUserById');
  });

  $router->group(array('middleware' => 'Permission:users.create'), function ($router) {
    $router->post('/users', 'ApiController@createUser');
  });

  $router->group(array('middleware' => 'Permission:users.edit'), function ($router) {
    $router->put('/users/:id', 'ApiController@updateUser');
  });

  $router->group(array('middleware' => 'Permission:users.delete'), function ($router) {
    $router->delete('/users/:id', 'ApiController@deleteUser');
  });

  // Roles
  $router->group(array('middleware' => 'Permission:roles.view'), function ($router) {
    $router->get('/roles', 'ApiController@getAllRoles');
    $router->get('/roles/:id', 'ApiController@getRoleById');
  });

  $router->group(array('middleware' => 'Permission:roles.edit'), function ($router) {
    $router->post('/roles', 'ApiController@createRole');
    $router->put('/roles/:id', 'ApiController@updateRole');
    $router->delete('/roles/:id', 'ApiController@deleteRole');
  });

  // Permisos (solo para administradores)
  $router->group(array('middleware' => 'Role:1'), function ($router) {
    $router->get('/permissions', 'ApiController@getAllPermissions');
    $router->post('/roles/:id/permissions', 'ApiController@assignPermissions');
    $router->post('/users/:id/permissions', 'ApiController@assignUserPermissions');
  });

  // Perfil de usuario
  $router->get('/profile', 'ApiController@getProfile');
  $router->put('/profile', 'ApiController@updateProfile');
  $router->put('/profile/password', 'ApiController@updatePassword');
});

return $router;
