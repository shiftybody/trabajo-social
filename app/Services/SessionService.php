<?php

namespace App\Services;

/**
 * Servicio para gestionar sesiones de usuario
 * Versión mejorada para PHP 5.4
 */
class SessionService
{

  // Constantes para tiempos de expiración (en segundos)
  const SESSION_SHORT_EXPIRY = 300;  // 5 minutos
  const SESSION_LONG_EXPIRY = 2592000; // 30 días

  /**
   * Constructor
   */
  public function __construct()
  {
    // La sesión ya se inicia en index.php
  }

  /**
   * Inicia sesión para un usuario
   * 
   * @param array $userData Datos del usuario para almacenar en sesión
   * @param bool $rememberMe Si es true, la sesión tendrá larga duración
   * @return void
   */
  public function login(array $userData, $rememberMe = false)
  {
    $_SESSION['auth'] = true;
    $_SESSION['user'] = $userData;
    $_SESSION['last_activity'] = time();

    // Guardar el tipo de sesión (corta o larga)
    $_SESSION['remember_me'] = $rememberMe;

    // Si es "recordarme", establecer una cookie persistente
    if ($rememberMe) {
      // Generar un token de identificación único
      $token = bin2hex(random_bytes(32));

      // Guardar el token en la sesión
      $_SESSION['persistent_token'] = $token;

      // Establecer cookie persistente (30 días)
      $params = session_get_cookie_params();
      setcookie(
        'remember_token',
        $token,
        time() + self::SESSION_LONG_EXPIRY,
        $params["path"],
        $params["domain"],
        $params["secure"],
        true // httponly
      );

      // En un sistema real, guardarías el token en la base de datos
      // asociado al usuario para verificación adicional
    }
  }

  /**
   * Cierra la sesión del usuario
   * 
   * @return void
   */
  public function logout()
  {
    // Destruir todas las variables de sesión
    $_SESSION = array();

    // Destruir la cookie de sesión
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
      );
    }

    // Eliminar la cookie "recordarme" si existe
    setcookie('remember_token', '', time() - 42000, '/');

    // Finalmente, destruir la sesión
    session_destroy();
  }

  /**
   * Verifica si el usuario está autenticado
   * 
   * @return bool
   */
  public function isAuthenticated()
  {
    return isset($_SESSION['auth']) && $_SESSION['auth'] === true;
  }

  /**
   * Verifica y renueva la sesión si es necesario
   * 
   * @return bool true si la sesión es válida, false si expiró
   */
  public function checkSession()
  {
    if (!$this->isAuthenticated()) {
      return false;
    }

    // Determinar el tiempo de expiración según el tipo de sesión
    $maxLifetime = isset($_SESSION['remember_me']) && $_SESSION['remember_me']
      ? self::SESSION_LONG_EXPIRY
      : self::SESSION_SHORT_EXPIRY;

    // Verificar si ha pasado el tiempo de inactividad
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $maxLifetime)) {
      $this->logout();
      return false;
    }

    // Actualizar la marca de tiempo de la última actividad
    $_SESSION['last_activity'] = time();
    return true;
  }

  /**
   * Verifica si el usuario tiene un rol específico
   * 
   * @param int|array $roles ID o array de IDs de roles permitidos
   * @return bool
   */
  public function hasRole($roles)
  {
    if (!$this->isAuthenticated()) {
      return false;
    }

    // Convertir a array si es un solo rol
    if (!is_array($roles)) {
      $roles = array($roles);
    }

    return in_array($_SESSION['user']['rol'], $roles);
  }

  /**
   * Obtiene los datos del usuario en sesión
   * 
   * @return array|null Datos del usuario o null si no está autenticado
   */
  public function getUser()
  {
    return $this->isAuthenticated() ? $_SESSION['user'] : null;
  }

  /**
   * Obtiene un dato específico del usuario en sesión
   * 
   * @param string $key Clave del dato a obtener
   * @return mixed|null Valor del dato o null si no existe
   */
  public function getUserData($key)
  {
    $user = $this->getUser();
    return $user && isset($user[$key]) ? $user[$key] : null;
  }

  /**
   * Establece un mensaje flash para mostrar en la próxima request
   * 
   * @param string $type Tipo de mensaje (success, error, info, warning)
   * @param string $message Contenido del mensaje
   * @return void
   */
  public function setFlash($type, $message)
  {
    $_SESSION['flash'] = array(
      'type' => $type,
      'message' => $message
    );
  }

  /**
   * Obtiene un mensaje flash y lo elimina de la sesión
   * 
   * @return array|null Mensaje flash o null si no hay
   */
  public function getFlash()
  {
    if (isset($_SESSION['flash'])) {
      $flash = $_SESSION['flash'];
      unset($_SESSION['flash']);
      return $flash;
    }
    return null;
  }
}
