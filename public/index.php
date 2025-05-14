<?php

require_once '../vendor/autoload.php';
require_once '../config/env.php';
require_once '../config/init.php';

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

Auth::init();

$request = new Request();
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
    if ($isApiRequest) {
      $response = Response::json([
        'status' => 'error',
        'message' => 'Ruta no encontrada'
      ], 404);
    } else {
      // Para rutas web, verificar autenticación antes de redirigir
      if (Auth::check()) {
        $response = Response::redirect(APP_URL . 'error/404');
      } else {
        $response = Response::redirect(APP_URL . 'login');
      }
    }
  } elseif ($code === 403) {
    if ($isApiRequest) {
      $response = Response::json([
        'status' => 'error',
        'message' => 'Acceso denegado'
      ], 403);
    } else {
      $response = Response::redirect(APP_URL . 'error/403');
    }
  } elseif ($code === 401) {
    if ($isApiRequest) {
      $response = Response::json([
        'status' => 'error',
        'message' => 'No autenticado'
      ], 401);
    } else {
      $response = Response::redirect(APP_URL . 'login');
    }
  } else {
    error_log("Error en index.php: " . $e->getMessage());

    if ($isApiRequest) {
      $response = Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    } else {
      $response = Response::redirect(APP_URL . 'error/500');
    }
  }

  $response->send();
}
