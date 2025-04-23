<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\userModel;
use Exception;

class UserController
{

  private $userModel;

  public function __construct()
  {
    $this->userModel = new userModel();
  }

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

      // Definir directorios para imágenes originales y miniaturas
      $baseDir = APP_ROOT . 'public/photos';
      $originalDir = $baseDir . '/original';
      $thumbnailDir = $baseDir . '/thumbnail';

      // Crear los directorios si no existen
      foreach ([$baseDir, $originalDir, $thumbnailDir] as $dir) {
        if (!file_exists($dir)) {
          mkdir($dir, 0777, true);
        }
      }

      // Darle nombre a la foto (sanitizado y único)
      $nombreBase = str_ireplace(" ", "_", $datos['nombre']);
      $nombreBase = $nombreBase . "_" . uniqid(); // Usar uniqid() en lugar de rand para evitar colisiones

      // Determinar la extensión según el tipo MIME
      $extension = "";
      switch (mime_content_type($_FILES['avatar']['tmp_name'])) {
        case "image/jpg":
        case "image/jpeg":
          $extension = ".jpg";
          break;
        case "image/png":
          $extension = ".png";
          break;
        case "image/gif":
          $extension = ".gif";
          break;
        default:
          // Manejar tipos no soportados
          return Response::json([
            'status' => 'error',
            'errores' => ['avatar' => 'Formato de imagen no soportado']
          ]);
      }

      // Nombre de archivo completo con extensión
      $nombreArchivo = $nombreBase . $extension;

      // Rutas completas para archivo original y miniatura
      $rutaOriginal = $originalDir . '/' . $nombreArchivo;
      $rutaThumbnail = $thumbnailDir . '/' . $nombreArchivo;

      // Mover la imagen subida al directorio de originales
      if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $rutaOriginal)) {
        return Response::json([
          'status' => 'error',
          'errores' => ['avatar' => 'Error al guardar la imagen']
        ]);
      }

      // Crear la miniatura solo si la carga original fue exitosa
      if (file_exists($rutaOriginal)) {
        try {
          // Asegurarse de que la clase ImageUtils está disponible
          if (!class_exists('\App\Utils\ImageUtils')) {
            require_once APP_ROOT . 'Utils/ImageUtils.php';
          }

          // Crear miniatura
          \App\Utils\ImageUtils::createThumbnail(
            $rutaOriginal,  // Origen: imagen original
            $rutaThumbnail, // Destino: carpeta de miniaturas
            200             // Tamaño máximo
          );
        } catch (\Exception $e) {
          error_log("Error al crear miniatura: " . $e->getMessage());
          // Continuar aunque falle la miniatura, al menos tenemos la original
        }
      }

      // Agregar al array de datos (guardar ruta relativa para la base de datos)
      $resultado['datos']['avatar'] = $nombreArchivo;

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
    } else {
      // Si no se subió un avatar, solo registrar los datos sin la imagen
      $resultado['datos']['avatar'] = "default.jpg"; // O cualquier valor por defecto que desees

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
}
