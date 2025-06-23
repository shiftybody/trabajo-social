<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;

/**
 * Controlador para manejar las sesiones de usuario.
 * Este controlador proporciona métodos para:
 * - Obtener el estado de la sesión
 * - Refrescar la actividad de la sesión
 */
class SessionController
{

  /**
   * Muestra el estado actual de la sesión del usuario.
   * Incluye información sobre si la sesión está activa, el tiempo restante,
   * y URLs para cerrar sesión o refrescar la sesión.
   *
   * @return Response
   */
  public function status()
  {
    $status = Auth::getSessionStatus();
    return Response::json([
      'isActive' => $status['isActive'],
      'timeRemaining' => $status['timeRemaining'],
      'warningThreshold' => 30,
      'logoutUrl' => APP_URL . 'api/logout',
      'refreshUrl' => APP_URL . 'api/session/refresh',
      'isRememberedSession' => $status['isRememberedSession'],
    ]);
  }

  /**
   * Refresca la actividad de la sesión del usuario. 
   * y devuelve un mensaje JSON con el nuevo estado de la sesión.
   *
   * @return ResponseC
   */
  public function refresh()
  {
    if (Auth::refreshSessionActivity()) {
      $newStatus = Auth::getSessionStatus();
      return Response::json([
        'success' => true,
        'message' => 'Session refreshed.',
        'timeRemaining' => $newStatus['timeRemaining'],
        'isRememberedSession' => $newStatus['isRememberedSession'],
      ]);
    }
    // Si no se pudo refrescar la sesión o no hay una sesión activa
    return Response::json(['success' => false, 'message' => 'Failed to refresh session or no active session.'], 401);
  }
}
