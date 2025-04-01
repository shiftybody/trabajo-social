<?php

/**
 * Middleware de Roles
 * 
 * Verifica si el usuario tiene el rol requerido
 */

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Models\permissionModel;

class RoleMiddleware
{
  /**
   * Rol requerido
   * @var int|array
   */
  private $rol;

  /**
   * Constructor
   * 
   * @param int|array|string $rol ID o array de IDs de roles permitidos
   */
  public function __construct($rol)
  {
    // Convertir a array si es un string con múltiples roles separados por comas
    if (is_string($rol) && strpos($rol, ',') !== false) {
      $this->rol = explode(',', $rol);
      // Limpiar posibles espacios y convertir a enteros
      $this->rol = array_map('trim', $this->rol);
      $this->rol = array_map('intval', $this->rol);
    } else {
      $this->rol = is_array($rol) ? $rol : array(intval($rol));
    }
  }

  /**
   * Procesa la petición
   * 
   * @param Request $request Petición a procesar
   * @param callable $next Siguiente función en la cadena
   * @return mixed Respuesta
   */
  public function handle(Request $request, callable $next)
  {
    // Iniciar sesión si no está iniciada
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    // Verificar si hay sesión activa
    if (!isset($_SESSION[APP_SESSION_NAME]) || empty($_SESSION[APP_SESSION_NAME]['id'])) {
      if ($request->expectsJson()) {
        return Response::json(array(
          'status' => 'error',
          'message' => 'No autenticado'
        ), 401);
      }

      return Response::redirect(APP_URL . 'login');
    }

    // Obtener rol de usuario de la sesión
    $usuarioRol = isset($_SESSION[APP_SESSION_NAME]['rol']) ?
      intval($_SESSION[APP_SESSION_NAME]['rol']) : 0;

    // Verificar si el rol del usuario está en los permitidos
    if (!in_array($usuarioRol, $this->rol)) {
      // Registrar intento de acceso no autorizado
      $permissionModel = new permissionModel();
      $permissionModel->registrarAccion(
        $_SESSION[APP_SESSION_NAME]['id'],
        'Intento de acceso no autorizado por rol',
        'Rol del usuario: ' . $usuarioRol . ', Roles permitidos: ' . implode(', ', $this->rol)
      );

      if ($request->expectsJson()) {
        return Response::json(array(
          'status' => 'error',
          'message' => 'Rol no autorizado para esta acción'
        ), 403);
      }

      // Redirigir a página de acceso denegado
      return Response::redirect(APP_URL . 'error/403');
    }

    // Tiene rol permitido, continuar
    return $next($request);
  }
}
