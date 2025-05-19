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
  /**
   * Usuario autenticado actual
   * @var object|null
   */
  private static $user = null;

  /**
   * Permisos del usuario cache
   * @var array|null
   */
  private static $permissions = null;

  /**
   * Modelo de usuario
   * @var userModel
   */
  private static $userModel = null;

  /**
   * Modelo de permisos
   * @var permissionModel
   */
  private static $permissionModel = null;

  /**
   * Rutas públicas que no requieren autenticación
   * @var array
   */
  private static $publicRoutes = [
    '/login',
    '/logout',
    '/errors/404',
    '/errors/403',
    '/errors/401',
    '/errors/500'
  ];

  /**
   * Inicializa el sistema de autenticación
   */
  public static function init()
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    // Asegurar que los modelos estén disponibles
    if (!self::$userModel) {
      self::$userModel = new userModel();
    }

    // Si no hay una sesión activa (self::$user aún no está cargado),
    // intentar restaurar desde la cookie.
    // self::check() aquí podría ser prematuro si loadUser aún no se ha ejecutado
    // con una sesión existente. Es mejor verificar directamente la variable de sesión.
    if (!isset($_SESSION[APP_SESSION_NAME]['id'])) {
      self::tryRestoreFromCookie();
    }

    // Cargar datos del usuario si hay una sesión (ya sea original o restaurada)
    // y manejar la expiración de la sesión.
    self::loadUser();
  }

  /**
   * Intenta restaurar la sesión desde la cookie de "recordar sesión"
   * @return bool True si la sesión fue restaurada, false en caso contrario
   */
  private static function tryRestoreFromCookie()
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

    $user = self::$userModel->obtenerUsuarioPorId($cookieData['id']);

    if (!$user) {
      setcookie(APP_SESSION_NAME, "", time() - 3600, "/");
      return false;
    }

    if ($user->usuario_password_hash !== $cookieData['token']) {
      setcookie(APP_SESSION_NAME, "", time() - 3600, "/");
      return false;
    }

    // Cookie válida, crear la sesión, indicando que SÍ es una sesión recordada
    self::createSession($user, true);
    // Opcional: Refrescar la cookie de "recordarme" para extender su duración si se desea.
    // self::createRememberCookie($user); 
    error_log("Sesión restaurada desde cookie para usuario ID: " . $user->usuario_id);
    return true;
  }

  /**
   * Carga los datos del usuario desde la sesión si existe y no ha expirado
   */
  private static function loadUser()
  {
    if (isset($_SESSION[APP_SESSION_NAME]['id'])) {
      // Verificar si la sesión ha expirado
      if (self::isExpired()) {
        // error_log("Auth::loadUser - Session expired for user ID: " . $_SESSION[APP_SESSION_NAME]['id']);
        self::logout(); // Limpia los datos de la sesión y las variables estáticas
        return; // No continuar cargando el usuario
      }

      // Si la sesión es válida y no ha expirado, cargar datos del usuario
      // Asegurar que el modelo de usuario esté instanciado
      if (!self::$userModel) {
        self::$userModel = new userModel();
      }

      $user = self::$userModel->obtenerUsuarioPorId($_SESSION[APP_SESSION_NAME]['id']);

      if ($user) {
        self::$user = $user;
        // self::$permissions = null; // Opcional: reiniciar permisos para carga diferida si es necesario
        // error_log("Auth::loadUser - User loaded from session: " . $user->usuario_usuario);

        // NO ACTUALIZAR 'ultima_actividad' AQUÍ EN CADA CARGA.
        // Esta marca de tiempo solo se actualiza en:
        // 1. Auth::createSession() (al iniciar sesión o restaurar desde cookie)
        // 2. Auth::refreshSessionActivity() (cuando el cliente lo solicita explícitamente)

      } else {
        // El ID de usuario en la sesión no se encontró en la BD, tratar como sesión inválida
        // error_log("Auth::loadUser - User ID " . $_SESSION[APP_SESSION_NAME]['id'] . " from session not found in DB.");
        self::logout();
      }
    } else {
      // No hay ID de sesión activa, self::$user permanecerá null
      // error_log("Auth::loadUser - No active session ID found.");
    }
  }

  /**
   * Verifica si la sesión ha expirado
   */
  private static function isExpired()
  {
    if (!isset($_SESSION[APP_SESSION_NAME]['ultima_actividad'])) {
      return true;
    }

    // Si la sesión es "recordada", no expira por el TIMEOUT corto de inactividad.
    // Su validez está ligada a la cookie "remember me" misma.
    if (isset($_SESSION[APP_SESSION_NAME]['is_remembered']) && $_SESSION[APP_SESSION_NAME]['is_remembered'] === true) {
      return false;
    }

    return (time() - $_SESSION[APP_SESSION_NAME]['ultima_actividad']) > SESSION_EXPIRATION_TIMOUT;
  }

  /**
   * Refresca la marca de tiempo de la última actividad de la sesión.
   * @return bool True si la actividad fue refrescada, false en caso contrario.
   */
  public static function refreshSessionActivity()
  {
    if (self::check() && isset($_SESSION[APP_SESSION_NAME]['id'])) {
      $_SESSION[APP_SESSION_NAME]['ultima_actividad'] = time();
      error_log("Session activity refreshed for user ID: " . self::id());
      return true;
    }
    error_log("Attempted to refresh session activity, but no active session or user not checked.");
    return false;
  }

  /**
   * Obtiene el estado actual de la sesión.
   * @return array Un array con el estado de la sesión.
   */
  public static function getSessionStatus()
  {
    if (!self::check() || !isset($_SESSION[APP_SESSION_NAME]['id']) || !isset($_SESSION[APP_SESSION_NAME]['ultima_actividad'])) {
      return [
        'isActive' => false,
        'timeRemaining' => 0,
        'expirationTimestamp' => 0,
        'sessionTotalDuration' => defined('SESSION_EXPIRATION_TIMOUT') ? SESSION_EXPIRATION_TIMOUT : 0,
        'isRememberedSession' => false, // Añadido
      ];
    }

    $isRemembered = isset($_SESSION[APP_SESSION_NAME]['is_remembered']) && $_SESSION[APP_SESSION_NAME]['is_remembered'] === true;
    $expirationTimestamp = $_SESSION[APP_SESSION_NAME]['ultima_actividad'] + SESSION_EXPIRATION_TIMOUT;
    $timeRemaining = $expirationTimestamp - time();

    if ($timeRemaining <= 0 && !$isRemembered) { // Solo expira si no es recordada y el tiempo se agotó
      return [
        'isActive' => false,
        'timeRemaining' => 0,
        'expirationTimestamp' => $expirationTimestamp,
        'sessionTotalDuration' => SESSION_EXPIRATION_TIMOUT,
        'isRememberedSession' => $isRemembered, // Añadido
      ];
    }

    // Si es recordada y el tiempo corto de actividad pasó, isActive sigue true, pero el cliente decidirá no mostrar modal corto.
    // timeRemaining para una sesión recordada se calculará basado en SESSION_EXPIRATION_TIMOUT para la ventana de actividad actual.
    // El cliente usará 'isRememberedSession' para la lógica del modal.
    return [
      'isActive' => true,
      'timeRemaining' => $isRemembered ? max(0, $timeRemaining) : max(0, $timeRemaining), // Asegurar que no sea negativo
      'expirationTimestamp' => $expirationTimestamp,
      'sessionTotalDuration' => SESSION_EXPIRATION_TIMOUT, // El modal de inactividad siempre se basa en este periodo corto
      'isRememberedSession' => $isRemembered, // Añadido
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

    // Obtener permisos específicos del usuario
    $userPermissions = self::$permissionModel->obtenerPermisosUsuario(self::id());
    foreach ($userPermissions as $permission) {
      // Los permisos específicos del usuario sobreescriben los del rol
      self::$permissions[$permission->permiso_slug] = (bool)$permission->concedido;
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

    $user = self::$userModel->autenticarUsuario($identifier, $password);

    if (!$user) {
      error_log("Login fallido para: " . $identifier);
      return false;
    }

    // Crear sesión, usando el valor de $remember
    self::createSession($user, $remember);

    // Crear cookie si se solicita
    if ($remember) {
      self::createRememberCookie($user);
    }

    // Actualizar último acceso
    self::$userModel->actualizarUltimoAcceso($user->usuario_id);

    error_log("Login exitoso para: " . $identifier);
    return true;
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
      'ultima_actividad' => time(),
      'is_remembered' => $isRemembered, // Guardar si la sesión es recordada 
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

  /**
   * Verifica si una ruta es pública
   */
  public static function isPublicRoute($uri)
  {
    return in_array($uri, self::$publicRoutes);
  }

  /**
   * Agrega una ruta pública
   */
  public static function addPublicRoute($route)
  {
    if (!in_array($route, self::$publicRoutes)) {
      self::$publicRoutes[] = $route;
    }
  }

  /**
   * Middleware de autenticación
   */
  public static function middleware($request, $next)
  {
    // Verificar si la ruta es pública
    if (self::isPublicRoute($request->getUri())) {
      return $next($request);
    }

    // Verificar autenticación
    if (!self::check()) {
      if ($request->expectsJson()) {
        return Response::json(['error' => 'No autenticado'], 401);
      }
      return Response::redirect(APP_URL . 'login');
    }

    return $next($request);
  }

  /**
   * Middleware de roles
   */
  public static function roleMiddleware($roles)
  {
    return function ($request, $next) use ($roles) {
      if (!self::check()) {
        if ($request->expectsJson()) {
          return Response::json(['error' => 'No autenticado'], 401);
        }
        return Response::redirect(APP_URL . 'login');
      }

      if (!self::hasRole($roles)) {
        if ($request->expectsJson()) {
          return Response::json(['error' => 'Acceso denegado'], 403);
        }
        return Response::redirect(APP_URL . 'error/403');
      }

      return $next($request);
    };
  }

  /**
   * Middleware de permisos
   */
  public static function permissionMiddleware($permission)
  {
    return function ($request, $next) use ($permission) {
      if (!self::check()) {
        if ($request->expectsJson()) {
          return Response::json(['error' => 'No autenticado'], 401);
        }
        return Response::redirect(APP_URL . 'login');
      }

      if (!self::can($permission)) {
        if ($request->expectsJson()) {
          return Response::json(['error' => 'Permiso denegado'], 403);
        }
        return Response::redirect(APP_URL . 'error/403');
      }

      return $next($request);
    };
  }
}
