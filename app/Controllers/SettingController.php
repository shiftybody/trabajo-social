<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class SettingController
{

  public function indexView()
  {
    ob_start();
    $titulo = 'Configuración';
    include APP_ROOT . 'app/Views/settings/index.php';
    $contenido = ob_get_clean();
    return Response::html($contenido);
  }

  // Aquí añadirías más métodos para manejar la creación, actualización
  // y eliminación de criterios, que serían llamados vía AJAX.
  // public function store() { ... }
  // public function update() { ... }
  // public function destroy() { ... }
}
