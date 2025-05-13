<?php

require_once '../vendor/autoload.php';
require_once '../config/env.php';
require_once '../config/init.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$request = new App\Core\Request();

// Obtener la URI y segmentos
$uri = $request->getUri();
$segments = explode('/', trim($uri, '/'));

try {
  // Determinar si es una solicitud de API
  $isApiRequest = isset($segments[0]) && $segments[0] === 'api';

  if ($isApiRequest) {
    $uri = '/' . implode('/', array_slice($segments, 1));
    $request->setUri($uri);
    $router = require_once __DIR__ . '/../app/Routes/api.php';
  } else {
    $router = require_once '../app/Routes/web.php';
  }

  $response = $router->dispatch($request);
  $response->send();
} catch (Exception $e) {

  $code = $e->getCode() ?: 500;

  if ($code === 404) {
    // Manejar rutas no encontradas
    if (isset($segments[0]) && $segments[0] === 'api') {
      // Respuesta JSON para API
      header('Content-Type: application/json');
      http_response_code(404);
      echo json_encode(['status' => 'error', 'message' => 'Ruta no encontrada']);
    } else {
      // Redirigir a login para solicitudes web
      header('Location: ' . APP_URL . 'login');
    }
  } else {
    // Manejar otros errores
    http_response_code($code);
    echo "Error interno del servidor: " . $e->getMessage();
  }

  exit();
}
