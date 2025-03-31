<?php

namespace app\Helpers;

/**
 * Clase ValidationHelper
 * 
 * Proporciona métodos para validar diferentes tipos de datos
 */
class ValidationHelper
{
    /**
     * Valida un nombre (letras y espacios)
     * 
     * @param string $nombre Nombre a validar
     * @return bool True si es válido, false en caso contrario
     */
    public function validarNombre($nombre)
    {
        return preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}$/", $nombre) === 1;
    }

    /**
     * Valida un número de teléfono (10 dígitos)
     * 
     * @param string $telefono Teléfono a validar
     * @return bool True si es válido, false en caso contrario
     */
    public function validarTelefono($telefono)
    {
        return preg_match("/^[0-9]{10}$/", $telefono) === 1;
    }

    /**
     * Valida una dirección de correo electrónico
     * 
     * @param string $email Email a validar
     * @return bool True si es válido, false en caso contrario
     */
    public function validarEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida una contraseña según criterios de seguridad
     * Debe tener al menos:
     * - Una letra mayúscula
     * - Una letra minúscula
     * - Un número
     * - Un carácter especial (@#$%)
     * - Entre 8 y 20 caracteres de longitud
     * 
     * @param string $password Contraseña a validar
     * @return bool True si es válida, false en caso contrario
     */
    public function validarPassword($password)
    {
        return preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}$/", $password) === 1;
    }

    /**
     * Valida una URL
     * 
     * @param string $url URL a validar
     * @return bool True si es válida, false en caso contrario
     */
    public function validarUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Valida un número entero
     * 
     * @param mixed $numero Número a validar
     * @return bool True si es válido, false en caso contrario
     */
    public function validarEntero($numero)
    {
        return filter_var($numero, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Valida que un valor esté dentro de un array de opciones válidas
     * 
     * @param mixed $valor Valor a validar
     * @param array $opciones Opciones válidas
     * @return bool True si es válido, false en caso contrario
     */
    public function validarOpciones($valor, $opciones)
    {
        return in_array($valor, $opciones);
    }
}