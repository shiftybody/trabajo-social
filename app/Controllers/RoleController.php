<?php

namespace App\Controllers;

use App\Core\Response;
use App\Models\roleModel;

/**
 * Controlador para la gestión de usuarios
 */
class RoleController
{
  /**
   * Modelo de usuario
   * @var userModel
   */
  private $roleModel;

  public function __construct()
  {
    $this->roleModel = new roleModel();
  }

  public function index() {}

  public function create() {}

  public function getAllRoles()
  {
    $rols = $this->roleModel->obtenerTodosRoles();
    if ($rols) {
      return Response::json($rols, 200);
    } else {
      return Response::json(['error' => 'No se encontraron roles'], 404);
    }
  }
}
