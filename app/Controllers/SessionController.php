<?php

namespace App\Controllers;

use App\Core\Response;

/**
 * Controlador para gestionar las operaciones relacionadas con la sesión
 */
class SessionController
{
  /**
   * Endpoint para mantener la sesión activa (ping)
   * 
   * @return Response Respuesta con el estado
   */
  public function ping()
  {
    // Iniciar sesión si no está iniciada
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    // Verificar si hay una sesión activa
    if (!isset($_SESSION[APP_SESSION_NAME]) || empty($_SESSION[APP_SESSION_NAME]['id'])) {
      return Response::json([
        'status' => 'error',
        'message' => 'No hay sesión activa'
      ], 401);
    }

    // Actualizar el tiempo de último acceso
    $_SESSION[APP_SESSION_NAME]['last_activity'] = time();

    // Responder éxito
    return Response::json([
      'status' => 'success',
      'message' => 'Sesión renovada'
    ]);
  }

  /**
   * Obtiene información sobre el estado actual de la sesión
   * 
   * @return Response Respuesta con información de la sesión
   */
  public function status()
  {
    // Iniciar sesión si no está iniciada
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    // Verificar si hay una sesión activa
    if (!isset($_SESSION[APP_SESSION_NAME]) || empty($_SESSION[APP_SESSION_NAME]['id'])) {
      // Verificar si hay cookie de recordatorio
      $remember = false;
      $cookieName = APP_SESSION_NAME . "_remember";

      if (isset($_COOKIE[$cookieName])) {
        // Hay una cookie pero no sesión - probablemente se está restaurando
        $authController = new AuthController();
        $restored = $authController->checkRememberCookie();

        // Si se restauró la sesión, ya hay un remember activo
        $remember = $restored;
      }

      if ($remember) {
        return Response::json([
          'status' => 'success',
          'session' => [
            'active' => true,
            'remember' => true
          ]
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'No hay sesión activa'
        ], 401);
      }
    }

    // Calcular tiempo restante antes de expiración
    $tiempoTranscurrido = time() - $_SESSION[APP_SESSION_NAME]['last_activity'];
    $tiempoRestante = SESSION_INACTIVE_TIMEOUT - $tiempoTranscurrido;

    // Verificar si el usuario eligió "recordar sesión"
    $remember = isset($_SESSION[APP_SESSION_NAME]['remember']) && $_SESSION[APP_SESSION_NAME]['remember'];

    // También verificar si existe la cookie de recordatorio
    $cookieExists = isset($_COOKIE[APP_SESSION_NAME . "_remember"]);

    // Preparar respuesta
    $response = [
      'status' => 'success',
      'session' => [
        'active' => true,
        'user_id' => $_SESSION[APP_SESSION_NAME]['id'],
        'username' => $_SESSION[APP_SESSION_NAME]['username'],
        'last_activity' => $_SESSION[APP_SESSION_NAME]['last_activity'],
        'time_elapsed' => $tiempoTranscurrido,
        'time_remaining' => $tiempoRestante,
        'remember' => $remember || $cookieExists // Si cualquiera es verdadero, hay recordatorio
      ]
    ];

    return Response::json($response);
  }
}
