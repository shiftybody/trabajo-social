<?php

namespace App\Utils;

/**
 * Clase para validar formularios y datos de entrada
 */
class FormValidator
{
  /**
   * Patrones de validación para diferentes tipos de datos
   */
  const PATRON_CORREO = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
  const PATRON_NOMBRE_USUARIO = '/^[a-zA-Z0-9._-]{3,50}$/';
  const PATRON_NOMBRE = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,50}$/';
  const PATRON_TELEFONO = '/^[0-9]{10}$/';
  const PATRON_PASSWORD = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/';

  /**
   * Valida un correo electrónico
   * 
   * @param string $correo Correo a validar
   * @return bool True si es válido, false en caso contrario
   */
  public static function validarCorreo($correo)
  {
    return preg_match(self::PATRON_CORREO, $correo) === 1;
  }

  /**
   * Valida un nombre de usuario
   * 
   * @param string $username Nombre de usuario a validar
   * @return bool True si es válido, false en caso contrario
   */
  public static function validarNombreUsuario($username)
  {
    return preg_match(self::PATRON_NOMBRE_USUARIO, $username) === 1;
  }

  /**
   * Valida un nombre (nombre, apellido)
   * 
   * @param string $nombre Nombre a validar
   * @return bool True si es válido, false en caso contrario
   */
  public static function validarNombre($nombre)
  {
    return preg_match(self::PATRON_NOMBRE, $nombre) === 1;
  }

  /**
   * Valida un número de teléfono
   * 
   * @param string $telefono Teléfono a validar
   * @return bool True si es válido, false en caso contrario
   */
  public static function validarTelefono($telefono)
  {
    return preg_match(self::PATRON_TELEFONO, $telefono) === 1;
  }

  /**
   * Valida una contraseña
   * 
   * @param string $password Contraseña a validar
   * @return bool True si es válida, false en caso contrario
   */
  public static function validarPassword($password)
  {
    return preg_match(self::PATRON_PASSWORD, $password) === 1;
  }

  /**
   * Sanitiza un texto para evitar inyección de código
   * 
   * @param string $texto Texto a sanitizar
   * @return string Texto sanitizado
   */
  public static function sanitizarTexto($texto)
  {
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
  }

  /**
   * Valida un formulario de login
   * 
   * @param string $username Nombre de usuario o correo
   * @param string $password Contraseña
   * @return array Array con errores encontrados (vacío si no hay errores)
   */
  public static function validarLogin($username, $password)
  {
    $errores = [];

    if (empty($username)) {
      $errores[] = 'El nombre de usuario o correo es obligatorio';
    }

    if (empty($password)) {
      $errores[] = 'La contraseña es obligatoria';
    }

    // Si es un email, validar formato
    if (strpos($username, '@') !== false && !self::validarCorreo($username)) {
      $errores[] = 'El formato del correo electrónico no es válido';
    }

    return $errores;
  }
}
