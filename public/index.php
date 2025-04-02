<?php

/**
 * Punto de entrada de la aplicación
 * 
 * Este archivo maneja todas las solicitudes entrantes y las dirige al enrutador apropiado
 */

// Iniciar sesión
session_start();

require_once '../vendor/autoload.php';
require_once '../config/env.php'; // Cargar variables de entorno

// Crear instancia de Request
$request = new App\Core\Request();

// Obtener URI de la solicitud y dividir en segmentos
$uri = $request->getUri();
$segments = explode('/', trim($uri, '/'));

$authController = new App\Controllers\AuthController();
$sessionRestored = $authController->checkRememberCookie();

// Comprobar si estamos en la raíz (URL base)
if ($uri === '/') {
  // Verificar si hay sesión activa
  if (isset($_SESSION[APP_SESSION_NAME]) && !empty($_SESSION[APP_SESSION_NAME]['id'])) {
    // Redirigir al dashboard si está autenticado
    header('Location: ' . APP_URL . 'dashboard');
    exit();
  } else {
    // Redirigir a login si no está autenticado
    header('Location: ' . APP_URL . 'login');
    exit();
  }
}

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
      // Verificar si hay sesión activa para rutas web
      if (isset($_SESSION[APP_SESSION_NAME]) && !empty($_SESSION[APP_SESSION_NAME]['id'])) {
        // Usuario autenticado, mostrar página 404
        http_response_code(404);
        echo "<h1>Página no encontrada</h1>";
        echo "<p>La página que busca no existe.</p>";
      } else {
        // Usuario no autenticado, redirigir a login
        header('Location: ' . APP_URL . 'login');
      }
    }
  } else {
    // Otros errores
    http_response_code($code);
    echo "Error interno del servidor: " . $e->getMessage();
  }

  exit();
}
