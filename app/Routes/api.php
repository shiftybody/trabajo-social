<?php

/**
 * Archivo de rutas de API para la aplicación
 */

use Phroute\Phroute\RouteCollector;
use App\Middlewares\JwtMiddleware;
use App\Helpers\RolHelper;

$router = new RouteCollector();

// Middleware para rutas que requieren autenticación JWT
$router->filter('jwt', function() {
    return JwtMiddleware::verificarToken() !== false;
});

// Middleware para rutas que requieren roles específicos
$router->filter('jwt-role', function($role) {
    $roles = explode(',', $role);
    $roles = array_map('intval', $roles);
    return JwtMiddleware::verificarRol($roles) !== false;
});

// Configurar los encabezados para API solo si no es una solicitud de formulario
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
$isFormRequest = strpos($contentType, 'application/x-www-form-urlencoded') !== false || 
                strpos($contentType, 'multipart/form-data') !== false;

if (!$isFormRequest) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    
    // Manejar el método OPTIONS para CORS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }
}

// Rutas públicas de la API
$router->post('/auth/login', ['App\Controllers\Api\ApiAuthController', 'login']);
$router->post('/auth/refresh', ['App\Controllers\Api\ApiAuthController', 'refresh']);
$router->get('/auth/logout', ['App\Controllers\Api\ApiAuthController', 'logout']);

// Rutas protegidas - Requieren JWT
$router->group(['before' => 'jwt'], function($router) {
    
    // Rutas para todos los usuarios autenticados
    $router->get('/me', function() {
        $payload = JwtMiddleware::verificarToken();
        echo json_encode([
            'status' => 'success',
            'data' => [
                'id' => $payload->id,
                'username' => $payload->username,
                'email' => $payload->email,
                'rol' => $payload->rol
            ]
        ]);
        exit;
    });
    
    // Rutas para administradores
    $router->group(['before' => 'jwt-role:1'], function($router) {
        // API de usuarios - Solo administradores
        $router->get('/usuarios', function() {
            $userModel = new App\Models\userModel();
            $usuarios = $userModel->obtenerTodosUsuarios();
            
            echo json_encode([
                'status' => 'success',
                'data' => $usuarios
            ]);
            exit;
        });
    });
    
    // Rutas para administradores y supervisores
    $router->group(['before' => 'jwt-role:1,2'], function($router) {
        // API de reportes - Administradores y supervisores
        $router->get('/reportes', function() {
            echo json_encode([
                'status' => 'success',
                'message' => 'API de reportes - Acceso permitido'
            ]);
            exit;
        });
    });
    
    // Rutas para administradores, supervisores y operadores
    $router->group(['before' => 'jwt-role:1,2,3'], function($router) {
        // API de donaciones
        $router->get('/donaciones', function() {
            echo json_encode([
                'status' => 'success',
                'message' => 'API de donaciones - Acceso permitido'
            ]);
            exit;
        });
        
        // API de donadores
        $router->get('/donadores', function() {
            echo json_encode([
                'status' => 'success',
                'message' => 'API de donadores - Acceso permitido'
            ]);
            exit;
        });
    });
});

return $router;