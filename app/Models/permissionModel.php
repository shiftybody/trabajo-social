<?php
namespace App\Models;

use PDO;
use App\Models\mainModel;

class permissionModel extends mainModel
{
    /**
     * Obtiene todos los permisos
     * 
     * @return array Lista de permisos
     */
    public function obtenerTodosPermisos()
    {
        try {
            $query = "SELECT * FROM permiso WHERE permiso_estado = 1";
            $resultado = $this->ejecutarConsulta($query);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerTodosPermisos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene los permisos asignados a un rol
     * 
     * @param int $rolId ID del rol
     * @return array Lista de permisos del rol
     */
    public function obtenerPermisosPorRol($rolId)
    {
        try {
            $query = "SELECT p.* FROM permiso p
                     JOIN rol_permiso rp ON p.permiso_id = rp.permiso_id
                     WHERE rp.rol_id = :rol_id AND p.permiso_estado = 1";
            $resultado = $this->ejecutarConsulta($query, [':rol_id' => $rolId]);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerPermisosPorRol: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene los permisos específicos de un usuario
     * 
     * @param int $usuarioId ID del usuario
     * @return array Permisos específicos del usuario
     */
    public function obtenerPermisosUsuario($usuarioId)
    {
        try {
            $query = "SELECT p.*, up.concedido FROM permiso p
                     JOIN usuario_permiso up ON p.permiso_id = up.permiso_id
                     WHERE up.usuario_id = :usuario_id AND p.permiso_estado = 1";
            $resultado = $this->ejecutarConsulta($query, [':usuario_id' => $usuarioId]);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerPermisosUsuario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verifica si un usuario tiene un permiso específico
     * 
     * @param int $usuarioId ID del usuario
     * @param string $permisoSlug Slug del permiso
     * @return bool True si tiene permiso, false en caso contrario
     */
    public function verificarPermiso($usuarioId, $permisoSlug)
    {
        try {
            // Primero verificar si hay un permiso específico para el usuario
            $query = "SELECT up.concedido FROM usuario_permiso up
                     JOIN permiso p ON up.permiso_id = p.permiso_id
                     WHERE up.usuario_id = :usuario_id AND p.permiso_slug = :permiso_slug
                     AND p.permiso_estado = 1";
            $resultado = $this->ejecutarConsulta($query, [
                ':usuario_id' => $usuarioId,
                ':permiso_slug' => $permisoSlug
            ]);
            $permisoUsuario = $resultado->fetch(PDO::FETCH_OBJ);
            
            // Si existe un permiso específico, retornar según concedido
            if ($permisoUsuario) {
                return (bool)$permisoUsuario->concedido;
            }
            
            // Si no hay permiso específico, verificar permisos del rol
            $query = "SELECT COUNT(*) FROM rol_permiso rp
                     JOIN permiso p ON rp.permiso_id = p.permiso_id
                     JOIN usuario u ON u.usuario_rol = rp.rol_id
                     WHERE u.usuario_id = :usuario_id AND p.permiso_slug = :permiso_slug
                     AND p.permiso_estado = 1";
            $resultado = $this->ejecutarConsulta($query, [
                ':usuario_id' => $usuarioId,
                ':permiso_slug' => $permisoSlug
            ]);
            
            return $resultado->fetchColumn() > 0;
        } catch (\Exception $e) {
            error_log("Error en verificarPermiso: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Asigna un permiso a un rol
     * 
     * @param int $rolId ID del rol
     * @param int $permisoId ID del permiso
     * @return bool True si se asignó correctamente, false en caso contrario
     */
    public function asignarPermisoRol($rolId, $permisoId)
    {
        try {
            // Verificar si ya existe la asignación
            $query = "SELECT COUNT(*) FROM rol_permiso 
                     WHERE rol_id = :rol_id AND permiso_id = :permiso_id";
            $resultado = $this->ejecutarConsulta($query, [
                ':rol_id' => $rolId,
                ':permiso_id' => $permisoId
            ]);
            
            if ($resultado->fetchColumn() > 0) {
                return true; // Ya existe la asignación
            }
            
            // Insertar nueva asignación
            $query = "INSERT INTO rol_permiso (rol_id, permiso_id, fecha_creacion) 
                     VALUES (:rol_id, :permiso_id, NOW())";
            $resultado = $this->ejecutarConsulta($query, [
                ':rol_id' => $rolId,
                ':permiso_id' => $permisoId
            ]);
            
            return $resultado->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error en asignarPermisoRol: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Revoca un permiso de un rol
     * 
     * @param int $rolId ID del rol
     * @param int $permisoId ID del permiso
     * @return bool True si se revocó correctamente, false en caso contrario
     */
    public function revocarPermisoRol($rolId, $permisoId)
    {
        try {
            $query = "DELETE FROM rol_permiso 
                     WHERE rol_id = :rol_id AND permiso_id = :permiso_id";
            $resultado = $this->ejecutarConsulta($query, [
                ':rol_id' => $rolId,
                ':permiso_id' => $permisoId
            ]);
            
            return $resultado->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error en revocarPermisoRol: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Asigna o deniega un permiso específico a un usuario
     * 
     * @param int $usuarioId ID del usuario
     * @param int $permisoId ID del permiso
     * @param bool $concedido True para conceder, false para denegar
     * @return bool True si se asignó correctamente, false en caso contrario
     */
    public function asignarPermisoUsuario($usuarioId, $permisoId, $concedido = true)
    {
        try {
            // Verificar si ya existe la asignación
            $query = "SELECT COUNT(*) FROM usuario_permiso 
                     WHERE usuario_id = :usuario_id AND permiso_id = :permiso_id";
            $resultado = $this->ejecutarConsulta($query, [
                ':usuario_id' => $usuarioId,
                ':permiso_id' => $permisoId
            ]);
            
            if ($resultado->fetchColumn() > 0) {
                // Actualizar asignación existente
                $query = "UPDATE usuario_permiso SET concedido = :concedido 
                         WHERE usuario_id = :usuario_id AND permiso_id = :permiso_id";
            } else {
                // Insertar nueva asignación
                $query = "INSERT INTO usuario_permiso (usuario_id, permiso_id, concedido, fecha_creacion) 
                         VALUES (:usuario_id, :permiso_id, :concedido, NOW())";
            }
            
            $resultado = $this->ejecutarConsulta($query, [
                ':usuario_id' => $usuarioId,
                ':permiso_id' => $permisoId,
                ':concedido' => $concedido ? 1 : 0
            ]);
            
            return $resultado->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error en asignarPermisoUsuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina un permiso específico de un usuario
     * 
     * @param int $usuarioId ID del usuario
     * @param int $permisoId ID del permiso
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function eliminarPermisoUsuario($usuarioId, $permisoId)
    {
        try {
            $query = "DELETE FROM usuario_permiso 
                     WHERE usuario_id = :usuario_id AND permiso_id = :permiso_id";
            $resultado = $this->ejecutarConsulta($query, [
                ':usuario_id' => $usuarioId,
                ':permiso_id' => $permisoId
            ]);
            
            return $resultado->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error en eliminarPermisoUsuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registra una acción de usuario en el registro de acceso
     * 
     * @param int|null $usuarioId ID del usuario o null si no está autenticado
     * @param string $accion Descripción de la acción realizada
     * @param string|null $detalles Detalles adicionales (opcional)
     * @return bool True si se registró correctamente, false en caso contrario
     */
    public function registrarAccion($usuarioId, $accion, $detalles = null)
    {
        try {
            $query = "INSERT INTO registro_acceso (usuario_id, ip_address, accion, detalles, fecha_registro) 
                     VALUES (:usuario_id, :ip_address, :accion, :detalles, NOW())";
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            
            $resultado = $this->ejecutarConsulta($query, [
                ':usuario_id' => $usuarioId,
                ':ip_address' => $ipAddress,
                ':accion' => $accion,
                ':detalles' => $detalles
            ]);
            
            return $resultado->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error en registrarAccion: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene el historial de acciones de un usuario
     * 
     * @param int $usuarioId ID del usuario
     * @param int $limite Límite de registros (opcional, predeterminado 50)
     * @return array Lista de acciones del usuario
     */
    public function obtenerHistorialUsuario($usuarioId, $limite = 50)
    {
        try {
            $query = "SELECT * FROM registro_acceso 
                     WHERE usuario_id = :usuario_id 
                     ORDER BY fecha_registro DESC 
                     LIMIT :limite";
            
            $resultado = $this->ejecutarConsulta($query, [
                ':usuario_id' => $usuarioId,
                ':limite' => $limite
            ]);
            
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerHistorialUsuario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene las últimas acciones registradas en el sistema
     * 
     * @param int $limite Límite de registros (opcional, predeterminado 100)
     * @return array Lista de acciones
     */
    public function obtenerUltimasAcciones($limite = 100)
    {
        try {
            $query = "SELECT ra.*, u.usuario_nombre, u.usuario_apellido_paterno 
                     FROM registro_acceso ra 
                     LEFT JOIN usuario u ON ra.usuario_id = u.usuario_id 
                     ORDER BY ra.fecha_registro DESC 
                     LIMIT :limite";
            
            $resultado = $this->ejecutarConsulta($query, [':limite' => $limite]);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerUltimasAcciones: " . $e->getMessage());
            return [];
        }
    }
}