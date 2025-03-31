<?php

namespace App\Middlewares;

use App\Services\SessionService;

/**
 * Middleware para verificar que el usuario NO esté autenticado
 * Útil para rutas como login, registro, etc., donde solo deben acceder usuarios no autenticados
 */
class GuestMiddleware
{
  /**
   * Ejecuta el middleware para usuarios no autenticados
   * 
   * @param callable $next Siguiente middleware o controlador
   * @return mixed
   */
  public function handle($next)
  {
    $sessionService = new SessionService();

    // Si el usuario está autenticado, redirigir a la página principal
    if ($sessionService->isAuthenticated()) {
      // Verificar si es una solicitud API
      if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
        header('Content-Type: application/json');
        header('HTTP/1.1 403 Forbidden');
        echo json_encode([
          'success' => false,
          'message' => 'Ya tiene una sesión activa',
          'code' => 403
        ]);
        exit;
      } else {
        // Para solicitudes web, redirigir al dashboard
        header('Location: /dashboard');
        exit;
      }
    }

    // Usuario no autenticado, continuar con la cadena de middleware
    return $next();
  }
}
