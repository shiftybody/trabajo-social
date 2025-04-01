<?php

namespace App\Helpers;

/**
 * Helper para gestionar roles y permisos
 */
class RolHelper
{
  // Definición de roles del sistema
  const ROL_ADMIN = 1;
  const ROL_SUPERVISOR = 2;
  const ROL_OPERADOR = 3;
  const ROL_CONSULTA = 4;

  // Mapeo de módulos a roles con permiso
  private static $permisos = [
    // Módulos del dashboard
    'dashboard' => [self::ROL_ADMIN, self::ROL_SUPERVISOR, self::ROL_OPERADOR, self::ROL_CONSULTA],
    'usuarios' => [self::ROL_ADMIN],
    'donaciones' => [self::ROL_ADMIN, self::ROL_SUPERVISOR, self::ROL_OPERADOR],
    'donadores' => [self::ROL_ADMIN, self::ROL_SUPERVISOR, self::ROL_OPERADOR],
    'reportes' => [self::ROL_ADMIN, self::ROL_SUPERVISOR, self::ROL_CONSULTA],
    'configuracion' => [self::ROL_ADMIN],

    // APIs
    'api_usuarios' => [self::ROL_ADMIN],
    'api_donaciones' => [self::ROL_ADMIN, self::ROL_SUPERVISOR, self::ROL_OPERADOR],
    'api_donadores' => [self::ROL_ADMIN, self::ROL_SUPERVISOR, self::ROL_OPERADOR],
    'api_reportes' => [self::ROL_ADMIN, self::ROL_SUPERVISOR, self::ROL_CONSULTA]
  ];

  /**
   * Verifica si un rol tiene permiso para acceder a un módulo
   * 
   * @param int $rol ID del rol
   * @param string $modulo Nombre del módulo
   * @return bool True si tiene permiso, false en caso contrario
   */
  public static function tienePermiso($rol, $modulo)
  {
    if (!isset(self::$permisos[$modulo])) {
      return false;
    }

    return in_array($rol, self::$permisos[$modulo]);
  }

  /**
   * Obtiene los módulos a los que tiene acceso un rol
   * 
   * @param int $rol ID del rol
   * @return array Array con los nombres de los módulos
   */
  public static function obtenerModulosPermitidos($rol)
  {
    $modulos = [];

    foreach (self::$permisos as $modulo => $roles) {
      if (in_array($rol, $roles)) {
        $modulos[] = $modulo;
      }
    }

    return $modulos;
  }

  /**
   * Obtiene los roles que tienen acceso a un módulo
   * 
   * @param string $modulo Nombre del módulo
   * @return array Array con los IDs de los roles
   */
  public static function obtenerRolesPermitidos($modulo)
  {
    if (!isset(self::$permisos[$modulo])) {
      return [];
    }

    return self::$permisos[$modulo];
  }

  /**
   * Obtiene la descripción de un rol por su ID
   * 
   * @param int $rol ID del rol
   * @return string Descripción del rol
   */
  public static function obtenerDescripcionRol($rol)
  {
    switch ($rol) {
      case self::ROL_ADMIN:
        return 'Administrador';
      case self::ROL_SUPERVISOR:
        return 'Supervisor';
      case self::ROL_OPERADOR:
        return 'Operador';
      case self::ROL_CONSULTA:
        return 'Consulta';
      default:
        return 'Desconocido';
    }
  }
}
