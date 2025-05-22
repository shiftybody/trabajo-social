<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

/**
 * Middleware de Roles Simplificado
 */
class RoleMiddleware
{
    private $roles;

    public function __construct($roles)
    {
        // Convertir string separado por comas a array
        if (is_string($roles) && strpos($roles, ',') !== false) {
            $this->roles = array_map('intval', explode(',', $roles));
        } elseif (is_string($roles)) {
            $this->roles = [intval($roles)];
        } else {
            $this->roles = is_array($roles) ? $roles : [$roles];
        }
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

        // Verificar roles si se especifican
        if (!empty($this->roles) && !Auth::hasRole($this->roles)) {
            if ($request->expectsJson()) {
                return Response::json(['error' => 'Acceso denegado'], 403);
            }
            error_log('Acceso denegado: No tienes los roles requeridos.');
            error_log('Role requerido' .  $this->roles);
            return Response::redirect(APP_URL . 'error/403');
        }

        // Continuar con el siguiente middleware o controlador
        return $next($request);
    }
}
