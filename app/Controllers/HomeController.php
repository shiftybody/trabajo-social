<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\userModel;

class homeController
{

  private $userModel;

  public function __construct()
  {
    $this->userModel = new userModel();
  }

  public function indexView(Request $request)
  {
    ob_start();
    $titulo = 'Inicio';
    include APP_ROOT . 'app/Views/home/index.php';
    $contenido = ob_get_clean();
    return Response::html($contenido);
  }
}
