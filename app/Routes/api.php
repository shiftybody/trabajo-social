<?php

use Phroute\Phroute\RouteCollector;

/**
 * Definición de rutas API compatible con PHP 5.4 y Phroute 2.2
 * 
 * @return RouteCollector
 */

// Iniciar el router
$router = new RouteCollector();

// ====================================================================
// MIDDLEWARE FILTERS
// ====================================================================

// Middleware para configurar headers de API y CORS
$router->filter('api', function () {
    // Establecer las cabeceras CORS
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

    // Si es una solicitud OPTIONS, terminar aquí (preflight request)
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit(0);
    }

    return null;
});

// Middleware para autenticación de API
$router->filter('api-auth', function () {
    $middleware = new \App\Middlewares\ApiAuthMiddleware();
    return $middleware->handle();
});

// Filtro para rol de administrador (rol_id 1)
$router->filter('api-admin', function () {
    $middleware = new \App\Middlewares\ApiRoleMiddleware([1]);
    return $middleware->handle();
});

// ====================================================================
// RUTAS API
// ====================================================================

// Aplicar middleware api a todas las rutas
$router->group(['before' => 'api'], function ($router) {

    // Prefijo para todas las rutas API
    $router->group(['prefix' => '/api'], function ($router) {

        // ====================================================================
        // RUTAS PÚBLICAS API
        // ====================================================================

        // Autenticación
        $router->post('/auth/login', ['App\Controllers\Api\AuthController', 'login']);

        // Rutas de verificación de sesión (no requieren token)
        $router->get('/auth/ping', ['App\Controllers\Api\AuthController', 'ping']);
        $router->get('/auth/check-session', ['App\Controllers\Api\AuthController', 'checkSession']);

        // ====================================================================
        // RUTAS PROTEGIDAS API (requieren autenticación)
        // ====================================================================

        $router->group(['before' => 'api-auth'], function ($router) {

            // Verificación y logout
            $router->get('/auth/check', ['App\Controllers\Api\AuthController', 'check']);
            $router->post('/auth/logout', ['App\Controllers\Api\AuthController', 'logout']);

            // ====================================================================
            // RUTAS ADMIN API (requieren rol de administrador)
            // ====================================================================

            $router->group(['before' => 'api-admin'], function ($router) {

                // Gestión de usuarios
                $router->get('/users', ['App\Controllers\Api\UserController', 'index']);
                $router->post('/users', ['App\Controllers\Api\UserController', 'store']);
                $router->get('/users/{id}', ['App\Controllers\Api\UserController', 'show']);
                $router->put('/users/{id}', ['App\Controllers\Api\UserController', 'update']);
                $router->delete('/users/{id}', ['App\Controllers\Api\UserController', 'destroy']);
            });
        });
    });

    // Ruta para manejar solicitudes a endpoints no existentes
    $router->any('/api/{any}', function () {
        header('Content-Type: application/json');
        header('HTTP/1.0 404 Not Found');
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint no encontrado',
            'code' => 404
        ]);
        exit;
    });
});

return $router;
