<?php

namespace App\Middlewares;

use App\Services\SessionService;

/**
 * Middleware para verificar que el usuario esté autenticado
 */
class AuthMiddleware
{
    /**
     * Ejecuta el middleware de autenticación
     * 
     * @param callable $next Siguiente middleware o controlador
     * @return mixed
     */
    public function handle($next)
    {
        $sessionService = new SessionService();

        // Verificar si el usuario está autenticado
        if (!$sessionService->isAuthenticated()) {
            // Redirigir al login
            $sessionService->setFlash('error', 'Debe iniciar sesión para acceder a esta página');
            header('Location: /login');
            exit;
        }

        // Verificar si la sesión ha expirado por inactividad
        if (!$sessionService->checkSession()) {
            $sessionService->setFlash('error', 'Su sesión ha expirado por inactividad, por favor inicie sesión nuevamente');
            header('Location: /login');
            exit;
        }

        // Continuar con la cadena de middleware
        return $next();
    }
}
