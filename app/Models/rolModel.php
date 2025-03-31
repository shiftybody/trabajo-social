<?php

namespace App\Models;

use PDO;
use app\models\mainModel;

/**
 * Clase rolModel
 * 
 * Maneja las operaciones relacionadas con roles de usuario en la base de datos
 */
class rolModel extends mainModel
{
  /**
   * Obtiene todos los roles disponibles
   * 
   * @return array Array de roles
   */
  public function obtenerTodosRoles()
  {
    try {
      $query = "SELECT * FROM rol ORDER BY rol_id";
      $result = $this->ejecutarConsulta($query);
      return $result->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en obtenerTodosRoles: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene un rol por su ID
   * 
   * @param int $id ID del rol
   * @return object|false Datos del rol o false si no existe
   */
  public function obtenerRolPorId($id)
  {
    try {
      $query = "SELECT * FROM rol WHERE rol_id = :id";
      $result = $this->ejecutarConsulta($query, [':id' => $id]);
      return $result->fetch(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en obtenerRolPorId: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtiene los permisos asociados a un rol
   * 
   * @param int $rol_id ID del rol
   * @return array Array de permisos
   */
  public function obtenerPermisosPorRol($rol_id)
  {
    try {
      $query = "SELECT p.* FROM permiso p 
                     JOIN rol_permiso rp ON p.permiso_id = rp.permiso_id 
                     WHERE rp.rol_id = :rol_id";

      $result = $this->ejecutarConsulta($query, [':rol_id' => $rol_id]);
      return $result->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en obtenerPermisosPorRol: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Verifica si un rol tiene un permiso específico
   * 
   * @param int $rol_id ID del rol
   * @param string $permiso_codigo Código del permiso
   * @return bool True si tiene el permiso, false en caso contrario
   */
  public function tienePermiso($rol_id, $permiso_codigo)
  {
    try {
      $query = "SELECT COUNT(*) FROM rol_permiso rp 
                     JOIN permiso p ON rp.permiso_id = p.permiso_id 
                     WHERE rp.rol_id = :rol_id AND p.permiso_codigo = :permiso_codigo";

      $result = $this->ejecutarConsulta($query, [
        ':rol_id' => $rol_id,
        ':permiso_codigo' => $permiso_codigo
      ]);

      return $result->fetchColumn() > 0;
    } catch (\Exception $e) {
      error_log("Error en tienePermiso: " . $e->getMessage());
      return false;
    }
  }
}
