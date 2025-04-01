<?php

/**
 * Middleware de Autenticación
 * 
 * Verifica si el usuario está autenticado
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

    // Verificar si hay sesión activa
    if (!isset($_SESSION[APP_SESSION_NAME]) || empty($_SESSION[APP_SESSION_NAME]['id'])) {
      // Verificar si hay cookie de recordar sesión
      $authController = new AuthController();
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

    // Usuario autenticado, continuar
    return $next($request);
  }
}
