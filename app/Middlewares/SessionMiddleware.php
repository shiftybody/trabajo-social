<?php

namespace App\Middlewares;

/**
 * Middleware para verificar si el usuario tiene una sesión activa
 */
class SessionMiddleware
{
  /**
   * Verifica si hay una sesión activa
   * 
   * @param bool $redirect Si es true, redirige a login si no hay sesión
   * @return bool True si hay sesión, false en caso contrario
   */
  public static function verificarSesion($redirect = true)
  {
    // Iniciar sesión si no está iniciada
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    // Verificar si existe la sesión
    if (isset($_SESSION[APP_SESSION_NAME])) {
      return true;
    }

    // Si no hay sesión activa, verificar si hay cookie de "recordar sesión"
    $authController = new \App\Controllers\Api\ApiAuthController();
    if ($authController->checkRememberCookie()) {
      return true;
    }

    // Si no hay sesión ni cookie y se requiere redirección
    if ($redirect) {
      header("Location: " . APP_URL . "login");
      exit();
    }

    return false;
  }

  /**
   * Verifica si el usuario tiene permisos para acceder a un módulo específico
   * 
   * @param array $rolesPermitidos Array de roles con permiso
   * @param bool $redirect Si es true, redirige a dashboard si no tiene permisos
   * @return bool True si tiene permisos, false en caso contrario
   */
  public static function verificarPermiso($rolesPermitidos, $redirect = true)
  {
    if (!self::verificarSesion($redirect)) {
      return false;
    }

    $rolUsuario = $_SESSION[APP_SESSION_NAME]['rol'];

    // Si roles permitidos es vacío o contiene el rol del usuario
    if (empty($rolesPermitidos) || in_array($rolUsuario, $rolesPermitidos)) {
      return true;
    }

    // Si no tiene permisos y se requiere redirección
    if ($redirect) {
      header("Location: " . APP_URL . "dashboard");
      exit();
    }

    return false;
  }
}
