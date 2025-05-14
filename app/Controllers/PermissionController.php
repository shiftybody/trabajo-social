<?php

namespace App\Models;

use PDO;
use App\Models\mainModel;

/**
 * Modelo de permisos simplificado
 * 
 * Maneja los permisos siguiendo la estructura:
 * rol -> rol_permiso -> permiso
 * usuario -> usuario_permiso -> permiso
 */
class permissionModel extends mainModel
{
    /**
     * Obtiene todos los permisos activos
     */
    public function obtenerTodosPermisos()
    {
        try {
            $query = "SELECT * FROM permiso WHERE permiso_estado = 1 ORDER BY permiso_nombre";
            $resultado = $this->ejecutarConsulta($query);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerTodosPermisos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los permisos asignados a un rol
     */
    public function obtenerPermisosPorRol($rolId)
    {
        try {
            $query = "SELECT p.permiso_id, p.permiso_nombre, p.permiso_slug, p.permiso_descripcion
                     FROM permiso p
                     INNER JOIN rol_permiso rp ON p.permiso_id = rp.permiso_id
                     WHERE rp.rol_id = :rol_id AND p.permiso_estado = 1
                     ORDER BY p.permiso_nombre";
            
            $resultado = $this->ejecutarConsulta($query, [':rol_id' => $rolId]);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerPermisosPorRol: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los permisos específicos de un usuario
     */
    public function obtenerPermisosUsuario($usuarioId)
    {
        try {
            $query = "SELECT p.permiso_id, p.permiso_nombre, p.permiso_slug, 
                            p.permiso_descripcion, up.concedido
                     FROM permiso p
                     INNER JOIN usuario_permiso up ON p.permiso_id = up.permiso_id
                     WHERE up.usuario_id = :usuario_id AND p.permiso_estado = 1
                     ORDER BY p.permiso_nombre";
            
            $resultado = $this->ejecutarConsulta($query, [':usuario_id' => $usuarioId]);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerPermisosUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si un usuario tiene un permiso específico
     * Primero verifica permisos específicos del usuario, luego del rol
     */
    public function verificarPermiso($usuarioId, $permisoSlug)
    {
        try {
            // 1. Verificar permiso específico del usuario
            $query = "SELECT up.concedido 
                     FROM usuario_permiso up
                     INNER JOIN permiso p ON up.permiso_id = p.permiso_id
                     WHERE up.usuario_id = :usuario_id 
                     AND p.permiso_slug = :permiso_slug
                     AND p.permiso_estado = 1";
            
            $resultado = $this->ejecutarConsulta($query, [
                ':usuario_id' => $usuarioId,
                ':permiso_slug' => $permisoSlug
            ]);
            
            $permisoUsuario = $resultado->fetch(PDO::FETCH_OBJ);
            
            // Si existe un permiso específico del usuario, usar ese
            if ($permisoUsuario !== false) {
                return (bool)$permisoUsuario->concedido;
            }

            // 2. Si no hay permiso específico, verificar permisos del rol
            $query = "SELECT COUNT(*) 
                     FROM usuario u
                     INNER JOIN rol_permiso rp ON u.usuario_rol = rp.rol_id
                     INNER JOIN permiso p ON rp.permiso_id = p.permiso_id
                     WHERE u.usuario_id = :usuario_id 
                     AND p.permiso_slug = :permiso_slug
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
     */
    public function asignarPermisoRol($rolId, $permisoId)
    {
        try {
            // Verificar si ya existe
            $query = "SELECT COUNT(*) FROM rol_permiso 
                     WHERE rol_id = :rol_id AND permiso_id = :permiso_id";
            $resultado = $this->ejecutarConsulta($query, [
                ':rol_id' => $rolId,
                ':permiso_id' => $permisoId
            ]);

            if ($resultado->fetchColumn() > 0) {
                return true; // Ya existe
            }

            // Insertar nuevo
            $datos = [
                'rol_id' => $rolId,
                'permiso_id' => $permisoId,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ];

            $resultado = $this->insertarDatos('rol_permiso', $datos);
            return $resultado->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error en asignarPermisoRol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Revoca un permiso de un rol
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
     * Asigna un permiso específico a un usuario
     */
    public function asignarPermisoUsuario($usuarioId, $permisoId, $concedido = true)
    {
        try {
            // Verificar si ya existe
            $query = "SELECT COUNT(*) FROM usuario_permiso 
                     WHERE usuario_id = :usuario_id AND permiso_id = :permiso_id";
            $resultado = $this->ejecutarConsulta($query, [
                ':usuario_id' => $usuarioId,
                ':permiso_id' => $permisoId
            ]);

            $datos = [
                'usuario_id' => $usuarioId,
                'permiso_id' => $permisoId,
                'concedido' => $concedido ? 1 : 0,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ];

            if ($resultado->fetchColumn() > 0) {
                // Actualizar existente
                $camposActualizar = [
                    [
                        'campo_nombre' => 'concedido',
                        'campo_marcador' => ':concedido',
                        'campo_valor' => $concedido ? 1 : 0
                    ]
                ];

                $condicion = [
                    'condicion_campo' => 'usuario_id',
                    'condicion_marcador' => ':usuario_id',
                    'condicion_valor' => $usuarioId
                ];

                // Agregar condición para permiso_id
                $query = "UPDATE usuario_permiso SET concedido = :concedido 
                         WHERE usuario_id = :usuario_id AND permiso_id = :permiso_id";
                $resultado = $this->ejecutarConsulta($query, [
                    ':concedido' => $concedido ? 1 : 0,
                    ':usuario_id' => $usuarioId,
                    ':permiso_id' => $permisoId
                ]);
            } else {
                // Insertar nuevo
                $resultado = $this->insertarDatos('usuario_permiso', $datos);
            }

            return $resultado->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error en asignarPermisoUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un permiso específico de un usuario
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
}