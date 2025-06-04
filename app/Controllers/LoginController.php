<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use Error;

/**
 * Controlador para autenticación API con sesiones
 * Compatible con PHP 5.6+
 */
class LoginController // Modificado: ya no extiende userModel
{

  /**
   * Vista de inicio de sesión
   */
  public function indexView()
  {

    // Mensaje de sesión expirada
    if (isset($_GET['expired_session']) && $_GET['expired_session'] == '1') {
      $status_message = 'Tu sesión ha expirado. Por favor, inicia sesión de nuevo.';
      $message_class = 'expired-session-message';
    }

    // Mensaje de cuenta deshabilitada
    if (isset($_GET['account_disabled']) && $_GET['account_disabled'] == '1') {
      $status_message = 'Tu cuenta ha sido deshabilitada. Contacta al administrador para más información.';
      $message_class = 'account-disabled-message';
    }

    ob_start();
    include APP_ROOT . 'app/Views/login/index.php';
    $content = ob_get_clean();
    return Response::html($content);
  }

  /**
   * Inicia sesión del usuario
   */
  public function login()
  {

    error_log("Intento de login recibido");

    $usuario = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;

    // Si no se proporcionan usuario o contraseña, devolver un error
    if (empty($usuario) || empty($password)) {
      return Response::json([
        'status' => 'error',
        'message' => 'Usuario y contraseña son requeridos'
      ], 400);
    }

    // Autenticar usuario usando Auth::attempt
    $authStatus = Auth::attempt($usuario, $password, $remember);

    if ($authStatus === true) {
      // Login exitoso
      error_log("Login exitoso para usuario: $usuario");

      return Response::json([
        'status' => 'success',
        'message' => 'Login exitoso',
        'redirect' => APP_URL . 'home'
      ]);
    } elseif ($authStatus === 'inactive') {
      return Response::json([
        'status' => 'error',
        'message' => 'Tu cuenta ha sido deshabilitada. Contacta al administrador para más información.'
      ], 401);
    } else {
      return Response::json([
        'status' => 'error',
        'message' => 'El usuario o contraseña son incorrectos.'
      ], 401);
    }
    exit;
  }

  /**
   * Cierra la sesión del usuario
   */
  public function logout(Request $request) // Modificar la firma del método
  {
    Auth::logout();

    $expired = $request->get('expired');

    // Construir la URL de login base
    $loginUrlBase = APP_URL . 'login';
    $loginUrl = $loginUrlBase;

    // Si la sesión expiró, añadir el parámetro correspondiente a la URL
    if ($expired === '1') {
      $loginUrl .= '?expired_session=1';
    }

    if ($request->expectsJson()) {
      return Response::json([
        'status' => 'success',
        'message' => 'Sesión cerrada exitosamente',
        'redirect' => $loginUrl
      ]);
    }
    return Response::redirect($loginUrl);

    exit;
  }
}
