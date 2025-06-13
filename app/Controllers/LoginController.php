<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

/**
 * Controlador para manejar el inicio de sesión de usuarios.
 * Este controlador proporciona métodos para
 * - Mostrar la vista de inicio de sesión
 * - Procesar el inicio de sesión
 * - Manejar el cierre de sesión
 */
class LoginController
{

  /**
   * Muestra la vista de inicio de sesión.
   * Si el usuario ya está autenticado, redirige a la página de inicio.
   * Si la sesión ha expirado o la cuenta está deshabilitada, muestra un mensaje correspondiente.
   *
   * @return Response
   */
  public function indexView()
  {

    if (Auth::check()) {
      return Response::redirect(APP_URL . 'home');
    }

    // Preparar mensaje de sesión expirada
    if (isset($_GET['expired_session']) && $_GET['expired_session'] == '1') {
      $status_message = 'Tu sesión ha expirado. Por favor, inicia sesión de nuevo.';
      $message_class = 'expired-session-message';
    }

    //  Preparar mensaje de cuenta deshabilitada
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
   * Procesa el inicio de sesión del usuario.
   * Valida las credenciales y maneja el estado de autenticación.
   * Responde con un mensaje JSON indicando el resultado del inicio de sesión.
   *
   * @return Response 
   */
  public function login()
  {

    $usuario = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($usuario) || empty($password)) {
      return Response::json([
        'status' => 'error',
        'message' => 'Usuario y contraseña son requeridos'
      ], 400);
    }

    // Autenticar usuario usando Auth::attempt
    $authStatus = Auth::attempt($usuario, $password, $remember);

    if ($authStatus === true) {
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
   * Maneja el cierre de sesión del usuario.
   * Desautentica al usuario y redirige a la página de inicio de sesión.
   * Si la sesión expiró, añade un parámetro a la URL de redirección.
   *
   * @param Request $request
   * @return Response
   */
  public function logout(Request $request)
  {
    Auth::logout();

    $expired = $request->get('expired');
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
