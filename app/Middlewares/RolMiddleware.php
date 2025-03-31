<?php

namespace App\Middlewares;

use App\Services\SessionService;

/**
 * Middleware para verificar que el usuario tenga un rol específico
 */
class RolMiddleware
{
    /**
     * Roles permitidos
     * @var array
     */
    protected $roles;

    /**
     * Constructor
     * 
     * @param int|array $roles ID o array de IDs de roles permitidos
     */
    public function __construct($roles)
    {
        // Asegurar que $roles sea un array
        $this->roles = is_array($roles) ? $roles : [$roles];
    }

    /**
     * Ejecuta el middleware de verificación de rol
     * 
     * @param callable $next Siguiente middleware o controlador
     * @return mixed
     */
    public function handle($next)
    {
        $sessionService = new SessionService();

        // Primero verificar que esté autenticado
        if (!$sessionService->isAuthenticated()) {
            // Verificar si es una solicitud API
            if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
                header('Content-Type: application/json');
                header('HTTP/1.1 401 Unauthorized');
                echo json_encode([
                    'success' => false,
                    'message' => 'Acceso no autorizado',
                    'code' => 401
                ]);
                exit;
            } else {
                // Para solicitudes web, redirigir al login
                $sessionService->setFlash('error', 'Debe iniciar sesión para acceder a esta página');
                header('Location: /login');
                exit;
            }
        }

        // Verificar si tiene el rol requerido
        if (!$sessionService->hasRole($this->roles)) {
            // Verificar si es una solicitud API
            if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
                header('Content-Type: application/json');
                header('HTTP/1.1 403 Forbidden');
                echo json_encode([
                    'success' => false,
                    'message' => 'No tiene permisos suficientes para acceder a este recurso',
                    'code' => 403
                ]);
                exit;
            } else {
                // Para solicitudes web, mostrar página de error o redirigir
                $sessionService->setFlash('error', 'No tiene permisos suficientes para acceder a esta página');
                header('Location: /dashboard');
                exit;
            }
        }

        // Usuario tiene el rol requerido, continuar con la cadena de middleware
        return $next();
    }
}
