<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

/**
 * Middleware de Permisos
 */
class PermissionMiddleware
{
    private $permissions;

    public function __construct($permissions)
    {
        // Dividir la cadena de permisos en un array
        $this->permissions = explode('|', str_replace(' ', '', $permissions));
    }

    /**
     * Maneja la verificación de permisos del usuario
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next)
    {

        // Verificar autenticación
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return Response::json(['status' => 'error'], 401);
            }
            return Response::redirect(APP_URL . 'login');
        }

        // Verificar si el usuario tiene permisos
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
