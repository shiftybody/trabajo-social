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
        // Inicializar Auth si no está inicializado
        Auth::init();

        // Verificar autenticación
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return Response::json(['error' => 'No autenticado'], 401);
            }
            return Response::redirect(APP_URL . 'login');
        }

        if (!empty($this->permissions)) {
            $hasAnyPermission = false;
            foreach ($this->permissions as $permission) {
                if (Auth::can($permission)) {
                    $hasAnyPermission = true;
                    break; // Si tiene al menos uno, ya no necesitamos verificar más
                }
            }

            if (!$hasAnyPermission) {
                if ($request->expectsJson()) {
                    return Response::json(['error' => 'Permiso denegado'], 403);
                }
                error_log('Permiso denegado para el usuario: ' . Auth::user()->id_usuario); // Asegúrate de que Auth::user()->usuario_id sea el correcto
                error_log('Permisos requeridos (cualquiera de): ' . implode(', ', $this->permissions));

                return Response::redirect(APP_URL . 'error/403');
            }
        }

        // Continuar con el siguiente middleware o controlador
        return $next($request);
    }
}