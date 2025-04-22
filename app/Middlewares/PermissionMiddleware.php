<?php

/**
 * Middleware de Permisos
 * 
 * Verifica si el usuario tiene el permiso requerido
 */

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Models\permissionModel;

class PermissionMiddleware
{
    /**
     * Permiso requerido
     * @var string
     */
    private $permiso;

    /**
     * Constructor
     * 
     * @param string $permiso Slug del permiso requerido
     */
    public function __construct($permiso = null)
    {
        $this->permiso = $permiso;
    }

    /**
     * Procesa la petición
     * 
     * @param Request $request Petición a procesar
     * @param callable $next Siguiente función en la cadena
     * @return mixed Respuesta
     */
    public function handle(Request $request, callable $next)
    {
        // Iniciar sesión si no está iniciada
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Verificar si hay sesión activa
        if (!isset($_SESSION[APP_SESSION_NAME]) || empty($_SESSION[APP_SESSION_NAME]['id'])) {
            if ($request->expectsJson()) {
                return Response::json(array(
                    'status' => 'error',
                    'message' => 'No autenticado'
                ), 401);
            }
            return Response::redirect(APP_URL . 'login');
        }

        // Obtener ID de usuario de la sesión
        $usuarioId = $_SESSION[APP_SESSION_NAME]['id'];

        // Si no se especifica un permiso, continuar
        if (!$this->permiso) {
            return $next($request);
        }

        // Verificar permiso
        $permissionModel = new permissionModel();
        $tienePermiso = $permissionModel->verificarPermiso($usuarioId, $this->permiso);

        if (!$tienePermiso) {
            // Registrar intento de acceso no autorizado
            $permissionModel->registrarAccion(
                $usuarioId,
                'Intento de acceso no autorizado',
                'Permiso requerido: ' . $this->permiso . ', URI: ' . $request->getUri()
            );

            if ($request->expectsJson()) {
                return Response::json(array(
                    'status' => 'error',
                    'message' => 'No autorizado para esta acción'
                ), 403);
            }

            // Redirigir a página de acceso denegado
            return Response::redirect(APP_URL . 'error/403');
        }

        // Tiene permiso, continuar
        return $next($request);
    }
}
