<?php

namespace App\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

/**
 * Clase para manejar la generación y validación de tokens JWT
 */
class JwtHandler
{
  private $secretKey;
  private $algorithm;

  public function __construct()
  {
    $this->secretKey = TOKEN_SECRET_KEY;
    $this->algorithm = 'HS256';
  }

  /**
   * Codifica un array de datos en un token JWT
   * 
   * @param array $payload Datos a codificar
   * @return string Token JWT generado
   */
  public function encode($payload)
  {
    try {
      return JWT::encode($payload, $this->secretKey, $this->algorithm);
    } catch (Exception $e) {
      error_log("Error al codificar JWT: " . $e->getMessage());
      return null;
    }
  }

  /**
   * Decodifica un token JWT
   * 
   * @param string $token Token JWT a decodificar
   * @return object|null Payload decodificado o null si hay error
   */
  public function decode($token)
  {
    try {
      $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
      return $decoded;
    } catch (Exception $e) {
      error_log("Error al decodificar JWT: " . $e->getMessage());
      return null;
    }
  }

  /**
   * Verifica si un token JWT es válido
   * 
   * @param string $token Token JWT a verificar
   * @return bool True si es válido, false en caso contrario
   */
  public function validate($token)
  {
    return $this->decode($token) !== null;
  }

  /**
   * Extrae el token JWT del encabezado Authorization
   * 
   * @return string|null Token JWT o null si no se encuentra
   */
  public static function getBearerToken()
  {
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
      return null;
    }

    return $matches[1];
  }
}
