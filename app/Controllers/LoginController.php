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
    $recordar =  isset($_POST['recordar']) ? true : false;

    $usuario = $this->autenticarUsuario($usuario, $password);

    if (!$usuario) {
      http_response_code(401);
      echo json_encode(['status' => 'error', 'message' => 'El usuario o contraseña son incorrectos']);
      exit;
    }

    $this->actualizarUltimoAcceso($usuario->usuario_id);
    $this->createSession($usuario);

    if ($recordar) {
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
    $_SESSION[APP_SESSION_NAME] = [
      'id' => $usuario->usuario_id,
      'username' => $usuario->usuario_usuario,
      'nombre' => $usuario->usuario_nombre,
      'apellido_paterno' => $usuario->usuario_apellido_paterno,
      'apellido_materno' => $usuario->usuario_apellido_materno,
      'email' => $usuario->usuario_email,
      'foto' => $usuario->usuario_foto,
      'rol' => $usuario->usuario_rol,
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

      // Verificar si la sesión ha expirado 
      // si la diferencia entre el tiempo actual y la última actividad es mayor al tiempo de expiración
      if ($currentTime - $_SESSION[APP_SESSION_NAME]['ultima_actividad'] > SESSION_EXPIRATION_TIMOUT) {
        // Si no debemos ignorar la cookie y hay una cookie válida, la sesión no expira
        if (!$ignoreRememberCookie && $this->validRememberCookie()) {
          return false;
        }
        return true; // La sesión ha expirado
      }
    } else {
      // Si no hay sesión, también consideramos que ha "expirado"
      return true;
    }

    return false; // La sesión no ha expirado
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

    // Verificar si el token de la cookie coincide con el hash de la base de datos
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
        // no se actualiza el último acceso porque no es un inicio de sesión 
        // $this->userModel->actualizarUltimoAcceso($usuario->usuario_id);
        $this->createSession($usuario);

        return true;
      } else {
        setcookie(APP_SESSION_NAME, "", time() - 1, "/");
        return false;
      }
    } else {
      return false; // No hay cookie o ya hay sesión activa
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

    // Detectar si es una expiración por inactividad o un cierre voluntario
    // Utilizamos $_GET en lugar de parámetros de función
    $expired = isset($_GET['expired']) && $_GET['expired'] == '1';

    // Limpiar datos de sesión
    if (isset($_SESSION[APP_SESSION_NAME])) {
      unset($_SESSION[APP_SESSION_NAME]);
      $_SESSION = array(); // Limpiar completamente el array de sesión
    }

    // Eliminar la cookie de recordatorio si existe
    if (isset($_COOKIE[APP_SESSION_NAME])) {
      setcookie(APP_SESSION_NAME, "", time() - 1, "/");
    }

    // Destruir la sesión por completo
    session_destroy();

    // Verificar si es una solicitud API
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    $isApiRequest = strpos($contentType, 'application/json') !== false;

    if ($isApiRequest) {
      $message = $expired ? 'Sesión expirada por inactividad' : 'Sesión cerrada correctamente';
      echo json_encode(['status' => 'success', 'message' => $message]);
      exit;
    } else {
      // Redireccionar a login con mensaje si es necesario
      $redirect = APP_URL . "login?expired=0";
      if ($expired) {
        $redirect .= '?expired=1';
      }
      header("Location: " . $redirect);
      exit;
    }
  }
}
