<?php

namespace App\Middlewares;

use App\Utils\JwtHandler;

/**
 * Middleware para verificar la validez de un token JWT en peticiones API
 */
class JwtMiddleware
{
  /**
   * Verifica que la petición contenga un token JWT válido
   * 
   * @return bool|object False si el token es inválido, payload decodificado si es válido
   */
  public static function verificarToken()
  {
    $jwtHandler = new JwtHandler();
    $token = JwtHandler::getBearerToken();

    if (!$token) {
      http_response_code(401);
      echo json_encode(['status' => 'error', 'message' => 'Token no proporcionado']);
      return false;
    }

    $payload = $jwtHandler->decode($token);

    if (!$payload) {
      http_response_code(401);
      echo json_encode(['status' => 'error', 'message' => 'Token inválido o expirado']);
      return false;
    }

    return $payload;
  }

  /**
   * Verifica que el usuario tenga los roles necesarios para acceder al recurso
   * 
   * @param array $rolesPermitidos Array de roles con permiso
   * @return bool|object False si no tiene permisos, payload decodificado si tiene permisos
   */
  public static function verificarRol($rolesPermitidos = [])
  {
    $payload = self::verificarToken();

    if (!$payload) {
      return false;
    }

    if (empty($rolesPermitidos) || in_array($payload->rol, $rolesPermitidos)) {
      return $payload;
    }

    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No tiene permisos para acceder a este recurso']);
    return false;
  }
}
