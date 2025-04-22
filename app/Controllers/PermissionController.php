<?php

namespace App\Controllers;

use App\Core\Response;
use App\Models\permissionModel;

class PermissionController
{
  private $permissionModel;

  public function __construct()
  {
    $this->permissionModel = new permissionModel();
  }

  public function index()
  {
    // Lógica para mostrar todos los permisos
    $permisos = $this->permissionModel->obtenerTodosPermisos();
    if ($permisos) {
      return Response::json($permisos, 200);
    } else {
      return Response::json(['error' => 'No se encontraron permisos'], 404);
    }
  } 
}
