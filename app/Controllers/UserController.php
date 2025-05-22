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

  public function indexView(Request $request)
  {

    ob_start();
    $titulo = 'Usuarios';
    include APP_ROOT . 'app/Views/users/index.php';
    $contenido = ob_get_clean();

    return Response::html($contenido);
  }

  public function createView(Request $request)
  {

    ob_start();
    $titulo = 'Crear Usuario';
    include APP_ROOT . 'app/Views/users/create.php';
    $contenido = ob_get_clean();

    return Response::html($contenido);
  }

  public function editView(Request $request)
  {
    ob_start();
    $id = $request->param('id');
    $titulo = 'Editar Usuario';
    $usuario = $this->userModel->obtenerUsuarioPorId($id);
    include APP_ROOT . 'app/Views/users/edit.php';
    $contenido = ob_get_clean();

    return Response::html($contenido);
  }

  public function getAllUsers(Request $request)
  {
    try {
      $usuarios = $this->userModel->obtenerTodosUsuarios();
      return Response::json([
        'status' => 'success',
        'data' => $usuarios
      ]);
    } catch (Exception $e) {
      return Response::json([
        'status' => 'error',
        'mensaje' => 'Error al obtener los usuarios: ' . $e->getMessage()
      ]);
    }
  }

  public function store(Request $request)
  {

    $avatar = $request->FILES('avatar');
    $datos = $request->PUT();

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

    // Valor por defecto para avatar
    $resultado['datos']['avatar'] = "default.jpg";

    // Verificar si se subió un archivo y es válido
    $archivoSubido = false;
    if ($avatar && isset($avatar['tmp_name']) && !empty($avatar['tmp_name']) && file_exists($avatar['tmp_name'])) {
      $archivoSubido = true;

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
    }

    // Solo procesar el archivo si se subió uno válido
    if ($archivoSubido) {
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
      $mimeType = mime_content_type($avatar['tmp_name']);

      switch ($mimeType) {
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
            'errores' => ['avatar' => 'Formato de imagen no soportado: ' . $mimeType]
          ]);
      }

      // Nombre de archivo completo con extensión
      $nombreArchivo = $nombreBase . $extension;

      // Rutas completas para archivo original y miniatura
      $rutaOriginal = $originalDir . '/' . $nombreArchivo;
      $rutaThumbnail = $thumbnailDir . '/' . $nombreArchivo;

      // Mover la imagen subida al directorio de originales
      if (!move_uploaded_file($avatar['tmp_name'], $rutaOriginal)) {
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


  public function update(Request $request)
  {
    $id = $request->param('id');
    $avatar = $request->FILES('avatar');
    $datos = $request->put();

    error_log(print_r($datos, true));

    // Validar que el usuario existe
    $usuario = $this->userModel->obtenerUsuarioPorId($id);
    if (!$usuario) {
      return Response::json([
        'status' => 'error',
        'mensaje' => 'Usuario no encontrado'
      ], 404);
    }

    // Definir reglas de validación
    $validar = [
      'nombre' => [
        'min' => 2,
        'max' => 50,
        'formato' => '[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}',
        'sanitizar' => true
      ],
      'apellidoPaterno' => [
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
        'formato' => 'email',
        'sanitizar' => true
      ],
      'username' => [
        'min' => 5,
        'max' => 20,
        'formato' => 'alfanumerico',
        'sanitizar' => true
      ],
      'estado' => [
        'requerido' => true,
      ],
      'rol' => [
        'formato' => 'entero'
      ],
    ];

    // si change_password es 0 no validar el password, si es 1 validar el password
    if ($datos['change_password'] == 1) {
      $validar['password'] = [
        'formato' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}'
      ];
      $validar['password2'] = [
        'requerido' => true
      ];
    }

    // Validar los datos
    $resultado = $this->userModel->validarDatos($datos, $validar);

    // Verificar errores de validación
    if (!empty($resultado['errores'])) {
      return Response::json([
        'status' => 'error',
        'errores' => $resultado['errores']
      ]);
    }

    // Verificar si se está actualizando el correo y si ya existe
    if (isset($resultado['datos']['correo']) && $resultado['datos']['correo'] !== $usuario->usuario_email) {
      if ($this->userModel->localizarCorreo($resultado['datos']['correo'])) {
        return Response::json([
          'status' => 'error',
          'errores' => ['correo' => 'Este correo ya está registrado']
        ]);
      }
    }

    // Verificar si se está actualizando el nombre de usuario y si ya existe
    if (isset($resultado['datos']['username']) && $resultado['datos']['username'] !== $usuario->usuario_usuario) {
      if ($this->userModel->localizarUsername($resultado['datos']['username'])) {
        return Response::json([
          'status' => 'error',
          'errores' => ['username' => 'Este nombre de usuario ya está registrado']
        ]);
      }
    }

    // Actualizar el usuario
    $actualizar = $this->userModel->actualizarUsuario($id, $resultado['datos']);

    if ($actualizar) {
      return Response::json([
        'status' => 'success',
        'mensaje' => 'Usuario actualizado correctamente'
      ]);
    } else {
      return Response::json([
        'status' => 'error',
        'errores' => ['general' => 'Error al actualizar el usuario']
      ]);
    }
  }
}
