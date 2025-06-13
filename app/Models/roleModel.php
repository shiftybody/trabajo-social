<?php

namespace App\Models;

use PDO;
use App\Models\mainModel;

/**
 * Modelo para manejar operaciones relacionadas con roles en la base de datos.
 * Proporciona métodos para crear, actualizar, eliminar y obtener roles.
 * Incluye validaciones para evitar duplicados y manejar estados de roles.
 */
class roleModel extends mainModel
{
  /**
   * Obtiene todos los roles activos
   * 
   * @return array Lista de roles
   */
  public function obtenerTodosRoles()
  {
    try {
      $query = "SELECT * FROM rol WHERE rol_estado = 1";
      $resultado = $this->ejecutarConsulta($query);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en obtenerTodosRoles: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene un rol por su ID
   * 
   * @param int $rolId ID del rol
   * @return object|false Datos del rol o false si no existe
   */
  public function obtenerRolPorId($rolId)
  {
    try {
      $query = "SELECT * FROM rol WHERE rol_id = :rol_id";
      $resultado = $this->ejecutarConsulta($query, [':rol_id' => $rolId]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en obtenerRolPorId: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Crea un nuevo rol
   * 
   * @param string $nombre Descripción del rol
   * @return int|false ID del rol creado o false si hubo error
   */
  public function crearRol($nombre)
  {
    try {
      $datos = [
        'rol_nombre' => $nombre,
        'rol_estado' => 1,
        'rol_fecha_creacion' => date("Y-m-d H:i:s"),
        'rol_ultima_modificacion' => date("Y-m-d H:i:s")
      ];

      $resultado = $this->insertarDatos("rol", $datos);

      if ($resultado->rowCount() > 0) {
        return $this->getLastInsertId();
      }

      return false;
    } catch (\Exception $e) {
      error_log("Error en crearRol: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Actualiza un rol existente
   * 
   * @param int $rolId ID del rol
   * @param string $nombre Nueva descripción
   * @return bool True si se actualizó correctamente, false en caso contrario
   */
  public function actualizarRol($rolId, $nombre)
  {
    try {
      $camposActualizar = [
        [
          "campo_nombre" => "rol_nombre",
          "campo_marcador" => ":nombre",
          "campo_valor" => $nombre
        ],
        [
          "campo_nombre" => "rol_ultima_modificacion",
          "campo_marcador" => ":ultima_modificacion",
          "campo_valor" => date("Y-m-d H:i:s")
        ]
      ];

      $condicion = [
        "condicion_campo" => "rol_id",
        "condicion_marcador" => ":rol_id",
        "condicion_valor" => $rolId
      ];

      $resultado = $this->actualizarDatos("rol", $camposActualizar, $condicion);
      return $resultado->rowCount() > 0;
    } catch (\Exception $e) {
      error_log("Error en actualizarRol: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Cambia el estado de un rol (activo/inactivo)
   * 
   * @param int $rolId ID del rol
   * @param bool $estado Nuevo estado (true=activo, false=inactivo)
   * @return bool True si se cambió correctamente, false en caso contrario
   */
  public function cambiarEstadoRol($rolId, $estado)
  {
    try {
      $camposActualizar = [
        [
          "campo_nombre" => "rol_estado",
          "campo_marcador" => ":estado",
          "campo_valor" => $estado ? 1 : 0
        ],
        [
          "campo_nombre" => "rol_ultima_modificacion",
          "campo_marcador" => ":ultima_modificacion",
          "campo_valor" => date("Y-m-d H:i:s")
        ]
      ];

      $condicion = [
        "condicion_campo" => "rol_id",
        "condicion_marcador" => ":rol_id",
        "condicion_valor" => $rolId
      ];

      $resultado = $this->actualizarDatos("rol", $camposActualizar, $condicion);
      return $resultado->rowCount() > 0;
    } catch (\Exception $e) {
      error_log("Error en cambiarEstadoRol: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Elimina un rol (si no tiene usuarios asignados)
   * 
   * @param int $rolId ID del rol
   * @return bool True si se eliminó correctamente, false en caso contrario
   */
  public function eliminarRol($rolId)
  {
    try {
      // Verificar si hay usuarios con este rol
      $query = "SELECT COUNT(*) FROM usuario WHERE usuario_rol = :rol_id";
      $resultado = $this->ejecutarConsulta($query, [':rol_id' => $rolId]);

      if ($resultado->fetchColumn() > 0) {
        // Hay usuarios asignados, no se puede eliminar
        return false;
      }

      // Eliminar permisos del rol
      $query = "DELETE FROM rol_permiso WHERE rol_id = :rol_id";
      $this->ejecutarConsulta($query, [':rol_id' => $rolId]);

      // Eliminar rol
      $resultado = $this->eliminarRegistro("rol", "rol_id", $rolId);
      return $resultado->rowCount() > 0;
    } catch (\Exception $e) {
      error_log("Error en eliminarRol: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtiene todos los usuarios que tienen un rol específico
   * 
   * @param int $rolId ID del rol
   * @return array Lista de usuarios con ese rol
   */
  public function obtenerUsuariosPorRol($rolId)
  {
    try {
      $query = "SELECT u.usuario_id, u.usuario_nombre, u.usuario_apellido_paterno, 
                     u.usuario_apellido_materno, u.usuario_email, u.usuario_usuario, 
                     u.usuario_estado 
                     FROM usuario u 
                     WHERE u.usuario_rol = :rol_id";

      $resultado = $this->ejecutarConsulta($query, [':rol_id' => $rolId]);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en obtenerUsuariosPorRol: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Verifica si existe un rol con la descripción dada
   * 
   * @param string $nombre Descripción del rol
   * @return bool True si existe, false en caso contrario
   */
  public function existeRolPornombre($nombre)
  {
    try {
      $query = "SELECT COUNT(*) FROM rol WHERE rol_nombre = :nombre";
      $resultado = $this->ejecutarConsulta($query, [':nombre' => $nombre]);
      return $resultado->fetchColumn() > 0;
    } catch (\Exception $e) {
      error_log("Error en existeRolPornombre: " . $e->getMessage());
      return false;
    }
  }
}
