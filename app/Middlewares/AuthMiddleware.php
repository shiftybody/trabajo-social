<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

/**
 * Middleware de Autenticación Simplificado
 */
class AuthMiddleware
{
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

        // Continuar con el siguiente middleware o controlador
        return $next($request);
    }
}
