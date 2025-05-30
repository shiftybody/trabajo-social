<?php

use App\Core\Router;
use App\Core\Response;

$router = new Router();


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

$router->post('/login', 'LoginController@login');
$router->get('/logout', 'LoginController@logout');

// Rutas protegidas (requieren autenticación)
$router->group(['middleware' => 'Auth'], function ($router) {

  // HOME
  $router->get('/home', 'homeController@index')->name('home');

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

  //ERRORS
  $router->get('/error/401', function () {
    http_response_code(401);
    ob_start();
    include APP_ROOT . 'app/Views/errors/401.php';
    $content = ob_get_clean();
    return Response::html($content, 401);
  });

  $router->get('/error/403', function () {
    http_response_code(403);
    ob_start();
    include APP_ROOT . 'app/Views/errors/403.php';
    $content = ob_get_clean();
    return Response::html($content, 403);
  });

  $router->get('/error/404', function () {
    http_response_code(404);
    ob_start();
    include APP_ROOT . 'app/Views/errors/404.php';
    $content = ob_get_clean();
    return Response::html($content, 404);
  });

  $router->get('/error/500', function () {
    http_response_code(500);
    ob_start();
    include APP_ROOT . 'app/Views/errors/500.php';
    $content = ob_get_clean();
    return Response::html($content, 500);
  });
});

// Ruta por defecto
$router->get('/', function () {
  return Response::redirect(APP_URL . 'home');
});

return $router;
