<?php

namespace App\Services;

use Firebase\JWT\JWT;

class TokenService
{
  private $secretKey;
  private $algorithm;
  private $issuer;
  private $tokenTTL; // Tiempo de vida del token en segundos

  public function __construct()
  {
    // Obtener de la configuración o definir aquí
    $this->secretKey = TOKEN_SECRET; // Debería estar en un archivo de configuración
    $this->algorithm = 'HS256';
    $this->issuer = 'trabajo-social-api';
    $this->tokenTTL = 3600; // 1 hora
  }

  /**
   * Genera un nuevo token JWT
   * 
   * @param array $userData Datos del usuario a incluir en el token
   * @return string Token JWT
   */
  public function createToken($userData)
  {
    $issuedAt = time();
    $expirationTime = $issuedAt + $this->tokenTTL;

    $payload = array(
      'iat' => $issuedAt,         // Tiempo en que fue emitido
      'iss' => $this->issuer,     // Emisor
      'nbf' => $issuedAt,         // No válido antes de
      'exp' => $expirationTime,   // Tiempo de expiración
      'data' => [                 // Datos del usuario
        'id' => $userData['id'],
        'email' => $userData['email'],
        'rol' => $userData['rol']
      ]
    );

    return JWT::encode($payload, $this->secretKey, $this->algorithm);
  }

  /**
   * Verifica y decodifica un token JWT
   * 
   * @param string $token Token JWT a verificar
   * @return object|false Payload decodificado o false si es inválido
   */
  public function validateToken($token)
  {
    if (empty($token)) {
      return false;
    }

    try {
      $decoded = JWT::decode($token, $this->secretKey, array($this->algorithm));
      return $decoded;
    } catch (\Exception $e) {
      error_log('Error validando token: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Extrae datos del usuario de un token decodificado
   * 
   * @param object $decoded Token decodificado
   * @return array Datos del usuario
   */
  public function getUserFromToken($decoded)
  {
    if (!$decoded || !isset($decoded->data)) {
      return null;
    }

    return (array) $decoded->data;
  }
}
