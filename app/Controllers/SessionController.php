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
      'sessionTotalDuration' => $status['sessionTotalDuration'],
      // El cliente puede definir su propio umbral de advertencia,
      // o podemos definir uno aquí si es necesario.
      // Por ejemplo, 30 segundos antes de la expiración real.
      'warningThreshold' => 30,
      'logoutUrl' => APP_URL . 'logout', // Asegúrate que APP_URL termina sin /
      'refreshUrl' => APP_URL . 'api/session/refresh', // Asegúrate que APP_URL termina sin /
      'isRememberedSession' => $status['isRememberedSession'] // <--- AÑADIR ESTA LÍNEA
    ]);
  }

  /**
   * Refresca la actividad de la sesión.
   */
  public function refresh()
  {
    if (Auth::refreshSessionActivity()) {
      // Devolver el nuevo estado de la sesión podría ser útil para el cliente
      $newStatus = Auth::getSessionStatus();
      return Response::json([
        'success' => true,
        'message' => 'Session refreshed.',
        'timeRemaining' => $newStatus['timeRemaining']
      ]);
    }
    return Response::json(['success' => false, 'message' => 'Failed to refresh session or no active session.'], 401); // 401 si no hay sesión
  }
}
