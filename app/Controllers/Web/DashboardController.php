<?php

namespace App\Controllers\Web;

use App\Services\SessionService;

class DashboardController
{
    protected $sessionService;

    public function __construct()
    {
        $this->sessionService = new SessionService();
    }

    /**
     * Muestra la página principal del dashboard
     * 
     * @return string Vista del dashboard
     */
    public function index()
    {
        // Obtener datos del usuario actual
        $userData = $this->sessionService->getUser();

        // Aquí podrías obtener datos adicionales según el rol del usuario
        // como estadísticas, notificaciones, etc.

        // Cargar la vista
        ob_start();
        require_once __DIR__ . '/../../Views/dashboard/index.php';
        return ob_get_clean();
    }
}
