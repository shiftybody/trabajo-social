<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

/**
 * Middleware de Permisos Flexible
 */
class PermissionMiddleware
{
    private $permissions; // Cambiado a un array para manejar múltiples permisos

    public function __construct($permissions)
    {
        // Dividir la cadena de permisos en un array, si hay múltiples
        // Permitimos separadores como ',' o '|' para mayor flexibilidad
        $this->permissions = explode('|', str_replace(' ', '', $permissions));
        // Si solo se pasó un permiso, el array tendrá un solo elemento
    }

    public function handle(Request $request, callable $next)
    {

        // Verificar autenticación
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return Response::json(['status' => 'error'], 401);
            }
            return Response::redirect(APP_URL . 'login');
        }

        if (!empty($this->permissions)) {
            $hasAnyPermission = false;
            foreach ($this->permissions as $permission) {
                if (Auth::can($permission)) {
                    $hasAnyPermission = true;
                    break;
                }
            }

            if (!$hasAnyPermission) {

                if ($request->expectsJson()) {
                    return Response::json(['status' => 'error', 'message' => 'Tu usuario no tiene los permisos requeridos'], 403);
                }
                return Response::redirect(APP_URL . 'error/403');
            }
        }

        return $next($request);
    }
}
