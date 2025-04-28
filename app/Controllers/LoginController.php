<?php

namespace App\Controllers;

use App\Models\userModel;

/**
 * Controlador para autenticación API con sesiones
 */
class LoginController extends userModel
{

  /**
   * Inicia sesión del usuario
   */
  public function login()
  {

    $usuario = $_POST['username'];
    $password = $_POST['password'];
    $remember =  isset($_POST['remember']) ? true : false;

    $usuario = $this->autenticarUsuario($usuario, $password);

    if (!$usuario) {
      http_response_code(401);
      echo json_encode(['status' => 'error', 'message' => 'El usuario o contraseña son incorrectos']);
      exit;
    }

    $this->actualizarUltimoAcceso($usuario->usuario_id);
    $this->createSession($usuario);

    if ($remember) {
      $this->createCookie($usuario);
    }

    echo json_encode([
      'status' => 'success',
      'redirect' => APP_URL . 'dashboard'
    ]);

    exit;
  }


  /**
   * Crea una nueva sesión ✅
   * 
   * @param object $usuario Objeto de usuario autenticado
   *
   * @return void
   */
  public function createSession($usuario)
  {
    // Crear sesión
    // basado obtener la descripcion del rol utilizando el id del rol

    $userRoles = $this->obtenerRoles();
    $rolDescripcion = null;

    foreach ($userRoles as $rol) {
      if ($rol->rol_id == $usuario->usuario_rol) {
        $rolDescripcion = $rol->rol_descripcion;
        break;
      }
    }


    $_SESSION[APP_SESSION_NAME] = [
      'id' => $usuario->usuario_id,
      'username' => $usuario->usuario_usuario,
      'nombre' => $usuario->usuario_nombre,
      'apellido_paterno' => $usuario->usuario_apellido_paterno,
      'apellido_materno' => $usuario->usuario_apellido_materno,
      'email' => $usuario->usuario_email,
      'avatar' => $usuario->usuario_avatar,
      'rol' => $rolDescripcion,
      'rol_descripcion' => $usuario->rol_descripcion,
      'ultima_actividad' => time(),
    ];
  }


  /**
   * Crea una cookie de recordar sesión ✅
   * 
   * @param object $usuario Objeto de usuario autenticado
   * 
   * @return void
   */
  public function createCookie($usuario)
  {
    $cookieData = [
      'id' => $usuario->usuario_id,
      'username' => $usuario->usuario_usuario,
      'token' => $usuario->usuario_password_hash
    ];

    $cookieValue = base64_encode(json_encode($cookieData));

    setcookie(
      APP_SESSION_NAME,
      $cookieValue,
      time() + REMEMBER_COOKIE_DURATION,
      "/",
      "", // Dominio vacío para el mismo dominio
      isset($_SERVER['HTTPS']), // Secure solo si HTTPS
      true // HttpOnly para mayor seguridad
    );
  }


  /**
   * Verifica si la sesión ha expirado por inactividad
   * Basado en la variable de sesión 'ultima_actividad'
   * & en la constante SESSION_EXPIRATION_TIMEOUT ✅
   *
   * @param bool $ignoreRememberCookie Si se debe ignorar la cookie de recordar sesión  
   *  
   * @return bool true si la sesión ha expirado por inactividad, false si no
   *
   */
  public function checkSessionTimeout($ignoreRememberCookie = false)
  {
    // Si existe una sesión activa
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
   * Verifica si hay una cookie de "recordar sesión" valida
   * 
   * @return bool Si la sesión fue restaurada exitosamente
   */
  public function validRememberCookie()
  {

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

    // TODO:   verificar que la diferencia entre
    // time() y SESSION_EXPIRATION_TIMEOUT no sea mayor a COOKIE_EXPIRATION_TIMEOUT 
    // para validar que la cookie no haya expirado.

    return true;
  }

  /**
   * Restaura la sesión desde la cookie de recordar sesión
   * 
   * @return bool true si se restauró la sesión, false si no
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
    } else {
      return false;
    }
  }


  /**
   * Actualiza el tiempo de la última actividad del usuario
   * 
   * @return void
   * 
   */
  public function updateLastActivity()
  {
    $_SESSION[APP_SESSION_NAME]['ultima_actividad'] = time();
  }

  /**
   * Cierra la sesión del usuario
   * 
   * TODO: evaluar si es necesario el uso de la variable $expired
   * 
   * Versión compatible con el router que sigue soportando
   * la notificación de sesión expirada
   */
  public function logout()
  {

    $expired = isset($_GET['expired']) && $_GET['expired'] == '1';

    if (isset($_SESSION[APP_SESSION_NAME])) {
      unset($_SESSION[APP_SESSION_NAME]);
      $_SESSION = array();
    }

    if (isset($_COOKIE[APP_SESSION_NAME])) {
      setcookie(APP_SESSION_NAME, "", time() - 1, "/");
    }

    session_destroy();

    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    $isApiRequest = strpos($contentType, 'application/json') !== false;

    if ($isApiRequest) {
      $message = $expired ? 'Sesión expirada por inactividad' : 'Sesión cerrada correctamente';
      echo json_encode(['status' => 'success', 'message' => $message]);
      exit;
    } else {
      $redirect = APP_URL . "login?expired=0";
      if ($expired) {
        $redirect .= '?expired=1';
      }
      header("Location: " . $redirect);
      exit;
    }
  }
}
