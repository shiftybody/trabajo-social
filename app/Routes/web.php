<?php

/**
 * Archivo de rutas web para la aplicación
 */

use Phroute\Phroute\RouteCollector;
use App\Middlewares\SessionMiddleware;
use App\Helpers\RolHelper;

$router = new RouteCollector();

// Middleware para rutas que requieren autenticación
$router->filter('auth', function () {
  return SessionMiddleware::verificarSesion();
});

// Middleware para rutas que requieren roles específicos
$router->filter('role', function ($role) {
  $roles = explode(',', $role);
  $roles = array_map('intval', $roles);
  return SessionMiddleware::verificarPermiso($roles);
});

// Rutas públicas
$router->get('/', function () {
  header('Location: ' . APP_URL . 'login');
  exit();
});

// Utilizamos ApiAuthController para el login, pero en la ruta web tradicional
$router->get('/login', function () {
  // Verificar si ya hay una sesión activa
  if (isset($_SESSION[APP_SESSION_NAME])) {
    header("Location: " . APP_URL . "dashboard");
    exit();
  }

  // Incluir la vista de login
  require_once APP_ROOT . '/app/Views/loginView.php';
});

$router->post('/login', ['App\Controllers\Api\ApiAuthController', 'login']);
$router->get('/logout', ['App\Controllers\Api\ApiAuthController', 'logout']);

// Rutas protegidas - Requieren autenticación
$router->group(['before' => 'auth'], function ($router) {

  // Dashboard - Accesible para todos los roles autenticados
  $router->get('/dashboard', function () {
    require_once APP_ROOT . '/app/Views/dashboard.php';
  });

  // Rutas protegidas por rol

  // Rutas para administradores
  $router->group(['before' => 'role:1'], function ($router) {
    // Rutas de administración de usuarios
    $router->get('/usuarios', function () {
      // Vista de listado de usuarios
      echo "Listado de usuarios - Solo administradores";
    });
  });

  // Rutas para administradores y supervisores
  $router->group(['before' => 'role:1,2'], function ($router) {
    // Rutas de reportes
    $router->get('/reportes', function () {
      // Vista de reportes
      echo "Reportes - Administradores y supervisores";
    });
  });

  // Rutas para administradores, supervisores y operadores
  $router->group(['before' => 'role:1,2,3'], function ($router) {
    // Rutas de donaciones
    $router->get('/donaciones', function () {
      // Vista de donaciones
      echo "Donaciones - Admins, supervisores y operadores";
    });

    // Rutas de donadores
    $router->get('/donadores', function () {
      // Vista de donadores
      echo "Donadores - Admins, supervisores y operadores";
    });
  });
});

return $router;
