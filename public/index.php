<?php

/**
 * Punto de entrada de la aplicación
 * 
 * Este archivo maneja todas las solicitudes 
 * entrantes y las dirige al enrutador apropiado
 */

require_once '../vendor/autoload.php';
require_once '../config/env.php';
require_once '../config/session.php';


if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$request = new App\Core\Request();
$authController = new App\Controllers\AuthController();

$uri = $request->getUri();
$segments = explode('/', trim($uri, '/'));

$isSessionExpired = $authController->checkSessionTimeout();
$rememberCookie = $authController->checkRememberCookie();

try {
  // Si el primer segmento es 'api', usar el router de API
  if (isset($segments[0]) && $segments[0] === 'api') {
    // Eliminar 'api' del URI para el enrutador
    $uri = '/' . implode('/', array_slice($segments, 1));
    $request->setUri($uri);

    // Cargar router de API
    $router = require_once __DIR__ . '/../app/Routes/api.php';
  } else {
    // aqui se llama al router web para que pueda cargar las rutas web
    $router = require_once '../app/Routes/web.php';
  }

  // PASO 1: Despachar la solicitud (DENTRO DEL TRY)
  $response = $router->dispatch($request);

  // PASO 2: Enviar la respuesta al cliente (DENTRO DEL TRY)
  $response->send();
} catch (Exception $e) {

  $code = $e->getCode() ?: 500;

  if ($code === 404) {
    // Ruta no encontrada
    if (isset($segments[0]) && $segments[0] === 'api') {
      // JSON para API
      header('Content-Type: application/json');
      http_response_code(404);
      echo json_encode(['status' => 'error', 'message' => 'Ruta no encontrada']);
    } else {
      // Usuario no autenticado, redirigir a login
      header('Location: ' . APP_URL . 'login');
    }
  } else {
    // Otros errores
    http_response_code($code);
    echo "Error interno del servidor: " . $e->getMessage();
  }
  exit();
}
