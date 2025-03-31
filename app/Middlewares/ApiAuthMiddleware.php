<?php

namespace App\Middlewares;

use App\Services\TokenService;

class ApiAuthMiddleware
{
  /**
   * Verifica que el token de autenticación sea válido
   * 
   * @return mixed
   */
  public function handle()
  {
    // Obtener token del encabezado Authorization
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

    // Verificar token
    $tokenService = new TokenService();
    $decoded = $tokenService->validateToken($token);

    if (!$decoded) {
      header('Content-Type: application/json');
      header('HTTP/1.1 401 Unauthorized');
      echo json_encode([
        'success' => false,
        'message' => 'Token inválido o expirado',
        'code' => 401
      ]);
      exit;
    }

    // Si el token es válido, obtener los datos del usuario
    $userData = $tokenService->getUserFromToken($decoded);

    // Almacenar los datos del usuario para uso en el controlador
    $_REQUEST['auth_user'] = $userData;

    return true;
  }
}
