<?php

namespace App\Controllers;

use App\Core\Auth; // Añadido

/**
 * Controlador para autenticación API con sesiones
 * Compatible con PHP 5.6+
 */
class LoginController // Modificado: ya no extiende userModel
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

    // Autenticar usuario usando Auth::attempt
    if (!Auth::attempt($usuario, $password, $remember)) {
      error_log("Login fallido para usuario: $usuario");
      http_response_code(401);
      echo json_encode(array('status' => 'error', 'message' => 'El usuario o contraseña son incorrectos'));
      exit;
    }

    // Login exitoso
    error_log("Login exitoso para usuario: $usuario");

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
   * Cierra la sesión del usuario
   */
  public function logout()
  {
    Auth::logout();

    // Verificar que APP_URL esté definida
    if (!defined('APP_URL')) {
      error_log('ERROR: APP_URL no está definida en logout()');
      if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        http_response_code(500);
        echo json_encode(array('status' => 'error', 'message' => 'Error de configuración del servidor.'));
      } else {
        echo "Error de configuración del servidor. Contacte al administrador.";
      }
      exit;
    }

    // Redirigir a la página de login
    $loginUrl = rtrim(APP_URL, '/') . '/login';
    error_log("Cierre de sesión, redirigiendo a: $loginUrl");

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
      echo json_encode(array('status' => 'success', 'message' => 'Logout exitoso', 'redirect' => $loginUrl));
    } else {
      header('Location: ' . $loginUrl);
    }
    exit;
  }
}
