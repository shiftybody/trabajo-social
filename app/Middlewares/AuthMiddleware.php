<?php

/**
 * Middleware de Autenticación
 * 
 * Verifica si el usuario está autenticado en las paginas protegidas con autenticación
 */

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Controllers\LoginController;

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

    // Verificar si la sesión ha expirado
    $authController = new LoginController();
    // Verificar si existe una sesión
    if (!isset($_SESSION[APP_SESSION_NAME])) {
      // No hay sesión - redirigir a login
      if ($request->expectsJson()) {
        return Response::json(array(
          'status' => 'error',
          'message' => 'No autenticado'
        ), 401);
      }
      return Response::redirect(APP_URL . 'logout');
    }

    // Verificar si la sesión ha expirado
    $isSessionExpired = $authController->checkSessionTimeout();

    if ($isSessionExpired) {
      // La sesión ha expirado - redirigir a logout con parámetro de expiración
      if ($request->expectsJson()) {
        return Response::json(array(
          'status' => 'error',
          'message' => 'Sesión expirada por inactividad'
        ), 401);
      }
      return Response::redirect(APP_URL . 'logout');
    }

    // Actualizar última actividad
    $authController->updateLastActivity();

    // Continuar con la petición
    return $next($request);
  }
}
