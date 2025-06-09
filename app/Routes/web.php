<?php

use App\Core\Router;
use App\Core\Response;

$router = new Router();

// Rutas públicas
$router->get('/login', 'LoginController@indexView');

// Rutas protegidas (requieren autenticación)
$router->group(['middleware' => 'Auth'], function ($router) {;

  // Ruta por defecto
  $router->get('/', function () {
    return Response::redirect(APP_URL . 'home');
  });

  $router->get('/home', 'homeController@indexView')->name('home');

  // USERS
  $router->group(['middleware' => 'Permission:users.manage|users.view'], function ($router) {
    $router->get('/users', 'UserController@indexView')->name('users.index');
  });

  $router->group(['middleware' => 'Permission:users.manage|users.edit'], function ($router) {
    $router->get('/users/edit/:id', 'UserController@editView')->name('users.edit');
  });

  $router->group(['middleware' => 'Permission:users.manage|users.create'], function ($router) {
    $router->get('/users/create', 'UserController@createView')->name('users.create');
  });

  $router->get('/profile', 'UserController@profile')->name('profile');


  // ROLES - Requiere permisos de gestión de roles
  $router->group(['middleware' => 'Permission:roles.view'], function ($router) {
    $router->get('/roles', 'RoleController@indexView')->name('roles.index');
  });

  $router->group(['middleware' => 'Permission:roles.create'], function ($router) {
    $router->get('/roles/create', 'RoleController@createView')->name('roles.create');
  });

  $router->group(['middleware' => 'Permission:roles.edit'], function ($router) {
    $router->get('/roles/edit/:id', 'RoleController@editView')->name('roles.edit');
    $router->get('/roles/:id/permissions', 'RoleController@permissionsView')->name('roles.permissions');
  });

  //ERRORS
  $router->get('/error/401', function () {
    http_response_code(401);
    ob_start();
    $titulo = 'Desautorizado';
    include APP_ROOT . 'app/Views/errors/401.php';
    $content = ob_get_clean();
    return Response::html($content, 401);
  });

  $router->get('/error/403', function () {
    http_response_code(403);
    ob_start();
    $titulo = 'Prohibido';
    include APP_ROOT . 'app/Views/errors/403.php';
    $content = ob_get_clean();
    return Response::html($content, 403);
  });

  $router->get('/error/404', function () {
    http_response_code(404);
    ob_start();
    $titulo = 'No encontrado';
    include APP_ROOT . 'app/Views/errors/404.php';
    $content = ob_get_clean();
    return Response::html($content, 404);
  });

  $router->get('/error/500', function () {
    http_response_code(500);
    ob_start();
    $titulo = 'Error interno del servidor';
    include APP_ROOT . 'app/Views/errors/500.php';
    $content = ob_get_clean();
    return Response::html($content, 500);
  });
});

return $router;
