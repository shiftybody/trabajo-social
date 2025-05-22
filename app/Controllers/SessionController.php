<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;

class SessionController
{
  /**
   * Devuelve el estado actual de la sesión.
   */
  public function status()
  {
    $status = Auth::getSessionStatus();
    return Response::json([
      'isActive' => $status['isActive'],
      'timeRemaining' => $status['timeRemaining'],
      // 'sessionTotalDuration' => $status['sessionTotalDuration'], // Eliminado
      'warningThreshold' => 30, // Umbral de advertencia (puede ser configurado por el cliente o aquí)
      'logoutUrl' => APP_URL . 'logout',
      'refreshUrl' => APP_URL . 'api/session/refresh',
      'isRememberedSession' => $status['isRememberedSession'],
    ]);
  }

  /**
   * Refresca la actividad de la sesión.
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
    return Response::json(['success' => false, 'message' => 'Failed to refresh session or no active session.'], 401);
  }
}
