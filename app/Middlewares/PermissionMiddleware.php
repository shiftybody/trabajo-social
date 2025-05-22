<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

/**
 * Middleware de Permisos Simplificado
 */
class PermissionMiddleware
{
    private $permission;

    public function __construct($permission)
    {
        $this->permission = $permission;
    }

    public function handle(Request $request, callable $next)
    {
        // Inicializar Auth si no está inicializado
        Auth::init();

        // Verificar autenticación
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return Response::json(['error' => 'No autenticado'], 401);
            }
            return Response::redirect(APP_URL . 'login');
        }

        // Verificar permiso si se especifica uno
        if ($this->permission && !Auth::can($this->permission)) {
            if ($request->expectsJson()) {
                return Response::json(['error' => 'Permiso denegado'], 403);
            }
            error_log('Permiso denegado para el usuario: ' . Auth::user()->usuario_id);
            error_log($this->permission);

            return Response::redirect(APP_URL . 'error/403');
        }

        // Continuar con el siguiente middleware o controlador
        return $next($request);
    }
}
