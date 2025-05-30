<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\permissionModel;
use App\Models\roleModel;

/**
 * Controlador para la gestión de permisos
 */
class PermissionController
{
    /**
     * Modelo de permisos
     * @var permissionModel
     */
    private $permissionModel;

    /**
     * Modelo de roles
     * @var roleModel
     */
    private $roleModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->permissionModel = new permissionModel();
        $this->roleModel = new roleModel();
    }

    /**
     * Muestra la vista de listado de permisos
     */
    public function index(Request $request)
    {
        ob_start();
        $titulo = 'Permisos';
        $permisos = $this->permissionModel->obtenerTodosPermisos(false);
        include APP_ROOT . 'app/Views/permissions/index.php';
        $contenido = ob_get_clean();

        return Response::html($contenido);
    }

    /**
     * Muestra el formulario para asignar permisos a un rol
     */
    public function assignForm(Request $request)
    {
        $rolId = $request->param('role_id');
        $rol = $this->roleModel->obtenerRolPorId($rolId);

        if (!$rol) {
            return Response::redirect(APP_URL . 'error/404');
        }

        ob_start();
        $titulo = 'Asignar Permisos a Rol: ' . $rol->rol_descripcion;
        $todosPermisos = $this->permissionModel->obtenerTodosPermisos();
        $permisosAsignados = $this->permissionModel->obtenerPermisosPorRol($rolId);

        // Convertir permisos asignados a un array de IDs para facilitar la comparación
        $permisosAsignadosIds = array_map(function ($permiso) {
            return $permiso->permiso_id;
        }, $permisosAsignados);

        include APP_ROOT . 'app/Views/permissions/assign.php';
        $contenido = ob_get_clean();

        return Response::html($contenido);
    }

    /**
     * Guarda la asignación de permisos a un rol
     */
    public function assignSave(Request $request)
    {
        $rolId = $request->param('role_id');
        $permisoIds = $request->post('permisos', []);

        // Verificar que el rol existe
        $rol = $this->roleModel->obtenerRolPorId($rolId);
        if (!$rol) {
            if ($request->expectsJson()) {
                return Response::json(['error' => 'Rol no encontrado'], 404);
            }
            return Response::redirect(APP_URL . 'error/404');
        }

        // Asignar permisos
        $resultado = $this->permissionModel->asignarPermisosARol($rolId, $permisoIds);

        if ($request->expectsJson()) {
            if ($resultado) {
                return Response::json([
                    'status' => 'success',
                    'message' => 'Permisos asignados correctamente'
                ]);
            } else {
                return Response::json([
                    'status' => 'error',
                    'message' => 'Error al asignar permisos'
                ]);
            }
        }

        // Si no es una petición AJAX, redirigir
        if ($resultado) {
            // Redirigir con message de éxito
            return Response::redirect(APP_URL . 'roles');
        } else {
            // Redirigir con message de error
            return Response::redirect(APP_URL . 'permissions/assign/' . $rolId);
        }
    }

    /**
     * API: Obtiene todos los permisos
     */
    public function getAllPermissions(Request $request)
    {
        $permisos = $this->permissionModel->obtenerTodosPermisos();
        return Response::json([
            'status' => 'success',
            'data' => $permisos
        ]);
    }

    /**
     * API: Obtiene los permisos de un rol
     */
    public function getRolePermissions(Request $request)
    {
        $rolId = $request->param('id');
        $permisos = $this->permissionModel->obtenerPermisosPorRol($rolId);

        return Response::json([
            'status' => 'success',
            'data' => $permisos
        ]);
    }

    /**
     * API: Asigna permisos a un rol
     */
    public function assignPermissions(Request $request)
    {
        $rolId = $request->param('id');
        $datos = $request->json();

        if (!isset($datos['permisos']) || !is_array($datos['permisos'])) {
            return Response::json([
                'status' => 'error',
                'message' => 'Formato de datos inválido'
            ], 400);
        }

        $permisoIds = $datos['permisos'];

        // Verificar que el rol existe
        $rol = $this->roleModel->obtenerRolPorId($rolId);
        if (!$rol) {
            return Response::json([
                'status' => 'error',
                'message' => 'Rol no encontrado'
            ], 404);
        }

        // Asignar permisos
        $resultado = $this->permissionModel->asignarPermisosARol($rolId, $permisoIds);

        if ($resultado) {
            return Response::json([
                'status' => 'success',
                'message' => 'Permisos asignados correctamente'
            ]);
        } else {
            return Response::json([
                'status' => 'error',
                'message' => 'Error al asignar permisos'
            ], 500);
        }
    }
}
