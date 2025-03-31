<?php

use Phroute\Phroute\RouteCollector;

/**
 * Definición de rutas web compatible con PHP 5.4 y Phroute 2.2
 * 
 * @return RouteCollector
 */

// Iniciar el router
$router = new RouteCollector();

// ====================================================================
// MIDDLEWARE FILTERS
// ====================================================================

// Middleware para usuarios no autenticados (guest)
$router->filter('guest', function () {
    $session = new \App\Services\SessionService();
    if ($session->isAuthenticated()) {
        header('Location: /dashboard');
        exit;
    }
    return null;
});

// Middleware para usuarios autenticados
$router->filter('auth', function () {
    $session = new \App\Services\SessionService();
    if (!$session->isAuthenticated()) {
        $session->setFlash('error', 'Debe iniciar sesión para acceder a esta página');
        header('Location: /login');
        exit;
    }

    // Verificar si la sesión ha expirado por inactividad
    if (!$session->checkSession()) {
        $session->setFlash('error', 'Su sesión ha expirado por inactividad, por favor inicie sesión nuevamente');
        header('Location: /login');
        exit;
    }

    return null;
});

// Middleware para verificar rol de administrador
$router->filter('admin', function () {
    $session = new \App\Services\SessionService();
    if (!$session->isAuthenticated()) {
        $session->setFlash('error', 'Debe iniciar sesión para acceder a esta página');
        header('Location: /login');
        exit;
    }

    // Verificar si el usuario es administrador (asumiendo que rol_id 1 es admin)
    if (!$session->hasRole(1)) {
        $session->setFlash('error', 'No tiene permisos suficientes para acceder a esta página');
        header('Location: /dashboard');
        exit;
    }

    return null;
});

// ====================================================================
// RUTAS PÚBLICAS (no requieren autenticación)
// ====================================================================

// Página principal - redirige según autenticación
$router->get('/', function () {
    $session = new \App\Services\SessionService();
    if ($session->isAuthenticated()) {
        header('Location: /dashboard');
    } else {
        header('Location: /login');
    }
    exit;
});

// Rutas de autenticación (para usuarios no autenticados)
$router->group(['before' => 'guest'], function ($router) {
    $router->get('/login', ['App\Controllers\Web\AuthController', 'showLoginForm']);
    $router->post('/login', ['App\Controllers\Web\AuthController', 'login']);
});

// ====================================================================
// RUTAS PROTEGIDAS (requieren autenticación)
// ====================================================================

// Grupo para rutas que requieren autenticación
$router->group(['before' => 'auth'], function ($router) {

    // Dashboard
    $router->get('/dashboard', ['App\Controllers\Web\DashboardController', 'index']);

    // Cerrar sesión
    $router->get('/logout', ['App\Controllers\Web\AuthController', 'logout']);
});

// ====================================================================
// RUTAS DE ADMINISTRACIÓN (requieren rol de administrador)
// ====================================================================

// Grupo para rutas que requieren rol de administrador
$router->group(['before' => 'admin'], function ($router) {

    // Gestión de usuarios
    $router->get('/users', ['App\Controllers\Web\UserController', 'index']);
    $router->get('/users/create', ['App\Controllers\Web\UserController', 'create']);
    $router->post('/users', ['App\Controllers\Web\UserController', 'store']);
    $router->get('/users/{id}', ['App\Controllers\Web\UserController', 'show']);
    $router->get('/users/{id}/edit', ['App\Controllers\Web\UserController', 'edit']);
    $router->post('/users/{id}', ['App\Controllers\Web\UserController', 'update']);
    $router->post('/users/{id}/delete', ['App\Controllers\Web\UserController', 'destroy']);
});

// ====================================================================
// RUTAS DE ERROR
// ====================================================================

// Ruta para errores 404
$router->get('/404', function () {
    header('HTTP/1.0 404 Not Found');
    echo 'Página no encontrada';
    exit;
});

return $router;
