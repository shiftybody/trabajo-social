<?php

/**
 * Punto de entrada de la aplicación
 */

// Iniciar sesión
session_start();

// Cargar configuración
require_once __DIR__ . '/../config/init.php';

// Autoload de clases (alternativa a Composer si es necesario)
spl_autoload_register(function ($class) {
  // Convertir namespace a ruta de archivo
  $prefix = 'App\\';
  $base_dir = __DIR__ . '/../app/';

  // Comprobar si la clase usa el prefijo
  $len = strlen($prefix);
  if (strncmp($prefix, $class, $len) !== 0) {
    return;
  }

  // Obtener la ruta relativa de la clase
  $relative_class = substr($class, $len);

  // Reemplazar namespace separators con directory separators
  // y añadir .php
  $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

  // Si el archivo existe, cargarlo
  if (file_exists($file)) {
    require $file;
  }
});

// Determinar si la petición es para la API o para la web
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

// Si el primer segmento es 'api', usar el router de API
if (isset($segments[0]) && $segments[0] === 'api') {
  // Eliminar 'api' del URI para el enrutador
  $_SERVER['REQUEST_URI'] = '/' . implode('/', array_slice($segments, 1));

  try {
    // Cargar router de API
    $router = require_once __DIR__ . '/../app/Routes/api.php';
    

    // Dispatcher para API
    $dispatcher = new Phroute\Phroute\Dispatcher($router->getData());

    // Obtener la respuesta
    $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

    // Si la respuesta no es un string JSON, convertirla
    if (!is_string($response)) {
      $response = json_encode([
        'status' => 'success',
        'data' => $response
      ]);
    }

    echo $response;
  } catch (Phroute\Phroute\Exception\HttpRouteNotFoundException $e) {
    http_response_code(404);
    echo json_encode([
      'status' => 'error',
      'message' => 'Ruta no encontrada'
    ]);
  } catch (Phroute\Phroute\Exception\HttpMethodNotAllowedException $e) {
    http_response_code(405);
    echo json_encode([
      'status' => 'error',
      'message' => 'Método no permitido'
    ]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
      'status' => 'error',
      'message' => 'Error interno del servidor',
      'error' => $e->getMessage()
    ]);
  }
} else {
  // Usar router web
  try {
    // Cargar router web
    $router = require_once __DIR__ . '/../app/Routes/web.php';

    // Dispatcher para web
    $dispatcher = new Phroute\Phroute\Dispatcher($router->getData());

    // Obtener la respuesta
    $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $uri);

    // Si la respuesta es un string, imprimirla directamente
    if (is_string($response)) {
      echo $response;
    }
  } catch (Phroute\Phroute\Exception\HttpRouteNotFoundException $e) {
    // Página no encontrada
    http_response_code(404);
    echo '<h1>Página no encontrada</h1>';
    echo '<p>La página que está buscando no existe.</p>';
    echo '<p><a href="' . APP_URL . '">Volver al inicio</a></p>';
  } catch (Phroute\Phroute\Exception\HttpMethodNotAllowedException $e) {
    // Método no permitido
    http_response_code(405);
    echo '<h1>Método no permitido</h1>';
    echo '<p>No se permite este método para la ruta especificada.</p>';
    echo '<p><a href="' . APP_URL . '">Volver al inicio</a></p>';
  } catch (Exception $e) {
    // Error interno
    http_response_code(500);
    echo '<h1>Error interno del servidor</h1>';
    echo '<p>Ha ocurrido un error al procesar su solicitud.</p>';
    echo '<p><a href="' . APP_URL . '">Volver al inicio</a></p>';

    // Registrar el error
    error_log("Error en " . $e->getFile() . " línea " . $e->getLine() . ": " . $e->getMessage());
  }
}
