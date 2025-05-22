<?php

use App\Core\Router;
use App\Core\Response;

$router = new Router();

// Establecer vistas de error personalizadas
$router->setErrorView('404', APP_ROOT . 'app/Views/errors/404.php');
$router->setErrorView('403', APP_ROOT . 'app/Views/errors/403.php');
$router->setErrorView('401', APP_ROOT . 'app/Views/errors/401.php');
$router->setErrorView('500', APP_ROOT . 'app/Views/errors/500.php');

// Rutas públicas - CORREGIDO: usar LoginController en lugar de AuthController
$router->get('/login', function () {
  try {
    // Si ya hay sesión activa, redirigir al home
    if (isset($_SESSION[APP_SESSION_NAME]) && !empty($_SESSION[APP_SESSION_NAME]['id'])) {
      return Response::redirect(APP_URL . 'home');
    }

    ob_start();
    $loginViewPath = APP_ROOT . 'app/Views/login/index.php';

    if (!file_exists($loginViewPath)) {
      throw new Exception("Vista de login no encontrada: " . $loginViewPath);
    }

    include $loginViewPath;
    $content = ob_get_clean();

    if (empty($content)) {
      throw new Exception("La vista de login generó contenido vacío");
    }

    return Response::html($content);
  } catch (Exception $e) {
    return Response::html("<h1>Error al cargar la página de login</h1><p>{$e->getMessage()}</p>");
  }
});

// CORREGIDO: usar LoginController
$router->post('/login', 'LoginController@login');
$router->get('/logout', 'LoginController@logout');

// Rutas de error
$router->get('/error/404', function () {
  http_response_code(404);
  ob_start();
  include APP_ROOT . 'app/Views/errors/404.php';
  $content = ob_get_clean();
  return Response::html($content, 404);
});

$router->get('/error/403', function () {
  http_response_code(403);
  ob_start();
  include APP_ROOT . 'app/Views/errors/403.php';
  $content = ob_get_clean();
  return Response::html($content, 403);
});

$router->get('/error/401', function () {
  http_response_code(401);
  ob_start();
  include APP_ROOT . 'app/Views/errors/401.php';
  $content = ob_get_clean();
  return Response::html($content, 401);
});

$router->get('/error/500', function () {
  http_response_code(500);
  ob_start();
  include APP_ROOT . 'app/Views/errors/500.php';
  $content = ob_get_clean();
  return Response::html($content, 500);
});

// Rutas protegidas (requieren autenticación)
$router->group(['middleware' => 'Auth'], function ($router) {

  // home
  $router->get('/home', 'homeController@index')->name('home');

  // Usuarios (requiere permiso específico para ver el listado y editar)
  $router->group(['middleware' => 'Permission:users.view'], function ($router) {
    // 
    $router->get('/users', 'UserController@indexView')->name('users.index');
    $router->get('/users/edit/:id', 'UserController@editView')->name('users.update');
  });

  // Crear usuario (vista y acción)
  $router->group(['middleware' => 'Permission:users.create'], function ($router) {
    $router->get('/users/create', 'UserController@createView')->name('users.create');
    $router->post('/users', 'UserController@store')->name('users.store');
  });

  // Roles (requiere permiso específico)
  $router->group(['middleware' => 'Permission:roles.view'], function ($router) {
    $router->get('/roles', 'RoleController@index')->name('roles.index');
    $router->get('/roles/edit/:id', 'RoleController@edit')->name('roles.edit');
    $router->get('/roles/create', 'RoleController@create')->name('roles.create');
  });

  // Permisos (solo accesible para administradores)
  $router->group(['middleware' => 'Role:1'], function ($router) {
    $router->get('/permissions', 'PermissionController@index')->name('permissions.index');
    $router->get('/permissions/assign/:role_id', 'PermissionController@assignForm')->name('permissions.assign');
    $router->post('/permissions/assign/:role_id', 'PermissionController@assignSave')->name('permissions.assign.save');
  });

  // Perfil de usuario (accesible para todos los usuarios autenticados)
  $router->get('/profile', 'UserController@profile')->name('profile');
  $router->post('/profile/update', 'UserController@updateProfile')->name('profile.update');
  $router->post('/profile/password', 'UserController@updatePassword')->name('profile.password');
});

// Ruta por defecto (opcional)
$router->get('/', function () {
  return Response::redirect(APP_URL . 'home');
});

return $router;
