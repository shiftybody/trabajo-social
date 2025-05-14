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
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    self::$userModel = new userModel();
    self::$permissionModel = new permissionModel();

    // Cargar usuario de la sesión
    self::loadUser();
  }

  /**
   * Cargar usuario desde la sesión
   */
  private static function loadUser()
  {
    if (isset($_SESSION[APP_SESSION_NAME]['id'])) {
      // Verificar si la sesión ha expirado
      if (self::isExpired()) {
        self::logout();
        return;
      }

      // Actualizar última actividad
      $_SESSION[APP_SESSION_NAME]['ultima_actividad'] = time();

      // Cargar datos completos del usuario
      self::$user = self::$userModel->obtenerUsuarioPorId($_SESSION[APP_SESSION_NAME]['id']);
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

    return (time() - $_SESSION[APP_SESSION_NAME]['ultima_actividad']) > SESSION_EXPIRATION_TIMOUT;
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

    // Crear sesión
    self::createSession($user);

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
   */
  public static function createSession($user)
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

    session_destroy();
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
