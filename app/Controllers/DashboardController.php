<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\userModel;

/**
 * Controlador del Dashboard principal
 */
class DashboardController
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
   * Muestra la página principal del dashboard
   * 
   * @param Request $request Petición actual
   * @return Response Respuesta HTML
   */
  public function index(Request $request)
  {

    ob_start();
    $titulo = 'Panel Principal';
    include APP_ROOT . 'app/Views/dashboard/index.php';
    $contenido = ob_get_clean();

    return Response::html($contenido);
  }
}
