<?php

// Este archivo define las rutas de la aplicación web.
// Las rutas están organizadas en grupos según su funcionalidad y permisos requeridos.

use App\Core\Router;
use App\Core\Response;

$router = new Router();

// Rutas públicas
$router->get('/login', 'LoginController@indexView');

// Rutas protegidas (requieren autenticación)
$router->group(['middleware' => 'Auth'], function ($router) {

  // Ruta por defecto
  $router->get('/', function () {
    return Response::redirect(APP_URL . 'home');
  });

  $router->get('/home', 'homeController@indexView');

  // USERS
  $router->group(['middleware' => 'Permission:users.manage|users.view'], function ($router) {
    $router->get('/users', 'UserController@indexView');
  });

  $router->group(['middleware' => 'Permission:users.manage|users.edit'], function ($router) {
    $router->get('/users/edit/:id', 'UserController@editView');
  });

  $router->group(['middleware' => 'Permission:users.manage|users.create'], function ($router) {
    $router->get('/users/create', 'UserController@createView');
  });

  $router->get('/profile', 'UserController@profile');

  // ROLES
  $router->group(['middleware' => 'Permission:roles.view'], function ($router) {
    $router->get('/roles', 'RoleController@indexView');
  });


  $router->group(['middleware' => 'Permission:permissions.view'], function ($router) {
    $router->get('/roles/:id/permissions', 'RoleController@permissionsView');
  });

  // ESTUDIES
  $router->group(['middleware' => 'Permission:studies.view'], function ($router) {
    $router->get('/studies', 'StudiesController@indexView');
  });

  $router->group(['middleware' => 'Permission:patients.edit'], function ($router) {
    $router->get('/patients/edit/:id', 'PatientsController@editView');
  });

  $router->group(['middleware' => 'Permission:patients.create'], function ($router) {
    $router->get('/patients/:id/studies', 'StudiesController@indexView');
    $router->get('/patients/:id/studies/new', 'StudiesController@createView');
  });

  $router->group(['middleware' => 'Permission:patients.edit|patients.create'], function ($router) {
    $router->get('/patients/:id/studies/:study_id/edit', 'StudiesController@editView');
  });

  // CONFIGURACIÓN
  $router->group(['middleware' => 'Permission:settings.view|settings.manage'], function ($router) {
    $router->get('/settings', 'SettingController@indexView');
  });

  // ERRORS
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
