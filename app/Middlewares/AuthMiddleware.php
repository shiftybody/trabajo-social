<?php

/**
 * Middleware de Autenticación
 * 
 * Verifica si el usuario está autenticado y gestiona el tiempo de inactividad
 */

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Controllers\AuthController;

class AuthMiddleware
{
  /**
   * Procesa la petición
   * 
   * @param Request $request Petición a procesar
   * @param callable $next Siguiente función en la cadena
   * @return mixed Respuesta
   */
  public function handle(Request $request, callable $next)
  {
    // Iniciar sesión si no está iniciada
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    // Verificar si la sesión ha expirado por inactividad
    $authController = new AuthController();
    $sessionExpired = $authController->checkSessionTimeout();

    // Si la sesión expiró, redirigir a login con mensaje
    if ($sessionExpired) {
      if ($request->expectsJson()) {
        return Response::json(array(
          'status' => 'error',
          'message' => 'Sesión expirada por inactividad'
        ), 401);
      }

      return Response::redirect(APP_URL . 'logout');
    }

    // TOFIX HERE ESTO NO ESTA VERIFICANDO CORRECTAMENTE

    // Verificar si hay sesión activa
    if (!isset($_SESSION[APP_SESSION_NAME]) || empty($_SESSION[APP_SESSION_NAME]['id'])) {
      // Verificar si hay cookie de recordar sesión
      if (!$authController->checkRememberCookie()) {
        if ($request->expectsJson()) {
          return Response::json(array(
            'status' => 'error',
            'message' => 'No autenticado'
          ), 401);
        }
        return Response::redirect(APP_URL . 'login');
      }
    }

    // Continuar con la petición
    return $next($request);
  }
}
