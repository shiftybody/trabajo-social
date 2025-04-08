<?php

use App\Core\Router;
use App\Core\Response;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\PermissionMiddleware;
use App\Middlewares\RoleMiddleware;

$router = new Router();
$response = new Response();

// Establecer vistas de error personalizadas
$router->setErrorView('404', APP_ROOT . 'app/Views/errors/404.php');
$router->setErrorView('403', APP_ROOT . 'app/Views/errors/403.php');
$router->setErrorView('401', APP_ROOT . 'app/Views/errors/401.php');
$router->setErrorView('500', APP_ROOT . 'app/Views/errors/500.php');

// Rutas públicas
$router->get('/login', function () {
  try {
    // Si ya hay sesión activa, redirigir al dashboard
    if (isset($_SESSION[APP_SESSION_NAME]) && !empty($_SESSION[APP_SESSION_NAME]['id'])) {
      return Response::redirect(APP_URL . 'dashboard');
    }

    ob_start();
    $loginViewPath = APP_ROOT . 'app/Views/auth/login.php';

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
    // Mostrar error en lugar de contenido vacío
    return Response::html("<h1>Error al cargar la página de login</h1><p>{$e->getMessage()}</p>");
  }
});

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
$router->group(array('middleware' => 'Auth'), function ($router) {
  // Dashboard
  $router->get('/dashboard', 'DashboardController@index')->name('dashboard');

  // Usuarios (requiere permiso específico)
  $router->group(array('middleware' => 'Permission:users.view'), function ($router) {
    $router->get('/users', 'UserController@index')->name('users.index');
    $router->get('/users/create', 'UserController@create')->name('users.create');
    $router->post('/users/store', 'UserController@store')->name('users.store');
    $router->get('/users/edit/:id', 'UserController@edit')->name('users.edit');
    $router->post('/users/update/:id', 'UserController@update')->name('users.update');
    $router->post('/users/delete/:id', 'UserController@delete')->name('users.delete');
  });

  // Roles (requiere permiso específico)
  $router->group(array('middleware' => 'Permission:roles.view'), function ($router) {
    $router->get('/roles', 'PermissionController@roles')->name('roles.index');
    $router->get('/roles/edit/:id', 'PermissionController@editRole')->name('roles.edit');
    $router->post('/roles/update/:id', 'PermissionController@updateRole')->name('roles.update');
    $router->get('/roles/create', 'PermissionController@createRole')->name('roles.create');
    $router->post('/roles/store', 'PermissionController@storeRole')->name('roles.store');
    $router->post('/roles/delete/:id', 'PermissionController@deleteRole')->name('roles.delete');
  });

  // Permisos (solo accesible para administradores)
  $router->group(array('middleware' => 'Role:1'), function ($router) {
    $router->get('/permissions', 'PermissionController@index')->name('permissions.index');
    $router->get('/permissions/assign/:role_id', 'PermissionController@assignForm')->name('permissions.assign');
    $router->post('/permissions/assign/:role_id', 'PermissionController@assignSave')->name('permissions.assign.save');
  });

  // Perfil de usuario (accesible para todos los usuarios autenticados)
  $router->get('/profile', 'UserController@profile')->name('profile');
  $router->post('/profile/update', 'UserController@updateProfile')->name('profile.update');
  $router->post('/profile/password', 'UserController@updatePassword')->name('profile.password');
});

return $router;
