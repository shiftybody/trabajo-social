<?php

namespace App\Controllers;

use App\Core\Auth; // Añadido
use App\Core\Request; // Asegúrate de que esta línea esté presente o añádela

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

    $usuario = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;

    // Si no se proporcionan usuario o contraseña, devolver un error
    if (empty($usuario) || empty($password)) {
      http_response_code(400);
      echo json_encode(array('status' => 'error', 'message' => 'Usuario y contraseña son requeridos'));
      exit;
    }

    // Autenticar usuario usando Auth::attempt
    $authStatus = Auth::attempt($usuario, $password, $remember);

    if ($authStatus === true) {
        // Login exitoso
        error_log("Login exitoso para usuario: $usuario");

        // Crear URL de redirección
        $redirectUrl = rtrim(APP_URL, '/') . '/home';
        error_log("Redirigiendo a: $redirectUrl");

        echo json_encode(array(
            'status' => 'success',
            'message' => 'Login exitoso',
            'redirect' => $redirectUrl
        ));
    } elseif ($authStatus === 'inactive') {
        error_log("Login fallido para usuario (inactivo): $usuario");
        http_response_code(401); // O podría ser 403 Forbidden si se prefiere
        echo json_encode(array('status' => 'error', 'message' => 'Cuenta deshabilitada permanentemente. Contacte al administrador.'));
    } else { // $authStatus === false (fallo de credenciales o error)
        error_log("Login fallido para usuario (credenciales/error): $usuario");
        http_response_code(401);
        echo json_encode(array('status' => 'error', 'message' => 'El usuario o contraseña son incorrectos.'));
    }
    exit;
  }

  /**
   * Cierra la sesión del usuario
   */
  public function logout(Request $request) // Modificar la firma del método
  {
    Auth::logout();

    $expired = $request->get('expired'); // Obtener el parámetro 'expired' de la URL

    // Verificar que APP_URL esté definida
    if (!defined('APP_URL')) {
      error_log('ERROR: APP_URL no está definida en logout()');
      // Determinar si es una solicitud AJAX para la respuesta de error
      $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
      if ($isAjax || ($request->isAjax() || $request->expectsJson())) {
        http_response_code(500);
        echo json_encode(array('status' => 'error', 'message' => 'Error de configuración del servidor.'));
      } else {
        echo "Error de configuración del servidor. Contacte al administrador.";
      }
      exit;
    }

    // Construir la URL de login base
    $loginUrlBase = rtrim(APP_URL, '/') . '/login';
    $loginUrl = $loginUrlBase;

    // Si la sesión expiró, añadir el parámetro correspondiente a la URL
    if ($expired === '1') {
      $loginUrl .= '?expired_session=1';
    }

    error_log("Cierre de sesión, redirigiendo a: $loginUrl");

    // Determinar si es una solicitud AJAX para la respuesta
    if ($request->isAjax() || $request->expectsJson()) {
      echo json_encode(array('status' => 'success', 'message' => 'Logout exitoso', 'redirect' => $loginUrl));
    } else {
      header('Location: ' . $loginUrl);
    }
    exit;
  }
}
