<?php

/**
 * Archivo principal que maneja todas las solicitudes
 * 
 * Este archivo es el punto de entrada de la aplicación.
 * Se encarga de cargar las dependencias necesarias, configurar el entorno
 * y dirigir las solicitudes a las rutas correspondientes.
 */

// Iniciar buffer de salida para evitar problemas con los headers
ob_start();

// Iniciar sesión temprano para evitar warnings
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Cargar autoloader y configuración
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/init.php';

// Importar clases necesarias
use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;

// Obtener la URI actual
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Determinar si es una solicitud de API
$isApiRequest = strpos($uri, '/api/') === 0;

try {
  // Cargar el router correspondiente (web o api)
  $router = $isApiRequest
    ? require_once __DIR__ . '/../app/Routes/api.php'
    : require_once __DIR__ . '/../app/Routes/web.php';

  // Crear un despachador con las rutas
  $dispatcher = new Dispatcher($router->getData());

  // Despachar la solicitud y obtener la respuesta
  $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $uri);

  // Mostrar la respuesta
  echo $response;
} catch (HttpRouteNotFoundException $e) {
  // Manejar rutas no encontradas (404)
  if ($isApiRequest) {
    // Para solicitudes API, devolver respuesta JSON
    header('Content-Type: application/json');
    header('HTTP/1.0 404 Not Found');
    echo json_encode([
      'success' => false,
      'message' => 'Endpoint no encontrado',
      'code' => 404
    ]);
  } else {
    // Para solicitudes web, redirigir a página de error 404
    header('Location: /404');
  }
} catch (HttpMethodNotAllowedException $e) {
  // Manejar métodos no permitidos (405)
  if ($isApiRequest) {
    // Para solicitudes API, devolver respuesta JSON
    header('Content-Type: application/json');
    header('HTTP/1.0 405 Method Not Allowed');
    echo json_encode([
      'success' => false,
      'message' => 'Método no permitido',
      'code' => 405
    ]);
  } else {
    // Para solicitudes web, mostrar mensaje de error
    header('HTTP/1.0 405 Method Not Allowed');
    echo 'Método no permitido';
  }
} catch (Exception $e) {
  // Manejar otros errores
  error_log('Error en index.php: ' . $e->getMessage());

  if ($isApiRequest) {
    // Para solicitudes API, devolver respuesta JSON
    header('Content-Type: application/json');
    header('HTTP/1.0 500 Internal Server Error');
    echo json_encode([
      'success' => false,
      'message' => 'Error interno del servidor',
      'code' => 500
    ]);
  } else {
    // Para solicitudes web, mostrar mensaje de error amigable
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Lo sentimos, ha ocurrido un error. Por favor, inténtelo de nuevo más tarde.';
  }
}

// Finalizar el buffer de salida
ob_end_flush();
