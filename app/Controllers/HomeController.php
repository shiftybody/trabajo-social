<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\userModel;

/**
 * Controlador del home principal
 */
class homeController
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
   * Muestra la página principal del home
   * 
   * @param Request $request Petición actual
   * @return Response Respuesta HTML
   */
  public function index(Request $request)
  {
    ob_start();
    $titulo = 'Inicio';
    include APP_ROOT . 'app/Views/home/index.php';
    $contenido = ob_get_clean();
    return Response::html($contenido);
  }
}
