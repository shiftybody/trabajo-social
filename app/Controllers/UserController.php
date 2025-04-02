<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\userModel;

/**
 * Controlador para la gestión de usuarios
 */
class UserController
{
  /**
   * Modelo de usuario
   * @var userModel
   */
  private $userModel;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->userModel = new userModel();
  }

  /**
   * Muestra la lista de usuarios
   * 
   * @param Request $request Petición actual
   * @return Response Respuesta HTML
   */
  public function index(Request $request)
  {
    // Obtener la lista de usuarios desde el modelo
    $usuarios = $this->userModel->obtenerUsuarios();

    // Cargar la vista de la lista de usuarios
    ob_start();

    // Variables disponibles en la vista
    $titulo = 'Lista de Usuarios';
    include APP_ROOT . 'app/Views/users/index.php';
    $contenido = ob_get_clean();

    return Response::html($contenido);
  }
}
