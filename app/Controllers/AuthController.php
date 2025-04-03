<?php

namespace App\Controllers;

use App\Models\userModel;

/**
 * Controlador para autenticación API con sesiones
 */
class AuthController
{
  private $userModel;

  public function __construct()
  {
    $this->userModel = new userModel();
  }

  /**
   * Endpoint para autenticar un usuario usando sesiones
   * Acepta tanto JSON como datos de formulario
   */
  public function login()
  {

    // Determinar el tipo de datos recibidos (JSON o formulario)
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    $isApiRequest = strpos($contentType, 'application/json') !== false;

    // Procesar según el tipo de contenido
    if ($isApiRequest) {
      // Obtener datos JSON del cuerpo de la petición
      $json = file_get_contents('php://input');
      $data = json_decode($json);

      if (!$data || !isset($data->username) || !isset($data->password)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Datos de inicio de sesión incompletos']);
        exit;
      }

      $username = $data->username;
      $password = $data->password;
      $remember = isset($data->remember) ? $data->remember : false;
    } else {
      // Obtener datos de formulario
      if (!isset($_POST['username']) || !isset($_POST['password'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Datos de inicio de sesión incompletos']);
        exit;
      }

      $username = $_POST['username'];
      $password = $_POST['password'];
      $remember = isset($_POST['recordar']) ? true : false;
    }

    // Autenticar usuario usando el modelo
    $usuario = $this->userModel->autenticarUsuario($username, $password);

    if (!$usuario) {
      http_response_code(401);
      echo json_encode(['status' => 'error', 'message' => 'Credenciales inválidas']);
      exit;
    }

    $this->userModel->actualizarUltimoAcceso($usuario->usuario_id);

    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    $tiempoActual = time();

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
      'ultima_actividad' => $tiempoActual,
    ];

    // Configurar cookie si se solicitó "recordar sesión"
    if ($remember) {
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

    if ($isApiRequest) {
      echo json_encode([
        'status' => 'success',
        'usuario' => [
          'id' => $usuario->usuario_id,
          'username' => $usuario->usuario_usuario,
          'nombre' => $usuario->usuario_nombre,
          'apellido_paterno' => $usuario->usuario_apellido_paterno,
          'rol' => $usuario->usuario_rol,
          'rol_descripcion' => $usuario->rol_descripcion
        ],
        'redirect' => APP_URL . 'dashboard'
      ]);
    } else {
      // Devolver respuesta para formulario
      echo json_encode([
        'status' => 'success',
        'redirect' => APP_URL . 'dashboard'
      ]);
    }
    exit;
  }

  /**
   * Verifica si hay una cookie de "recordar sesión" válida
   * y regenera la sesión
   * 
   * @return bool Si la sesión fue restaurada exitosamente
   */
  public function checkRememberCookie()
  {

    // si no existe la variable de sesión, pero existe la cookie de recordar
    if (!isset($_SESSION[APP_SESSION_NAME]) && isset($_COOKIE[APP_SESSION_NAME])) {

      // almacenar la cookie en una variable para su uso posterior en un array asociativo
      $cookieData = json_decode(base64_decode($_COOKIE[APP_SESSION_NAME]), true);

      if (!$cookieData || !isset($cookieData['id']) || !isset($cookieData['token'])) {
        setcookie(APP_SESSION_NAME, "", time() - 1, "/");
        return false;
      }

      $usuario = $this->userModel->obtenerUsuarioPorId($cookieData['id']);

      if (!$usuario) {
        setcookie(APP_SESSION_NAME, "", time() - 1, "/");
        return false;
      }

      // Verificar si el token de la cookie coincide con el hash de la base de datos
      if ($usuario->usuario_password_hash !== $cookieData['token']) {
        setcookie(APP_SESSION_NAME, "", time() - 1, "/");
        return false;
      }

      // Regenerar sesión
      $this->userModel->actualizarUltimoAcceso($usuario->usuario_id);

      // Almacenar tiempo de ultima actividad en la sesión
      $tiempoActual = time();

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
        'ultima_actividad' => $tiempoActual,
      ];

      // Actualizar la cookie con los nuevos datos de sesión
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

      return true;
    }

    return false;
  }


  public function checkSessionTimeout()
  {

    // Verificar que exista una sesión con registro de ultima actividad ✅

    // si no existe la variable de sesión o no hay tiempo de última actividad
    if (!isset($_SESSION[APP_SESSION_NAME]) || !isset($_SESSION[APP_SESSION_NAME]['ultima_actividad'])) {
      // No hay sesión que verificar devuelve falso es decir que no ha expirado
      return false;
    }

    $currentTime = time();

    if ($currentTime - $_SESSION[APP_SESSION_NAME]['ultima_actividad'] > SESSION_EXPIRATION_TIME) {
      // true significa que ha expirado la sesión
      return true;
    }

    // Actualizar el tiempo de última actividad
    $_SESSION[APP_SESSION_NAME]['ultima_actividad'] = $currentTime;

    return false;
  }

  /**
   * Cierra la sesión del usuario
   * 
   * Versión compatible con el router que sigue soportando
   * la notificación de sesión expirada
   */
  public function logout()
  {
    // Iniciar sesión si no está iniciada
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

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
      $redirect = APP_URL . "login";
      if ($expired) {
        $redirect .= '?expired=1';
      }
      header("Location: " . $redirect);
      exit;
    }
  }
}
