<?php

namespace App\Controllers;

use App\Models\userModel;

/**
 * Controlador para autenticación API con sesiones
 * Compatible con PHP 5.6+
 */
class LoginController extends userModel
{
  /**
   * Inicia sesión del usuario
   */
  public function login()
  {
    // Verificar que APP_URL esté definida
    if (!defined('APP_URL')) {
      error_log('ERROR: APP_URL no está definida en login()');
      http_response_code(500);
      echo json_encode(array('status' => 'error', 'message' => 'Error de configuración del servidor'));
      exit;
    }

    $usuario = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;

    // Validar datos de entrada
    if (empty($usuario) || empty($password)) {
      http_response_code(400);
      echo json_encode(array('status' => 'error', 'message' => 'Usuario y contraseña son requeridos'));
      exit;
    }

    // Autenticar usuario
    $usuarioAuth = $this->autenticarUsuario($usuario, $password);

    if (!$usuarioAuth) {
      error_log("Login fallido para usuario: $usuario");
      http_response_code(401);
      echo json_encode(array('status' => 'error', 'message' => 'El usuario o contraseña son incorrectos'));
      exit;
    }

    // Login exitoso
    error_log("Login exitoso para usuario: $usuario");

    $this->actualizarUltimoAcceso($usuarioAuth->usuario_id);
    $this->createSession($usuarioAuth);

    if ($remember) {
      $this->createCookie($usuarioAuth);
    }

    // Crear URL de redirección
    $redirectUrl = rtrim(APP_URL, '/') . '/dashboard';
    error_log("Redirigiendo a: $redirectUrl");

    echo json_encode(array(
      'status' => 'success',
      'message' => 'Login exitoso',
      'redirect' => $redirectUrl
    ));

    exit;
  }

  /**
   * Crea una nueva sesión
   */
  public function createSession($usuario)
  {

    $userRoles = $this->getRoles();
    $rolDescripcion = null;

    foreach ($userRoles as $rol) {
      if ($rol->rol_id == $usuario->usuario_rol) {
        $rolDescripcion = $rol->rol_descripcion;
        break;
      }
    }

    $_SESSION[APP_SESSION_NAME] = array(
      'id' => $usuario->usuario_id,
      'username' => $usuario->usuario_usuario,
      'nombre' => $usuario->usuario_nombre,
      'apellido_paterno' => $usuario->usuario_apellido_paterno,
      'apellido_materno' => $usuario->usuario_apellido_materno,
      'email' => $usuario->usuario_email,
      'avatar' => $usuario->usuario_avatar,
      'rol' => $usuario->usuario_rol,
      'rol_descripcion' => $rolDescripcion,
      'ultima_actividad' => time(),
    );

    error_log("Sesión creada para usuario ID: " . $usuario->usuario_id);
  }

  /**
   * Crea una cookie de recordar sesión
   */
  public function createCookie($usuario)
  {
    $cookieData = array(
      'id' => $usuario->usuario_id,
      'username' => $usuario->usuario_usuario,
      'token' => $usuario->usuario_password_hash
    );

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

    error_log("Cookie creada para usuario: " . $usuario->usuario_usuario);
  }

  /**
   * Verifica si la sesión ha expirado por inactividad
   */
  public function checkSessionTimeout($ignoreRememberCookie = false)
  {
    if (isset($_SESSION[APP_SESSION_NAME]) && isset($_SESSION[APP_SESSION_NAME]['ultima_actividad'])) {
      $currentTime = time();
      if ($currentTime - $_SESSION[APP_SESSION_NAME]['ultima_actividad'] > SESSION_EXPIRATION_TIMOUT) {
        if (!$ignoreRememberCookie && $this->validRememberCookie()) {
          return false;
        }
        return true;
      }
    } else {
      return true;
    }

    return false;
  }

  /**
   * Verifica si hay una cookie de "recordar sesión" válida
   */
  public function validRememberCookie()
  {
    if (!isset($_COOKIE[APP_SESSION_NAME])) {
      return false;
    }

    $cookieData = json_decode(base64_decode($_COOKIE[APP_SESSION_NAME]), true);

    if (!$cookieData || !isset($cookieData['id']) || !isset($cookieData['username']) || !isset($cookieData['token'])) {
      return false;
    }

    $usuario = $this->obtenerUsuarioPorId($cookieData['id']);

    if (!$usuario) {
      return false;
    }

    if ($usuario->usuario_password_hash !== $cookieData['token']) {
      return false;
    }

    return true;
  }

  /**
   * Restaura la sesión desde la cookie de recordar sesión
   */
  public function RestoreSessionFromCookie()
  {
    if (!isset($_SESSION[APP_SESSION_NAME]) && isset($_COOKIE[APP_SESSION_NAME])) {
      $cookieData = json_decode(base64_decode($_COOKIE[APP_SESSION_NAME]), true);

      if ($this->validRememberCookie()) {
        $usuario = $this->obtenerUsuarioPorId($cookieData['id']);
        $this->createSession($usuario);
        return true;
      } else {
        setcookie(APP_SESSION_NAME, "", time() - 1, "/");
        return false;
      }
    }

    return false;
  }

  /**
   * Actualiza el tiempo de la última actividad del usuario
   */
  public function updateLastActivity()
  {
    if (isset($_SESSION[APP_SESSION_NAME])) {
      $_SESSION[APP_SESSION_NAME]['ultima_actividad'] = time();
    }
  }

  /**
   * Cierra la sesión del usuario
   */
  public function logout()
  {
    $expired = isset($_GET['expired']) && $_GET['expired'] == '1';

    // Limpiar sesión
    if (isset($_SESSION[APP_SESSION_NAME])) {
      unset($_SESSION[APP_SESSION_NAME]);
    }

    // Limpiar cookie
    if (isset($_COOKIE[APP_SESSION_NAME])) {
      setcookie(APP_SESSION_NAME, "", time() - 1, "/");
    }

    session_destroy();

    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    $isApiRequest = strpos($contentType, 'application/json') !== false;

    if ($isApiRequest) {
      $message = $expired ? 'Sesión expirada por inactividad' : 'Sesión cerrada correctamente';
      echo json_encode(array('status' => 'success', 'message' => $message));
    } else {
      $redirect = APP_URL . "login";
      if ($expired) {
        $redirect .= '?expired=1';
      }
      header("Location: " . $redirect);
    }

    exit;
  }
}
