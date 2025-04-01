<?php

namespace App\Controllers\Web;

use App\Models\userModel;

/**
 * Controlador encargado de manejar la autenticación y autorización de usuarios
 */
class LoginController
{
  private $userModel;

  public function __construct()
  {
    $this->userModel = new userModel();
  }

  /**
   * Muestra la vista de login
   */
  public function showLoginForm()
  {
    // Verificar si ya hay una sesión activa
    if (isset($_SESSION[APP_SESSION_NAME])) {
      header("Location: " . APP_URL . "dashboard");
      exit();
    }

    // Incluir la vista de login
    require_once APP_ROOT . '/app/Views/loginView.php';
  }

  /**
   * Procesa el formulario de login
   */
  public function login()
  {
    // Verificar si se recibieron los datos de login
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
      return json_encode([
        'status' => 'error',
        'message' => 'Datos de inicio de sesión incompletos'
      ]);
      exit();
    }

    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['recordar']);

    // Autenticar usuario
    $usuario = $this->userModel->autenticarUsuario($username, $password);

    if (!$usuario) {
      return json_encode([
        'status' => 'error',
        'message' => 'Credenciales inválidas'
      ]);
      exit();
    }

    // Registrar último acceso
    $this->userModel->actualizarUltimoAcceso($usuario->usuario_id);

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
      'token' => $this->generateToken($usuario)
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

    return json_encode([
      'status' => 'success',
      'redirect' => APP_URL . 'dashboard'
    ]);
  }

  /**
   * Cierra la sesión del usuario
   */
  public function logout()
  {
    // Destruir sesión
    if (isset($_SESSION[APP_SESSION_NAME])) {
      unset($_SESSION[APP_SESSION_NAME]);
    }

    // Eliminar cookie de "recordar sesión" si existe
    if (isset($_COOKIE[APP_SESSION_NAME . "_remember"])) {
      setcookie(APP_SESSION_NAME . "_remember", "", time() - 3600, "/");
    }

    // Redirigir a login
    header("Location: " . APP_URL . "login");
    exit();
  }

  /**
   * Genera un token JWT para el usuario
   */
  private function generateToken($usuario)
  {
    require_once APP_ROOT . '/app/Utils/JwtHandler.php';
    $jwtHandler = new \App\Utils\JwtHandler();

    $payload = [
      'id' => $usuario->usuario_id,
      'username' => $usuario->usuario_usuario,
      'email' => $usuario->usuario_email,
      'rol' => $usuario->usuario_rol,
      'iat' => time(),
      'exp' => time() + (60 * 60) // 1 hora de expiración
    ];

    return $jwtHandler->encode($payload);
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
        'rol_descripcion' => $usuario->rol_descripcion,
        'token' => $this->generateToken($usuario)
      ];

      return true;
    }

    return false;
  }
}
