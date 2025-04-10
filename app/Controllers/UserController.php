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

    // Cargar la vista de la lista de usuarios
    ob_start();

    // Variables disponibles en la vista
    $titulo = 'Usuarios';
    include APP_ROOT . 'app/Views/users/index.php';
    $contenido = ob_get_clean();

    return Response::html($contenido);
  }

  public function create(Request $request)
  {
    // Cargar la vista de creación de usuario
    ob_start();
    // Variables disponibles en la vista
    $titulo = 'Crear Usuario';
    include APP_ROOT . 'app/Views/users/create.php';
    $contenido = ob_get_clean();

    return Response::html($contenido);
  }

  public function store(Request $request)
  {

    $data = $request->post();
    $this->userModel->registrarUsuario($data);

    // Redirigir a la lista de usuarios después de crear el usuario
    header('Location: /users');
    exit;
  }
}
