<?php

namespace App\Controllers\Web;

use App\Models\userModel;
use App\Services\SessionService;
use Phroute\Phroute\RouteCollector;

class AuthController
{
    protected $userModel;
    protected $sessionService;

    public function __construct()
    {
        $this->userModel = new userModel();
        $this->sessionService = new SessionService();
    }

    /**
     * Muestra la vista de login
     * 
     * @return string Vista de login
     */
    public function showLoginForm()
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->sessionService->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }

        // Mostrar la vista de login
        ob_start();
        require_once __DIR__ . '/../../Views/auth/login.php';
        return ob_get_clean();
    }

    /**
     * Procesa el intento de login
     * 
     * @return void
     */
    public function login()
    {
        // Verificar si es una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        // Obtener datos del formulario
        $identificador = isset($_POST['usuario']) ? $_POST['usuario'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] == '1';

        // Validar los datos
        if (empty($identificador) || empty($password)) {
            $this->sessionService->setFlash('error', 'Todos los campos son obligatorios');
            header('Location: /login');
            exit;
        }

        // Intentar autenticar al usuario
        $usuario = $this->userModel->autenticarUsuario($identificador, $password);

        // Si la autenticación falló
        if (!$usuario) {
            $this->sessionService->setFlash('error', 'Credenciales inválidas');
            header('Location: /login');
            exit;
        }

        // Actualizar último acceso
        $this->userModel->actualizarUltimoAcceso($usuario->usuario_id);

        // Iniciar sesión con el flag remember_me
        $this->sessionService->login([
            'id' => $usuario->usuario_id,
            'nombre' => $usuario->usuario_nombre,
            'apellido_paterno' => $usuario->usuario_apellido_paterno,
            'username' => $usuario->usuario_usuario,
            'email' => $usuario->usuario_email,
            'rol' => $usuario->usuario_rol,
            'rol_descripcion' => $usuario->rol_descripcion
        ], $rememberMe);

        // Redirigir al dashboard
        header('Location: /dashboard');
        exit;
    }

    /**
     * Cierra la sesión del usuario
     * 
     * @return void
     */
    public function logout()
    {
        $this->sessionService->logout();
        header('Location: /login');
        exit;
    }
}
