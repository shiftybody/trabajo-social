<?php

namespace App\Middlewares;

use App\Services\TokenService;

class ApiRoleMiddleware
{
  private $roles;

  public function __construct($roles)
  {
    $this->roles = is_array($roles) ? $roles : [$roles];
  }

  /**
   * Verifica que el usuario tenga el rol requerido
   * 
   * @return mixed
   */
  public function handle()
  {
    // Primero verificar autenticación
    $apiAuth = new ApiAuthMiddleware();
    $result = $apiAuth->handle();

    if ($result !== true) {
      return $result; // Si hay error de autenticación, devolver ese error
    }

    // Verificar rol
    $userData = $_REQUEST['auth_user'];

    if (!in_array($userData['rol'], $this->roles)) {
      header('Content-Type: application/json');
      header('HTTP/1.1 403 Forbidden');
      echo json_encode([
        'success' => false,
        'message' => 'No tiene permisos suficientes para este recurso',
        'code' => 403
      ]);
      exit;
    }

    return true;
  }
}
