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
    '/error/404',
    '/error/403',
    '/error/401',
    '/error/500'
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
    // AÑADIR ESTA VERIFICACIÓN E INICIALIZACIÓN PARA PERMISSIONMODEL
    if (!self::$permissionModel) {
      self::$permissionModel = new permissionModel();
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

    return (time() - $_SESSION[APP_SESSION_NAME]['ultima_actividad']) > SESSION_EXPIRATION_TIMOUT;
  }

  /**
   * Refresca la marca de tiempo de la última actividad de la sesión.
   * @return bool True si la actividad fue refrescada, false en caso contrario.
   */
  public static function refreshSessionActivity()
  {
    if (self::check() && isset($_SESSION[APP_SESSION_NAME]['id'])) {
      // Actualizar siempre la última actividad si la sesión está activa
      $_SESSION[APP_SESSION_NAME]['ultima_actividad'] = time();

      // Opcional: Log mejorado para depuración
      $sessionTypeDetail = (isset($_SESSION[APP_SESSION_NAME]['is_remembered']) && $_SESSION[APP_SESSION_NAME]['is_remembered'] === true) ? " (remembered session)" : " (standard session)";

      return true;
    }
    // Mantener este log puede ser útil para depurar por qué no se refrescó una sesión
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
        'sessionTotalDuration' => defined('SESSION_EXPIRATION_TIMOUT') ? SESSION_EXPIRATION_TIMOUT : 0,
        'isRememberedSession' => false,
      ];
    }

    $isRemembered = isset($_SESSION[APP_SESSION_NAME]['is_remembered']) && $_SESSION[APP_SESSION_NAME]['is_remembered'] === true;

    // Verificar si la cookie recordarme sigue existiendo para sesiones marcadas como recordadas
    if ($isRemembered && !isset($_COOKIE[APP_SESSION_NAME])) {
      // La cookie ha desaparecido, pero la sesión sigue marcada como recordada
      // Actualizar el estado y calcular el tiempo restante como una sesión normal
      $isRemembered = false;
      $_SESSION[APP_SESSION_NAME]['is_remembered'] = false;
      $_SESSION[APP_SESSION_NAME]['ultima_actividad'] = time(); // Reiniciar el contador
      error_log("getSessionStatus: cookie 'recordarme' no encontrada para sesión recordada. Actualizando estado.");
    }

    // Calcular el tiempo de expiración basado en la última actividad
    $expirationTimestamp = $_SESSION[APP_SESSION_NAME]['ultima_actividad'] + SESSION_EXPIRATION_TIMOUT;

    // Para sesiones recordadas, establecer un tiempo restante constante alto
    // o calcular el tiempo restante real para sesiones normales
    $timeRemaining = $isRemembered
      ? SESSION_EXPIRATION_TIMOUT // Valor constante alto para sesiones recordadas
      : max(0, $expirationTimestamp - time());

    // Una sesión recordada siempre está activa mientras exista la cookie
    // Una sesión normal está activa si tiene tiempo restante
    $isActive = $isRemembered ? true : ($timeRemaining > 0);

    return [
      'isActive' => $isActive,
      'timeRemaining' => $timeRemaining,
      'expirationTimestamp' => $expirationTimestamp,
      'sessionTotalDuration' => SESSION_EXPIRATION_TIMOUT,
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
    // transform object to json
    // error_log(json_encode($authResult)); // Modificado para loguear el array completo

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
      // Manejo de un formato de respuesta inesperado (si $authResult no es un array con 'status')
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
