<?php

namespace App\Controllers\Api;

use App\Models\userModel;
use App\Services\SessionService;

class AuthController
{
  protected $userModel;
  protected $sessionService;

  public function __construct()
  {
    $this->userModel = new userModel();
    $this->sessionService = new SessionService();
  }

  /**
   * Maneja la solicitud de login por API
   * 
   * @return string JSON con resultado de la autenticación
   */
  public function login()
  {
    // Establecer cabeceras para respuesta JSON
    header('Content-Type: application/json');

    // Obtener datos de solicitud JSON
    $requestData = json_decode(file_get_contents('php://input'), true);

    // Verificar datos recibidos
    if (!isset($requestData['usuario']) || !isset($requestData['password'])) {
      return json_encode([
        'success' => false,
        'message' => 'Datos incompletos. Se requiere usuario y password',
        'code' => 400
      ]);
    }

    $identificador = $requestData['usuario'];
    $password = $requestData['password'];

    // Intentar autenticar al usuario
    $usuario = $this->userModel->autenticarUsuario($identificador, $password);

    // Si la autenticación falló
    if (!$usuario) {
      return json_encode([
        'success' => false,
        'message' => 'Credenciales inválidas',
        'code' => 401
      ]);
    }

    // Actualizar último acceso
    $this->userModel->actualizarUltimoAcceso($usuario->usuario_id);

    // Preparar datos para el token
    $userData = [
      'id' => $usuario->usuario_id,
      'nombre' => $usuario->usuario_nombre,
      'apellido_paterno' => $usuario->usuario_apellido_paterno,
      'username' => $usuario->usuario_usuario,
      'email' => $usuario->usuario_email,
      'rol' => $usuario->usuario_rol,
      'rol_descripcion' => $usuario->rol_descripcion
    ];

    // Crear token JWT
    $tokenService = new \App\Services\TokenService();
    $token = $tokenService->createToken($userData);

    return json_encode([
      'success' => true,
      'message' => 'Autenticación exitosa',
      'user' => $userData,
      'token' => $token,
      'code' => 200
    ]);
  }

  /**
   * Verificar estado de autenticación del usuario
   * 
   * @return string JSON con estado de autenticación
   */
  public function check()
  {
    // Obtener token del encabezado Authorization
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

    // Verificar token (simplificado)
    // En un sistema real, verificarías el token contra tu base de datos y su validez
    if (empty($token)) {
      header('Content-Type: application/json');
      return json_encode([
        'authenticated' => false,
        'message' => 'Token no proporcionado',
        'code' => 401
      ]);
    }

    // Simular verificación de token exitosa
    // En un sistema real, aquí verificarías que el token existe en tu base de datos 
    // y que no ha expirado

    header('Content-Type: application/json');
    return json_encode([
      'authenticated' => true,
      'message' => 'Token válido',
      'code' => 200
    ]);
  }

  /**
   * Cierra la sesión por API (invalidando el token)
   * 
   * @return string JSON con resultado del cierre de sesión
   */
  public function logout()
  {
    // Obtener token del encabezado Authorization
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

    // Verificar token
    if (empty($token)) {
      header('Content-Type: application/json');
      return json_encode([
        'success' => false,
        'message' => 'Token no proporcionado',
        'code' => 401
      ]);
    }

    // En un sistema real, aquí invalidarías el token en tu base de datos

    header('Content-Type: application/json');
    return json_encode([
      'success' => true,
      'message' => 'Sesión cerrada correctamente',
      'code' => 200
    ]);
  }

  /**
   * Actualiza la marca de tiempo de actividad
   * 
   * @return string Respuesta JSON
   */
  public function ping()
  {
    header('Content-Type: application/json');

    $sessionService = new \App\Services\SessionService();

    if ($sessionService->isAuthenticated()) {
      // Solo actualiza la última actividad
      $_SESSION['last_activity'] = time();

      return json_encode([
        'success' => true,
        'message' => 'Actividad actualizada',
        'code' => 200
      ]);
    }

    return json_encode([
      'success' => false,
      'message' => 'No hay sesión activa',
      'code' => 401
    ]);
  }

  /**
   * Verifica si la sesión del usuario sigue siendo válida
   * 
   * @return string Respuesta JSON
   */
  public function checkSession()
  {
    header('Content-Type: application/json');

    $sessionService = new \App\Services\SessionService();

    if (!$sessionService->isAuthenticated()) {
      return json_encode([
        'valid' => false,
        'message' => 'No hay sesión activa',
        'code' => 401
      ]);
    }

    // Verificar si la sesión ha expirado
    $valid = $sessionService->checkSession();

    return json_encode([
      'valid' => $valid,
      'message' => $valid ? 'Sesión válida' : 'Sesión expirada',
      'code' => $valid ? 200 : 401
    ]);
  }
}
