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
    // Si no es una solicitud POST, devolver error 405
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
      exit;
    }

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

    // Registrar último acceso
    $this->userModel->actualizarUltimoAcceso($usuario->usuario_id);

    // Iniciar sesión si no está iniciada
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

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
      'rol_descripcion' => $usuario->rol_descripcion
    ];

    // Configurar cookie si se solicitó "recordar sesión"
    if ($remember) {
      $cookieData = [
        'id' => $usuario->usuario_id,
        'username' => $usuario->usuario_usuario,
        'token' => hash('sha256', $usuario->usuario_password_hash . TOKEN_SECRET_KEY)
      ];
      $cookieValue = base64_encode(json_encode($cookieData));
      setcookie(APP_SESSION_NAME . "_remember", $cookieValue, time() + (30 * 24 * 60 * 60), "/");
    }

    // Enviar respuesta según tipo de solicitud
    if ($isApiRequest) {
      // Devolver respuesta para API (ahora también basada en sesiones)
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
   */
  public function checkRememberCookie()
  {
    if (!isset($_SESSION[APP_SESSION_NAME]) && isset($_COOKIE[APP_SESSION_NAME . "_remember"])) {
      $cookieData = json_decode(base64_decode($_COOKIE[APP_SESSION_NAME . "_remember"]), true);

      if (!$cookieData || !isset($cookieData['id']) || !isset($cookieData['token'])) {
        setcookie(APP_SESSION_NAME . "_remember", "", time() - 3600, "/");
        return false;
      }

      $usuario = $this->userModel->obtenerUsuarioPorId($cookieData['id']);

      if (!$usuario) {
        setcookie(APP_SESSION_NAME . "_remember", "", time() - 3600, "/");
        return false;
      }

      $tokenExpected = hash('sha256', $usuario->usuario_password_hash . TOKEN_SECRET_KEY);

      if ($cookieData['token'] !== $tokenExpected) {
        setcookie(APP_SESSION_NAME . "_remember", "", time() - 3600, "/");
        return false;
      }

      // Regenerar sesión
      $this->userModel->actualizarUltimoAcceso($usuario->usuario_id);

      $_SESSION[APP_SESSION_NAME] = [
        'id' => $usuario->usuario_id,
        'username' => $usuario->usuario_usuario,
        'nombre' => $usuario->usuario_nombre,
        'apellido_paterno' => $usuario->usuario_apellido_paterno,
        'apellido_materno' => $usuario->usuario_apellido_materno,
        'email' => $usuario->usuario_email,
        'foto' => $usuario->usuario_foto,
        'rol' => $usuario->usuario_rol,
        'rol_descripcion' => $usuario->rol_descripcion
      ];

      return true;
    }

    return false;
  }

  /**
   * Cierra la sesión del usuario
   */
  public function logout()
  {
    // Iniciar sesión si no está iniciada
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    // Destruir sesión
    if (isset($_SESSION[APP_SESSION_NAME])) {
      unset($_SESSION[APP_SESSION_NAME]);
    }

    // Eliminar cookie de "recordar sesión" si existe
    if (isset($_COOKIE[APP_SESSION_NAME . "_remember"])) {
      setcookie(APP_SESSION_NAME . "_remember", "", time() - 3600, "/");
    }

    // Verificar si es una solicitud API
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    $isApiRequest = strpos($contentType, 'application/json') !== false;

    if ($isApiRequest) {
      echo json_encode(['status' => 'success', 'message' => 'Sesión cerrada correctamente']);
      exit;
    } else {
      // Redirigir a login para solicitudes web
      header("Location: " . APP_URL . "login");
      exit;
    }
  }
}
