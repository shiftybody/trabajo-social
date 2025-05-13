<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Controllers\LoginController;

class AuthMiddleware {
    private $authController;
    private $publicRoutes = [
        '/login',
        '/error/404',
        '/error/403',
        '/error/401',
        '/error/500'
    ];

    public function __construct() {
        $this->authController = new LoginController();
    }

    public function handle(Request $request, callable $next) {
        // Verificar si la ruta actual es pública
        if (in_array($request->getUri(), $this->publicRoutes)) {
            return $next($request);
        }

        // Verificar estado de la sesión
        if ($this->authController->checkSessionTimeout()) {
            // Si la sesión expiró, intentar restaurar desde cookie
            if ($this->authController->validRememberCookie()) {
                $this->authController->RestoreSessionFromCookie();
            } else {
                // Si no hay cookie válida, redirigir al login
                return Response::redirect(APP_URL . 'login');
            }
        } else {
            // Actualizar timestamp de última actividad
            $this->authController->updateLastActivity();
        }

        // Verificar si el usuario está autenticado
        if (!isset($_SESSION[APP_SESSION_NAME]) || empty($_SESSION[APP_SESSION_NAME]['id'])) {
            return Response::redirect(APP_URL . 'login');
        }

        // Continuar con la siguiente middleware o controlador
        return $next($request);
    }

    /**
     * Agregar una ruta pública
     */
    public function addPublicRoute($route) {
        $this->publicRoutes[] = $route;
    }
}
