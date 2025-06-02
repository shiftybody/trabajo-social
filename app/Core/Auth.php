<?php

namespace App\Core;

use App\Models\userModel;
use App\Models\permissionModel;

/**
 * Clase Auth simplificada
 * 
 * Sistema de autenticación y autorización RBAC simplificado
 * siguiendo la estructura de BD: usuario -> rol -> permisos
 */
class Auth
{

  private static $user = null;
  private static $permissions = null;
  private static $userModel = null;
  private static $permissionModel = null;

  public static function init()
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    if (!self::$userModel) {
      self::$userModel = new userModel();
    }

    if (!self::$permissionModel) {
      self::$permissionModel = new permissionModel();
    }

    if (!isset($_SESSION[APP_SESSION_NAME]['id'])) {
      self::restoreSessionFromCookie();
    }

    self::loadUser();
  }

  private static function restoreSessionFromCookie()
  {
    if (!isset($_COOKIE[APP_SESSION_NAME])) {
      return false;
    }

    $cookieValue = $_COOKIE[APP_SESSION_NAME];
    if (empty($cookieValue)) {
      setcookie(APP_SESSION_NAME, "", time() - 3600, "/");
      return false;
    }

    $cookieData = json_decode(base64_decode($cookieValue), true);

    if (!$cookieData || !isset($cookieData['id']) || !isset($cookieData['token'])) {
      setcookie(APP_SESSION_NAME, "", time() - 3600, "/");
      return false;
    }

    if (!self::$userModel) {
      self::$userModel = new userModel();
    }

    $user = self::$userModel->getUserById($cookieData['id']);

    if (!$user) {
      setcookie(APP_SESSION_NAME, "", time() - 3600, "/");
      return false;
    }

    if ($user->usuario_password_hash !== $cookieData['token']) {
      setcookie(APP_SESSION_NAME, "", time() - 3600, "/");
      return false;
    }

    self::createSession($user, true);
    return true;
  }

  private static function loadUser()
  {
    if (isset($_SESSION[APP_SESSION_NAME]['id'])) {
      if (self::isExpired()) {
        self::logout();
        return;
      }

      if (!self::$userModel) {
        self::$userModel = new userModel();
      }

      $user = self::$userModel->getUserById($_SESSION[APP_SESSION_NAME]['id']);

      if ($user) {
        self::$user = $user;

        // Verificar si la sesión está marcada como recordada pero la cookie ya no existe
        if (isset($_SESSION[APP_SESSION_NAME]['is_remembered']) && $_SESSION[APP_SESSION_NAME]['is_remembered'] === true) {
          if (!isset($_COOKIE[APP_SESSION_NAME])) {
            // La cookie de recordar ha desaparecido, actualizar el estado de la sesión
            $_SESSION[APP_SESSION_NAME]['is_remembered'] = false;
            $_SESSION[APP_SESSION_NAME]['ultima_actividad'] = time(); // Reiniciar el temporizador
            error_log("Cookie 'recordarme' no encontrada para sesión recordada. Actualizando a sesión normal.");
          }
        }
      } else {
        self::logout();
      }
    }
  }


  private static function isExpired()
  {
    if (!isset($_SESSION[APP_SESSION_NAME]['ultima_actividad'])) {
      return true;
    }

    if (isset($_SESSION[APP_SESSION_NAME]['is_remembered']) && $_SESSION[APP_SESSION_NAME]['is_remembered'] === true) {

      if (isset($_COOKIE[APP_SESSION_NAME])) {
        return false;
      } else {

        $_SESSION[APP_SESSION_NAME]['is_remembered'] = false;
        $_SESSION[APP_SESSION_NAME]['ultima_actividad'] = time();
      }
    }

    return (time() - $_SESSION[APP_SESSION_NAME]['ultima_actividad']) > SESSION_EXPIRATION_TIMEOUT;
  }

  /**
   * Refresca la marca de tiempo de la última actividad de la sesión.
   * @return bool True si la actividad fue refrescada, false en caso contrario.
   */
  public static function refreshSessionActivity()
  {
    if (self::check() && isset($_SESSION[APP_SESSION_NAME]['id'])) {

      $_SESSION[APP_SESSION_NAME]['ultima_actividad'] = time();

      return true;
    }

    error_log("Attempted to refresh session activity, but no active session or user ID found in session.");

    return false;
  }

  /**
   * Obtiene el estado actual de la sesión.
   * @return array Un array con el estado de la sesión.
   */
  public static function getSessionStatus()
  {
    if (!self::check() || !isset($_SESSION[APP_SESSION_NAME]['id'])) {
      return [
        'isActive' => false,
        'timeRemaining' => 0,
        'expirationTimestamp' => 0,
        'sessionTotalDuration' => defined('SESSION_EXPIRATION_TIMEOUT') ? SESSION_EXPIRATION_TIMEOUT : 0,
        'isRememberedSession' => false,
      ];
    }

    $isRemembered = isset($_SESSION[APP_SESSION_NAME]['is_remembered']) && $_SESSION[APP_SESSION_NAME]['is_remembered'] === true;

    if ($isRemembered && !isset($_COOKIE[APP_SESSION_NAME])) {
      $isRemembered = false;
      $_SESSION[APP_SESSION_NAME]['is_remembered'] = false;
      $_SESSION[APP_SESSION_NAME]['ultima_actividad'] = time(); // Reiniciar el contador
      error_log("getSessionStatus: cookie 'recordarme' no encontrada para sesión recordada. Actualizando estado.");
    }

    $expirationTimestamp = $_SESSION[APP_SESSION_NAME]['ultima_actividad'] + SESSION_EXPIRATION_TIMEOUT;

    $timeRemaining = $isRemembered
      ? SESSION_EXPIRATION_TIMEOUT // Valor constante alto para sesiones recordadas
      : max(0, $expirationTimestamp - time());

    // Una sesión recordada siempre está activa mientras exista la cookie
    // Una sesión normal está activa si tiene tiempo restante
    $isActive = $isRemembered ? true : ($timeRemaining > 0);

    return [
      'isActive' => $isActive,
      'timeRemaining' => $timeRemaining,
      'expirationTimestamp' => $expirationTimestamp,
      'sessionTotalDuration' => SESSION_EXPIRATION_TIMEOUT,
      'isRememberedSession' => $isRemembered,
    ];
  }

  /**
   * Verifica si el usuario está autenticado
   */
  public static function check()
  {
    return self::$user !== null;
  }

  /**
   * Obtiene el usuario autenticado
   */
  public static function user()
  {
    return self::$user;
  }

  /**
   * Obtiene el ID del usuario autenticado
   */
  public static function id()
  {
    return self::$user ? self::$user->usuario_id : null;
  }

  /**
   * Obtiene el rol del usuario
   */
  public static function role()
  {
    return self::$user ? self::$user->usuario_rol : null;
  }

  /**
   * Obtiene la descripción del rol del usuario
   */
  public static function roleName()
  {
    return self::$user ? self::$user->rol_descripcion : null;
  }

  /**
   * Verifica si el usuario tiene un rol específico
   */
  public static function hasRole($roleId)
  {
    if (!self::check()) {
      return false;
    }

    if (is_array($roleId)) {
      return in_array(self::role(), $roleId);
    }

    return self::role() == $roleId;
  }

  /**
   * Verifica si el usuario tiene alguno de los roles especificados
   */
  public static function hasAnyRole(array $roles)
  {
    return self::hasRole($roles);
  }

  /**
   * Verifica si el usuario es administrador (rol 1)
   */
  public static function isAdmin()
  {
    return self::hasRole(1);
  }

  /**
   * Verifica si el usuario tiene un permiso específico
   */
  public static function can($permission)
  {
    if (!self::check()) {
      return false;
    }

    // Lazy loading de permisos
    if (self::$permissions === null) {
      self::loadPermissions();
    }

    return isset(self::$permissions[$permission]) && self::$permissions[$permission];
  }

  /**
   * Verifica si el usuario NO tiene un permiso específico
   */
  public static function cannot($permission)
  {
    return !self::can($permission);
  }

  /**
   * Cargar permisos del usuario
   */
  private static function loadPermissions()
  {
    self::$permissions = [];

    if (!self::check()) {
      return;
    }

    // Obtener permisos del rol
    $rolePermissions = self::$permissionModel->obtenerPermisosPorRol(self::role());
    foreach ($rolePermissions as $permission) {
      self::$permissions[$permission->permiso_slug] = true;
    }
  }

  /**
   * Obtiene todos los permisos del usuario
   */
  public static function permissions()
  {
    if (self::$permissions === null) {
      self::loadPermissions();
    }

    return self::$permissions;
  }

  /**
   * Intenta autenticar a un usuario
   */
  public static function attempt($identifier, $password, $remember = false)
  {
    // Asegurar que los modelos estén disponibles
    if (!self::$userModel) {
      self::$userModel = new userModel();
    }

    $authResult = self::$userModel->autenticarUsuario($identifier, $password);

    if (isset($authResult['status'])) {
      switch ($authResult['status']) {
        case 'success':
          $user = $authResult['user'];
          self::createSession($user, $remember);
          if ($remember) {
            self::createRememberCookie($user);
          }
          self::$userModel->actualizarUltimoAcceso($user->usuario_id);
          error_log("Login exitoso para: " . $identifier);
          return true; // Autenticación exitosa
        case 'inactive':
          error_log("Intento de login de usuario inactivo: " . $identifier);
          return 'inactive'; // Estado específico para usuario inactivo
        case 'failed':
          error_log("Login fallido (usuario no encontrado o contraseña incorrecta) para: " . $identifier);
          return false; // Fallo de autenticación (usuario/contraseña)
        case 'error':
          error_log("Error en Auth::attempt llamando a autenticarUsuario: " . (isset($authResult['message']) ? $authResult['message'] : 'Error desconocido'));
          return false; // Error general
        default:
          error_log("Resultado inesperado de autenticarUsuario para: " . $identifier);
          return false; // Estado desconocido
      }
    } else {
      error_log("Formato de respuesta inesperado de autenticarUsuario para: " . $identifier . " - Respuesta: " . json_encode($authResult));
      return false;
    }
  }

  /**
   * Crea una nueva sesión para el usuario
   * @param object $user El objeto del usuario
   * @param bool $isRemembered Indica si la sesión se está creando a partir de una cookie "recordarme"
   */
  public static function createSession($user, $isRemembered = false)
  {
    $_SESSION[APP_SESSION_NAME] = [
      'id' => $user->usuario_id,
      'username' => $user->usuario_usuario,
      'nombre' => $user->usuario_nombre,
      'apellido_paterno' => $user->usuario_apellido_paterno,
      'apellido_materno' => $user->usuario_apellido_materno,
      'email' => $user->usuario_email,
      'avatar' => $user->usuario_avatar,
      'rol' => $user->usuario_rol,
      'rol_descripcion' => $user->rol_descripcion,
      'estado_id' => $user->usuario_estado,
      'ultima_actividad' => time(),
      'is_remembered' => $isRemembered,
    ];

    self::$user = $user;
    self::$permissions = null; // Reset permisos para cargar los nuevos
  }

  /**
   * Crea una cookie de recordar sesión
   */
  public static function createRememberCookie($user)
  {
    $cookieData = [
      'id' => $user->usuario_id,
      'token' => $user->usuario_password_hash
    ];

    $cookieValue = base64_encode(json_encode($cookieData));

    setcookie(
      APP_SESSION_NAME,
      $cookieValue,
      time() + REMEMBER_COOKIE_DURATION,
      "/",
      "",
      isset($_SERVER['HTTPS']),
      true
    );
  }

  /**
   * Cierra la sesión del usuario
   */
  public static function logout()
  {
    // Limpiar variables estáticas
    self::$user = null;
    self::$permissions = null;

    // Limpiar sesión
    if (isset($_SESSION[APP_SESSION_NAME])) {
      unset($_SESSION[APP_SESSION_NAME]);
    }

    // Limpiar cookie
    if (isset($_COOKIE[APP_SESSION_NAME])) {
      setcookie(APP_SESSION_NAME, "", time() - 1, "/");
    }

    // Solo destruir la sesión si está activa
    if (session_status() === PHP_SESSION_ACTIVE) {
      session_destroy();
    }
  }
}
