<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\roleModel;
use App\Models\permissionModel;
use Exception;

class RoleController
{

  private $roleModel;
  private $permissionModel;

  public function __construct()
  {
    $this->roleModel = new roleModel();
    $this->permissionModel = new permissionModel();
  }

  public function indexView()
  {
    ob_start();
    $titulo = 'Roles';
    include APP_ROOT . 'app/Views/roles/index.php';
    $contenido = ob_get_clean();
    return Response::html($contenido);
  }

  public function createView()
  {
    ob_start();
    $titulo = 'Crear Rol';
    include APP_ROOT . 'app/Views/roles/create.php';
    $contenido = ob_get_clean();
    return Response::html($contenido);
  }

  public function editView(Request $request)
  {

    $id = $request->param('id');
    $rol = $this->roleModel->obtenerRolPorId($id);

    if (!$rol) {
      return Response::redirect(APP_URL . 'error/404');
    }

    ob_start();
    $titulo = 'Editar Rol';
    include APP_ROOT . 'app/Views/roles/edit.php';
    $contenido = ob_get_clean();
    return Response::html($contenido);
  }

  public function permissionsView(Request $request)
  {
    $id = $request->param('id');

    $rol = $this->roleModel->obtenerRolPorId($id);
    if (!$rol) {
      return Response::redirect(APP_URL . 'error/404');
    }

    ob_start();
    $titulo = 'Gestionar Permisos';
    $rol_id = $id;
    include APP_ROOT . 'app/Views/roles/permissions.php';
    $contenido = ob_get_clean();

    return Response::html($contenido);
  }

  public function getAllRoles()
  {
    try {
      $roles = $this->roleModel->obtenerTodosRoles();

      foreach ($roles as $rol) {
        $usuarios = $this->roleModel->obtenerUsuariosPorRol($rol->rol_id);
        $rol->usuarios_count = count($usuarios);
      }

      return Response::json([
        'status' => 'success',
        'data' => $roles
      ]);
    } catch (Exception $e) {
      error_log("Error en getAllRoles: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener los roles'
      ], 500);
    }
  }

  public function getAllPermissions()
  {
    $permisos = $this->permissionModel->obtenerTodosPermisos();
    return Response::json([
      'status' => 'success',
      'data' => $permisos
    ]);
  }

  public function getRoleById(Request $request)
  {
    try {
      $id = $request->param('id');

      if (!$id || !is_numeric($id)) {
        return Response::json([
          'status' => 'error',
          'message' => 'ID de rol inválido'
        ], 400);
      }

      $rol = $this->roleModel->obtenerRolPorId($id);

      if (!$rol) {
        return Response::json([
          'status' => 'error',
          'message' => 'Rol no encontrado'
        ], 404);
      }

      $usuarios = $this->roleModel->obtenerUsuariosPorRol($id);
      $rol->usuarios_asignados = $usuarios;
      $rol->usuarios_count = count($usuarios);

      return Response::json([
        'status' => 'success',
        'data' => $rol
      ]);
    } catch (Exception $e) {
      error_log("Error en getRoleById: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  public function store(Request $request)
  {
    try {
      $datos = $request->POST();

      $validar = [
        'nombre' => [
          'requerido' => true,
          'min' => 2,
          'max' => 50,
          'sanitizar' => true
        ],
        'rol_base' => []
      ];

      $resultado = $this->roleModel->validarDatos($datos, $validar);

      if (!empty($resultado['errores'])) {
        return Response::json([
          'status' => 'error',
          'errores' => $resultado['errores']
        ]);
      }

      if ($this->roleModel->existeRolPornombre($resultado['datos']['nombre'])) {
        return Response::json([
          'status' => 'error',
          'errores' => ['nombre' => 'Ya existe un rol con este nombre']
        ]);
      }

      $rolId = $this->roleModel->crearRol($resultado['datos']['nombre']);

      error_log("Rol creado con ID: $rolId");
      error_log("rol base : " . $resultado['datos']['rol_base']);

      if ($rolId) {

        if (!empty($resultado['datos']['rol_base'])) {

          $permisosBase = $this->permissionModel->obtenerPermisosPorRol($resultado['datos']['rol_base']);

          $permisosIds = array_map(function ($permiso) {
            return $permiso->permiso_id;
          }, $permisosBase);

          if (!empty($permisosIds)) {
            $this->permissionModel->asignarPermisosARol($rolId, $permisosIds);
          }
        }

        return Response::json([
          'status' => 'success',
          'message' => 'Rol creado correctamente',
          'data' => ['id' => $rolId]
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al crear el rol'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en store: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  public function update(Request $request)
  {
    try {
      $id = $request->param('id');
      $datos = $request->POST();
      $rol = $this->roleModel->obtenerRolPorId($id);

      if (!$rol) {
        return Response::json([
          'status' => 'error',
          'message' => 'Rol no encontrado'
        ], 404);
      }

      $validar = [
        'nombre' => [
          'requerido' => true,
          'min' => 2,
          'max' => 50,
          'sanitizar' => true
        ]
      ];

      $resultado = $this->roleModel->validarDatos($datos, $validar);

      if (!empty($resultado['errores'])) {
        return Response::json([
          'status' => 'error',
          'errores' => $resultado['errores']
        ]);
      }

      // Verificar que el nombre no exista (excepto el rol actual)
      if ($resultado['datos']['nombre'] !== $rol->rol_nombre) {
        if ($this->roleModel->existeRolPornombre($resultado['datos']['nombre'])) {
          return Response::json([
            'status' => 'error',
            'errores' => ['nombre' => 'Ya existe un rol con este nombre']
          ]);
        }
      }

      // Actualizar el rol
      $actualizado = $this->roleModel->actualizarRol($id, $resultado['datos']['nombre']);

      if ($actualizado) {
        return Response::json([
          'status' => 'success',
          'message' => 'Rol actualizado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al actualizar el rol'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en update: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  public function delete(Request $request)
  {
    try {
      $id = $request->param('id');

      // Verificar que el rol existe
      $rol = $this->roleModel->obtenerRolPorId($id);
      if (!$rol) {
        return Response::json([
          'status' => 'error',
          'message' => 'Rol no encontrado'
        ], 404);
      }

      // Verificar que no sea el rol de administrador (ID 1)
      if ($id == 1) {
        return Response::json([
          'status' => 'error',
          'message' => 'No se puede eliminar el rol de administrador'
        ], 400);
      }

      // Verificar que no tenga usuarios asignados
      $usuarios = $this->roleModel->obtenerUsuariosPorRol($id);
      if (count($usuarios) > 0) {
        return Response::json([
          'status' => 'error',
          'message' => 'No se puede eliminar el rol porque tiene usuarios asignados'
        ], 400);
      }

      // Eliminar el rol
      $eliminado = $this->roleModel->eliminarRol($id);

      if ($eliminado) {
        return Response::json([
          'status' => 'success',
          'message' => 'Rol eliminado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al eliminar el rol'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en delete: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Obtiene los permisos de un rol
   */
  public function getRolePermissions(Request $request)
  {
    try {
      $id = $request->param('id');

      $permisos = $this->permissionModel->obtenerPermisosPorRol($id);

      return Response::json([
        'status' => 'success',
        'data' => $permisos
      ]);
    } catch (Exception $e) {
      error_log("Error en getRolePermissions: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener permisos del rol'
      ], 500);
    }
  }

  /**
   * API: Actualiza los permisos de un rol
   */
  public function updateRolePermissions(Request $request)
  {
    try {
      $id = $request->param('id');
      $datos = $request->POST();

      // Verificar que el rol existe
      $rol = $this->roleModel->obtenerRolPorId($id);
      if (!$rol) {
        return Response::json([
          'status' => 'error',
          'message' => 'Rol no encontrado'
        ], 404);
      }

      // Obtener permisos del request
      $permisos = isset($datos['permisos']) && is_array($datos['permisos']) ? $datos['permisos'] : [];

      // Actualizar permisos
      $actualizado = $this->permissionModel->asignarPermisosARol($id, $permisos);

      if ($actualizado) {
        return Response::json([
          'status' => 'success',
          'message' => 'Permisos del rol actualizados correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al actualizar permisos del rol'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en updateRolePermissions: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Obtiene las rutas de navegación filtradas por permisos del usuario
   */
  public function getNavigationRoutes()
  {
    try {
      // Leer el archivo JSON de rutas
      $rutasPath = APP_ROOT . 'public/js/data/navigation-routes.json';

      if (!file_exists($rutasPath)) {
        throw new Exception('Archivo de rutas no encontrado');
      }

      $rutasJson = file_get_contents($rutasPath);
      $rutasData = json_decode($rutasJson, true);

      if (!$rutasData || !isset($rutasData['routes'])) {
        throw new Exception('Formato de archivo de rutas inválido');
      }

      // Filtrar rutas según permisos del usuario actual
      $rutasFiltradas = [];

      foreach ($rutasData['routes'] as $ruta) {
        if (Auth::can($ruta['permissionKey'])) {
          $rutasFiltradas[] = $ruta;
        }
      }

      return Response::json([
        'status' => 'success',
        'data' => [
          'routes' => $rutasFiltradas
        ]
      ]);
    } catch (Exception $e) {
      error_log("Error en getNavigationRoutes: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener rutas de navegación'
      ], 500);
    }
  }
}
