<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

/**
 * Middleware de Autenticación Mejorado
 * 
 * Verifica:
 * 1. Que el usuario esté autenticado
 * 2. Que el usuario esté activo (estado = 1)
 * 3. Refresca la actividad de la sesión si todo está correcto (excepto para ciertas rutas)
 */
class AuthMiddleware
{
    /**
     * Rutas que NO deben refrescar automáticamente la actividad de sesión
     * @var array
     */
    private $rutasNoRefresh = [
        '/session/status',
        '/session/refresh'
    ];

    /**
     * Maneja la verificación de autenticación y estado del usuario
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next)
    {
        // Verificar autenticación básica
        if (!Auth::check()) {
            return $this->handleUnauthenticated($request);
        }

        // Verificar estado del usuario
        $user = Auth::user();
        if (!$user) {
            // Usuario no encontrado, cerrar sesión
            Auth::logout();
            return $this->handleUnauthenticated($request, 'Usuario no encontrado');
        }

        // Verificar si el usuario está activo
        if (!$this->isUserActive($user)) {
            // Registrar intento de acceso de usuario inactivo
            error_log("Intento de acceso de usuario inactivo: Usuario ID {$user->usuario_id} ({$user->usuario_usuario})");

            // Cerrar sesión del usuario inactivo
            Auth::logout();

            return $this->handleInactiveUser($request);
        }

        // Verificar si esta ruta debe refrescar automáticamente la actividad
        $uri = $request->getUri();
        $debeRefrescar = !in_array($uri, $this->rutasNoRefresh);
        error_log("Ruta: {$uri} - Debe refrescar: " . ($debeRefrescar ? 'Sí' : 'No')); // Agrega este log para debuga

        // Solo refrescar actividad si no está en la lista de exclusión
        if ($debeRefrescar) {
            Auth::refreshSessionActivity();
        }

        // Continuar con el siguiente middleware o controlador
        return $next($request);
    }

    /**
     * Verifica si el usuario está activo
     *
     * @param object $user Objeto del usuario
     * @return bool
     */
    private function isUserActive($user)
    {
        // Verificar que el usuario tenga el campo de estado
        if (!isset($user->usuario_estado)) {
            error_log("Campo usuario_estado no encontrado para el usuario ID: " . $user->usuario_id);
            return false;
        }

        // El usuario debe tener estado = 1 para estar activo
        return (int)$user->usuario_estado === 1;
    }

    /**
     * Maneja usuarios no autenticados
     *
     * @param Request $request
     * @param string|null $message Mensaje adicional para logs
     * @return Response
     */
    private function handleUnauthenticated(Request $request, $message = null)
    {
        if ($message) {
            error_log("AuthMiddleware: {$message}");
        }

        if ($request->expectsJson()) {
            return Response::json([
                'error' => 'No autenticado',
                'code' => 'UNAUTHENTICATED'
            ], 401);
        }

        return Response::redirect(APP_URL . 'login');
    }

    /**
     * Maneja usuarios inactivos
     *
     * @param Request $request
     * @return Response
     */
    private function handleInactiveUser(Request $request)
    {
        if ($request->expectsJson()) {
            return Response::json([
                'error' => 'Cuenta deshabilitada. Contacte al administrador.',
                'code' => 'ACCOUNT_DISABLED'
            ], 403);
        }

        // Para requests web, redirigir al login con message de cuenta deshabilitada
        $loginUrl = APP_URL . 'login?account_disabled=1';
        return Response::redirect($loginUrl);
    }
}
