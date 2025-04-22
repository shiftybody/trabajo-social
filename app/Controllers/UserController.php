<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\userModel;
use Exception;

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

  /**
   * 1. El UserController recibe la solicitud POST
     2. Extrae y valida los datos que no esten vacios,
        que sean del tipo correcto, sanitización,
        tamaños maximos y mínimos.
     3. Llama al UserModel pasándole los datos ya procesados
     4. El UserModel aplica la lógica de negocio y realiza operaciones en la base de datos
     5. El UserController recibe la respuesta del modelo y devuelve la vista correspondiente
   */

  // TODO: verificar porque sale el error mime_content_type(): Empty filename or path in /var/www/html/app/Controllers/UserController.php on line 190
  // TODO: resolver que se tiene que borrar en caso de que la subida al servidor no sea exitosa. 
  public function store(Request $request)
  {
    # --- Obtener datos de la solicitud --- #
    $avatar = $request->FILES('avatar');
    $datos = $request->POST();

    # --- Validar datos de inputs --- #
    $validar = [
      'nombre' => [
        'requerido' => true,
        'min' => 2,
        'max' => 50,
        'formato' => '[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}',
        'sanitizar' => true
      ],
      'apellidoPaterno' => [
        'requerido' => true,
        'min' => 2,
        'max' => 50,
        'formato' => '[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}',
        'sanitizar' => true
      ],
      'apellidoMaterno' => [
        'min' => 2,
        'max' => 50,
        'formato' => '[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}',
        'sanitizar' => true
      ],
      'telefono' => [
        'formato' => '^\d{10}$',
        'sanitizar' => true
      ],
      'correo' => [
        'requerido' => true,
        'formato' => 'email',
        'sanitizar' => true
      ],
      'username' => [
        'requerido' => true,
        'min' => 5,
        'max' => 20,
        'formato' => 'alfanumerico',
        'sanitizar' => true
      ],
      'rol' => [
        'requerido' => true,
        'formato' => 'entero'
      ],
      'password' => [
        'requerido' => true,
        'formato' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}'
      ],
      'password2' => [
        'requerido' => true
      ]
    ];

    $resultado = $this->userModel->validarDatos($datos, $validar);

    // Verificar errores de validación
    if (!empty($resultado['errores'])) {
      return Response::json([
        'status' => 'error',
        'errores' => $resultado['errores']
      ]);
    }

    // Verificar coincidencia de contraseñas
    if ($resultado['datos']['password'] !== $datos['password2']) {
      return Response::json([
        'status' => 'error',
        'errores' => ['password2' => 'Las contraseñas no coinciden']
      ]);
    }

    // si las contraseñas coinciden borrar el campo password2
    unset($resultado['datos']['password2']);

    // Verificar si el correo ya existe
    if ($this->userModel->localizarCorreo($resultado['datos']['correo'])) {
      return Response::json([
        'status' => 'error',
        'errores' => ['correo' => 'Este correo ya está registrado']
      ]);
    }

    // verificar que el username no exista
    if ($this->userModel->localizarUsername($resultado['datos']['username'])) {
      return Response::json([
        'status' => 'error',
        'errores' => ['username' => 'Este nombre de usuario ya está registrado']
      ]);
    }

    # --- Validar del avatar --- #
    if (!empty($avatar['tmp_name'])) {
      $validacionArchivo = $this->userModel->validarArchivo($avatar, [
        'tipos' => ['image/jpeg', 'image/png', 'image/gif'],
        'tamano_max' => 5 * 1024 * 1024, // 5MB
        'extensiones' => ['jpg', 'jpeg', 'png', 'gif']
      ]);

      if (!$validacionArchivo['valido']) {
        return Response::json([
          'status' => 'error',
          'errores' => ['avatar' => $validacionArchivo['mensaje']]
        ]);
      }

      $directorio = APP_ROOT . 'storage/photos/';

      // crear el directorio si no existe
      if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
      }

      // darle normbre a la foto
      $foto = str_ireplace(" ", "_", $datos['nombre']);
      $foto = $foto . "_" . rand(0, 100);

      // colocar extension
      switch (mime_content_type($_FILES['avatar']['tmp_name'])) {
        case "image/jpg":
          $foto = $foto . ".jpg";
          break;
        case "image/jpeg":
          $foto = $foto . ".jpg";
          break;
        case "image/png":
          $foto = $foto . ".png";
          break;
        case "image/gif":
          $foto = $foto . ".gif";
          break;
      }

      // mover la imagen al directorio
      move_uploaded_file($_FILES['avatar']['tmp_name'], $directorio . "/" . $foto);

      // agregar al array de datos
      $resultado['datos']['avatar'] = $foto;
    } else {
      $foto = "";
      $resultado['datos']['avatar'] = $foto;
    }

    # --- Enviar los datos al modelo para que realice el registro --- #

    $registrarUsuario = $this->userModel->registrarUsuario($resultado['datos']);

    if ($registrarUsuario) {
      return Response::json([
        'status' => 'success',
        'mensaje' => 'Usuario registrado correctamente'
      ]);
    } else {
      return Response::json([
        'status' => 'error',
        'errores' => ['general' => 'Error al registrar el usuario']
      ]);
    }
  }
}
