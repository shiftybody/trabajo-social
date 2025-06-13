<?php

namespace App\Models;

use PDO;
use App\Models\mainModel;

/**
 * Modelo de permisos
 * Maneja los permisos en la base de datos.
 * Proporciona métodos para crear, actualizar, eliminar y obtener permisos.
 * Incluye validaciones para comprobar si un rol tiene un permiso específico,
 * asignar permisos a roles y obtener permisos de usuarios.
 */
class permissionModel extends mainModel
{
    /**
     * Obtiene todos los permisos
     * 
     * @param bool $soloActivos Si es true, solo devuelve permisos activos
     * @return array Lista de permisos
     */
    public function obtenerTodosPermisos($soloActivos = true)
    {
        try {
            $query = "SELECT * FROM permiso";
            if ($soloActivos) {
                $query .= " WHERE permiso_estado = 1";
            }
            $query .= " ORDER BY permiso_nombre ASC";

            $resultado = $this->ejecutarConsulta($query);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerTodosPermisos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un permiso por su ID
     * 
     * @param int $permisoId ID del permiso
     * @return object|false Datos del permiso o false si no existe
     */
    public function obtenerPermisoPorId($permisoId)
    {
        try {
            $query = "SELECT * FROM permiso WHERE permiso_id = :permiso_id";
            $resultado = $this->ejecutarConsulta($query, [':permiso_id' => $permisoId]);
            return $resultado->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerPermisoPorId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un permiso por su slug
     * 
     * @param string $slug Slug del permiso
     * @return object|false Datos del permiso o false si no existe
     */
    public function obtenerPermisoPorSlug($slug)
    {
        try {
            $query = "SELECT * FROM permiso WHERE permiso_slug = :slug";
            $resultado = $this->ejecutarConsulta($query, [':slug' => $slug]);
            return $resultado->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerPermisoPorSlug: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo permiso
     * 
     * @param string $nombre Nombre del permiso
     * @param string $descripcion Descripción del permiso
     * @param string $slug Slug único del permiso
     * @return int|false ID del permiso creado o false si hubo error
     */
    public function crearPermiso($nombre, $descripcion, $slug)
    {
        try {
            $datos = [
                'permiso_nombre' => $nombre,
                'permiso_descripcion' => $descripcion,
                'permiso_slug' => $slug,
                'permiso_estado' => 1,
                'permiso_fecha_creacion' => date("Y-m-d H:i:s"),
                'permiso_ultima_modificacion' => date("Y-m-d H:i:s")
            ];

            $resultado = $this->insertarDatos("permiso", $datos);

            if ($resultado->rowCount() > 0) {
                return $this->getLastInsertId();
            }

            return false;
        } catch (\Exception $e) {
            error_log("Error en crearPermiso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un permiso existente
     * 
     * @param int $permisoId ID del permiso
     * @param array $datos Datos a actualizar (nombre, descripcion, slug, estado)
     * @return bool True si se actualizó correctamente, false en caso contrario
     */
    public function actualizarPermiso($permisoId, $datos)
    {
        try {
            $camposActualizar = [];

            // Construir array de datos a actualizar
            foreach ($datos as $campo => $valor) {
                $campoDb = 'permiso_' . $campo;
                $camposActualizar[] = [
                    "campo_nombre" => $campoDb,
                    "campo_marcador" => ":" . $campo,
                    "campo_valor" => $valor
                ];
            }

            // Añadir fecha de última modificación
            $camposActualizar[] = [
                "campo_nombre" => "permiso_ultima_modificacion",
                "campo_marcador" => ":ultima_modificacion",
                "campo_valor" => date("Y-m-d H:i:s")
            ];

            $condicion = [
                "condicion_campo" => "permiso_id",
                "condicion_marcador" => ":permiso_id",
                "condicion_valor" => $permisoId
            ];

            $resultado = $this->actualizarDatos("permiso", $camposActualizar, $condicion);
            return $resultado->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error en actualizarPermiso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un permiso
     * 
     * @param int $permisoId ID del permiso
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function eliminarPermiso($permisoId)
    {
        try {
            // Primero eliminar las relaciones en rol_permiso
            $query = "DELETE FROM rol_permiso WHERE permiso_id = :permiso_id";
            $this->ejecutarConsulta($query, [':permiso_id' => $permisoId]);

            // Luego eliminar el permiso
            $resultado = $this->eliminarRegistro("permiso", "permiso_id", $permisoId);
            return $resultado->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error en eliminarPermiso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los permisos asignados a un rol
     * 
     * @param int $rolId ID del rol
     * @return array Lista de permisos del rol
     */
    public function obtenerPermisosPorRol($rolId)
    {
        try {
            $query = "SELECT p.* FROM permiso p
                     INNER JOIN rol_permiso rp ON p.permiso_id = rp.permiso_id
                     WHERE rp.rol_id = :rol_id AND p.permiso_estado = 1
                     ORDER BY p.permiso_nombre ASC";

            $resultado = $this->ejecutarConsulta($query, [':rol_id' => $rolId]);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerPermisosPorRol: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Asigna permisos a un rol
     * 
     * @param int $rolId ID del rol
     * @param array $permisoIds Array con IDs de permisos a asignar
     * @return bool True si se asignaron correctamente, false en caso contrario
     */
    public function asignarPermisosARol($rolId, $permisoIds)
    {
        try {
            $conexion = $this->conectarBD();
            $conexion->beginTransaction();

            // Eliminar permisos actuales
            $query = "DELETE FROM rol_permiso WHERE rol_id = :rol_id";
            $stmt = $conexion->prepare($query);
            $stmt->bindValue(':rol_id', $rolId);
            $stmt->execute();

            // Si no hay permisos para asignar, terminar
            if (empty($permisoIds)) {
                $conexion->commit();
                return true;
            }

            // Insertar nuevos permisos
            $query = "INSERT INTO rol_permiso (rol_id, permiso_id, fecha_creacion) VALUES (:rol_id, :permiso_id, :fecha)";
            $stmt = $conexion->prepare($query);

            foreach ($permisoIds as $permisoId) {
                $stmt->bindValue(':rol_id', $rolId);
                $stmt->bindValue(':permiso_id', $permisoId);
                $stmt->bindValue(':fecha', date("Y-m-d H:i:s"));
                $stmt->execute();
            }

            $conexion->commit();
            return true;
        } catch (\Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            error_log("Error en asignarPermisosARol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un rol tiene un permiso específico
     * 
     * @param int $rolId ID del rol
     * @param string $permisoSlug Slug del permiso
     * @return bool True si el rol tiene el permiso, false en caso contrario
     */
    public function rolTienePermiso($rolId, $permisoSlug)
    {
        try {
            $query = "SELECT COUNT(*) FROM rol_permiso rp
                     INNER JOIN permiso p ON rp.permiso_id = p.permiso_id
                     WHERE rp.rol_id = :rol_id AND p.permiso_slug = :permiso_slug AND p.permiso_estado = 1";

            $resultado = $this->ejecutarConsulta($query, [
                ':rol_id' => $rolId,
                ':permiso_slug' => $permisoSlug
            ]);

            return $resultado->fetchColumn() > 0;
        } catch (\Exception $e) {
            error_log("Error en rolTienePermiso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los permisos específicos de un usuario
     * 
     * @param int $usuarioId ID del usuario
     * @return array Lista de permisos específicos del usuario
     */
    public function obtenerPermisosUsuario($usuarioId)
    {
        try {
            $query = "SELECT p.*, up.concedido FROM permiso p
                     INNER JOIN usuario_permiso up ON p.permiso_id = up.permiso_id
                     WHERE up.usuario_id = :usuario_id AND p.permiso_estado = 1
                     ORDER BY p.permiso_nombre ASC";

            $resultado = $this->ejecutarConsulta($query, [':usuario_id' => $usuarioId]);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerPermisosUsuario: " . $e->getMessage());
            return [];
        }
    }
}
