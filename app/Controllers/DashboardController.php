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
    // Obtener información del usuario actual desde la sesión
    $usuarioId = $_SESSION[APP_SESSION_NAME]['id'];
    $usuario = $this->userModel->obtenerUsuarioPorId($usuarioId);

    // Obtener datos para el dashboard
    $estadisticas = [
      'totalUsuarios' => $this->userModel->seleccionarDatos('contar', 'usuario', 'usuario_id'),
      'ultimoAcceso' => $usuario->usuario_ultima_actividad ?: 'Primer acceso'
    ];

    // Cargar la vista del dashboard
    ob_start();

    // Variables disponibles en la vista
    $titulo = 'Dashboard - Panel de Control';
    $nombreUsuario = $usuario->usuario_nombre . ' ' . $usuario->usuario_apellido_paterno;
    $rolUsuario = $usuario->rol_descripcion;

    include APP_ROOT . 'app/Views/dashboard/index.php';
    $contenido = ob_get_clean();

    return Response::html($contenido);
  }
}
