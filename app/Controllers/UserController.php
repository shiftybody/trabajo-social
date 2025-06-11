<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\userModel;
use App\Utils\ImageUtils;
use Exception;

class UserController
{

  private $userModel;

  public function __construct()
  {
    $this->userModel = new userModel();
  }

  public function indexView()
  {

    ob_start();
    $titulo = 'Usuarios';
    include APP_ROOT . 'app/Views/users/index.php';
    $contenido = ob_get_clean();

    return Response::html($contenido);
  }

  public function createView()
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
    $usuario = $this->userModel->getUserById($id);
    include APP_ROOT . 'app/Views/users/edit.php';
    $contenido = ob_get_clean();

    return Response::html($contenido);
  }

  public function getAllUsers()
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
        'message' => 'Error al obtener los usuarios: ' . $e->getMessage()
      ]);
    }
  }

  public function getUserById(Request $request)
  {
    try {
      $id = $request->param('id');

      if (!$id || !is_numeric($id)) {
        return Response::json([
          'status' => 'error',
          'message' => 'ID de usuario inválido'
        ], 400);
      }

      $usuario = $this->userModel->getUserById($id);

      if (!$usuario) {
        return Response::json([
          'status' => 'error',
          'message' => 'Usuario no encontrado'
        ], 404);
      }


      $usuarioDetalles = [
        'id' => $usuario->usuario_id,
        'nombre_completo' => trim($usuario->usuario_nombre . ' ' . $usuario->usuario_apellido_paterno . ' ' . $usuario->usuario_apellido_materno),
        'nombre' => $usuario->usuario_nombre,
        'apellido_paterno' => $usuario->usuario_apellido_paterno,
        'apellido_materno' => $usuario->usuario_apellido_materno,
        'usuario' => $usuario->usuario_usuario,
        'email' => $usuario->usuario_email,
        'telefono' => $usuario->usuario_telefono ?: 'No especificado',
        'rol' => $usuario->rol_nombre,
        'rol_id' => $usuario->usuario_rol,
        'estado' => $usuario->usuario_estado == 1 ? 'Activo' : 'Inactivo',
        'estado_id' => $usuario->usuario_estado,
        'avatar' => $usuario->usuario_avatar,
        'fecha_creacion' => $usuario->usuario_fecha_creacion ? date('d/m/Y H:i', strtotime($usuario->usuario_fecha_creacion)) : 'No disponible',
        'ultima_modificacion' => $usuario->usuario_ultima_modificacion ? date('d/m/Y H:i', strtotime($usuario->usuario_ultima_modificacion)) : 'No disponible',
        'ultimo_acceso' => $usuario->usuario_ultimo_acceso ? date('d/m/Y H:i', strtotime($usuario->usuario_ultimo_acceso)) : 'Nunca'
      ];

      return Response::json([
        'status' => 'success',
        'data' => $usuarioDetalles
      ]);
    } catch (Exception $e) {
      error_log("Error en getUserById: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  public function store(Request $request)
  {

    $avatar = $request->files('avatar');
    $datos = $request->post();

    error_log(print_r($datos, true));
    error_log(print_r($avatar, true));

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

    // Si hay errores de formato, devolverlos
    if (!empty($resultado['errores'])) {
      return Response::json([
        'status' => 'error',
        'errores' => $resultado['errores']
      ]);
    }

    // Si las contraseñas no coinciden, devolver error
    if ($resultado['datos']['password'] !== $datos['password2']) {
      return Response::json([
        'status' => 'error',
        'errores' => ['password2' => 'Las contraseñas no coinciden']
      ]);
    }

    // Si coincidieron eliminar el campo password2
    unset($resultado['datos']['password2']);

    // Verificar si el correo ya existe
    if ($this->userModel->localizarCorreo($resultado['datos']['correo'])) {
      return Response::json([
        'status' => 'error',
        'errores' => ['correo' => 'Este correo ya está registrado']
      ]);
    }

    // si el nombre de usuario ya existe devolver error
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
          'errores' => ['avatar' => $validacionArchivo['message']]
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
            require_once APP_ROOT . 'app/Utils/ImageUtils.php';
          }

          // Crear miniatura
          \App\Utils\ImageUtils::createThumbnail(
            $rutaOriginal,  // Origen: imagen original
            $rutaThumbnail, // Destino: carpeta de miniaturas
            200             // Tamaño máximo
          );
        } catch (\Exception $e) {
          error_log("Error al crear miniatura: " . $e->getMessage());
          // Si hay un error al crear la miniatura, eliminar la imagen original también
          if (file_exists($rutaOriginal)) {
            unlink($rutaOriginal);
          }
          return Response::json([
            'status' => 'error',
            'errores' => ['avatar' => 'Error al procesar la imagen (miniatura): ' . $e->getMessage()]
          ]);
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
        'message' => 'Usuario registrado correctamente'
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
    $avatar = $request->files('avatar');
    $datos = $request->post();

    // Validar que el usuario existe
    $usuario = $this->userModel->getUserById($id);
    if (!$usuario) {
      return Response::json([
        'status' => 'error',
        'message' => 'Usuario no encontrado'
      ], 404);
    }

    // si los datos subidos son iguales a los del usuario no hacer nada
    if (
      $datos['nombre'] == $usuario->usuario_nombre &&
      $datos['apellidoPaterno'] == $usuario->usuario_apellido_paterno &&
      $datos['apellidoMaterno'] == $usuario->usuario_apellido_materno &&
      $datos['telefono'] == $usuario->usuario_telefono &&
      $datos['correo'] == $usuario->usuario_email &&
      $datos['username'] == $usuario->usuario_usuario &&
      $datos['rol'] == $usuario->usuario_rol &&
      $datos['estado'] == $usuario->usuario_estado &&
      (!isset($datos['change_password']) || ($datos['change_password'] == 0 && empty($datos['password'])))
    ) {
      return Response::json([
        'status' => 'success',
        'message' => 'No se realizaron cambios en el usuario'
      ]);
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

    // evitar desactivar el usuario a si mismo    
    if (isset($_SESSION[APP_SESSION_NAME]['id']) && $_SESSION[APP_SESSION_NAME]['id'] == $id && $resultado['datos']['estado'] == 0) {
      return Response::json([
        'status' => 'error',
        'message' => 'No puedes desactivar tu propia cuenta'
      ]);
    }

    // evitar que se cambie de rol si es el ultimo administrador y si el rol es diferente a 1 (administrador)
    if ($this->userModel->esUltimoAdministrador($id) && $resultado['datos']['rol'] != 1) {
      return Response::json([
        'status' => 'error',
        'message' => 'No puedes cambiar de rol a un administrador si es el ultimo administrador'
      ]);
    }

    // --- Manejo del Avatar --- //
    $nombreArchivo = $usuario->usuario_avatar; // Mantener avatar actual por defecto
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
          'errores' => ['avatar' => $validacionArchivo['message']]
        ]);
      }
    }

    if ($archivoSubido) {
      $baseDir = APP_ROOT . 'public/photos';
      $originalDir = $baseDir . '/original';
      $thumbnailDir = $baseDir . '/thumbnail';

      // Crear directorios si no existen
      foreach ([$baseDir, $originalDir, $thumbnailDir] as $dir) {
        if (!file_exists($dir)) {
          mkdir($dir, 0777, true);
        }
      }

      $nombreBase = str_ireplace(" ", "_", isset($resultado['datos']['nombre']) ? $resultado['datos']['nombre'] : 'user');
      $nombreBase = $nombreBase . "_" . uniqid();

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
          return Response::json([
            'status' => 'error',
            'errores' => ['avatar' => 'Formato de imagen no soportado: ' . $mimeType]
          ]);
      }

      $nombreArchivoNuevo = $nombreBase . $extension;
      $rutaOriginalNueva = $originalDir . '/' . $nombreArchivoNuevo;
      $rutaThumbnailNueva = $thumbnailDir . '/' . $nombreArchivoNuevo;

      if (!move_uploaded_file($avatar['tmp_name'], $rutaOriginalNueva)) {
        return Response::json([
          'status' => 'error',
          'errores' => ['avatar' => 'Error al guardar la nueva imagen']
        ]);
      }

      if (file_exists($rutaOriginalNueva)) {
        try {
          ImageUtils::createThumbnail(
            $rutaOriginalNueva,
            $rutaThumbnailNueva,
            200
          );
        } catch (\Exception $e) {
          error_log("Error al crear miniatura para actualización: " . $e->getMessage());
          if (file_exists($rutaOriginalNueva)) {
            unlink($rutaOriginalNueva);
          }
          return Response::json([
            'status' => 'error',
            'errores' => ['avatar' => 'Error al procesar la nueva imagen (miniatura): ' . $e->getMessage()]
          ]);
        }
      }

      // Si se subió una nueva imagen y se procesó correctamente, eliminar la anterior (si no es default)
      if ($usuario->usuario_avatar && $usuario->usuario_avatar !== 'default.jpg') {
        $rutaOriginalAntigua = $originalDir . '/' . $usuario->usuario_avatar;
        $rutaThumbnailAntigua = $thumbnailDir . '/' . $usuario->usuario_avatar;
        if (file_exists($rutaOriginalAntigua)) {
          unlink($rutaOriginalAntigua);
        }
        if (file_exists($rutaThumbnailAntigua)) {
          unlink($rutaThumbnailAntigua);
        }
      }
      $nombreArchivo = $nombreArchivoNuevo; // Actualizar al nuevo nombre de archivo
    }

    $resultado['datos']['avatar'] = $nombreArchivo; // Asignar el avatar (nuevo o existente)


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

    // Si se cambió la contraseña, hashearla y eliminar password2
    if ($datos['change_password'] == 1) {
      if ($resultado['datos']['password'] !== $datos['password2']) {
        return Response::json([
          'status' => 'error',
          'errores' => ['password2' => 'Las contraseñas no coinciden']
        ]);
      }
      unset($resultado['datos']['password2']);
    } else {
      // Si no se cambia la contraseña, eliminar los campos para no actualizarlos
      unset($resultado['datos']['password']);
      unset($resultado['datos']['password2']);
    }
    unset($resultado['datos']['change_password']); // Eliminar el campo auxiliar

    // Actualizar el usuario
    $actualizar = $this->userModel->actualizarUsuario($id, $resultado['datos']);

    if ($actualizar) {
      return Response::json([
        'status' => 'success',
        'message' => 'Usuario actualizado correctamente',
        'redirect' =>  APP_URL . 'users'
      ]);
    } else {
      return Response::json([
        'status' => 'error',
        'errores' => ['general' => 'Error al actualizar el usuario']
      ]);
    }
  }

  public function resetPassword(Request $request)
  {
    $id = $request->param('id');
    $datos = $request->POST();
    // imprimir $datos para debuga
    error_log(print_r($datos, true));

    // Validar que el usuario existe
    $usuario = $this->userModel->getUserById($id);
    if (!$usuario) {
      return Response::json([
        'status' => 'error',
        'message' => 'Usuario no encontrado'
      ], 404);
    }

    $validar = [
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

    // Verificar que las contraseñas coincidan
    if ($resultado['datos']['password'] !== $datos['password2']) {
      return Response::json([
        'status' => 'error',
        'errores' => ['password2' => 'Las contraseñas no coinciden']
      ]);
    }

    // Actualizar solo la contraseña
    $actualizar = $this->userModel->actualizarUsuario($id, [
      'password' => $resultado['datos']['password']
    ]);

    if ($actualizar) {
      return Response::json([
        'status' => 'success',
        'message' => 'Contraseña reseteada correctamente. Efectos aplicados en el siguiente inicio de sesión.'
      ]);
    } else {
      return Response::json([
        'status' => 'error',
        'message' => 'Error al resetear la contraseña'
      ]);
    }
  }

  public function changeStatus(Request $request)
  {
    $id = $request->param('id');
    $datos = $request->POST();

    // Log para debugging
    error_log("Cambio de estado - ID: " . $id . " - Datos: " . print_r($datos, true));

    // Validar que el usuario existe
    $usuario = $this->userModel->getUserById($id);
    if (!$usuario) {
      return Response::json([
        'status' => 'error',
        'message' => 'Usuario no encontrado'
      ], 404);
    }

    // Validar que se envió el nuevo estado
    if (!isset($datos['estado'])) {
      return Response::json([
        'status' => 'error',
        'errores' => ['estado' => 'El nuevo estado es requerido']
      ]);
    }

    $nuevoEstado = $datos['estado'];

    // Validar que el nuevo estado sea válido (0 o 1)
    if (!in_array($nuevoEstado, ['0', '1', 0, 1])) {
      return Response::json([
        'status' => 'error',
        'errores' => ['estado' => 'El estado debe ser 0 (inactivo) o 1 (activo)']
      ]);
    }

    // Convertir a entero para consistencia
    $nuevoEstado = (int)$nuevoEstado;
    $estadoActual = (int)$usuario->usuario_estado;

    // Verificar si realmente hay un cambio
    if ($estadoActual === $nuevoEstado) {
      $estadoTexto = $nuevoEstado === 1 ? 'activo' : 'inactivo';
      return Response::json([
        'status' => 'error',
        'message' => "El usuario ya se encuentra en estado {$estadoTexto}"
      ]);
    }

    // Prevenir desactivar al propio usuario (medida de seguridad)
    if (isset($_SESSION[APP_SESSION_NAME]['id']) && $_SESSION[APP_SESSION_NAME]['id'] == $id && $nuevoEstado === 0) {
      return Response::json([
        'status' => 'error',
        'message' => 'No puedes desactivar tu propia cuenta'
      ]);
    }

    // Actualizar el estado del usuario
    $actualizar = $this->userModel->actualizarUsuario($id, [
      'estado' => $nuevoEstado
    ]);

    if ($actualizar) {
      $estadoTexto = $nuevoEstado === 1 ? 'activado' : 'desactivado';
      $nombreCompleto = trim($usuario->usuario_nombre . ' ' . $usuario->usuario_apellido_paterno . ' ' . $usuario->usuario_apellido_materno);

      return Response::json([
        'status' => 'success',
        'message' => "El usuario {$nombreCompleto} ha sido {$estadoTexto} correctamente"
      ]);
    } else {
      return Response::json([
        'status' => 'error',
        'message' => 'Error al cambiar el estado del usuario'
      ]);
    }
  }

  public function delete(Request $request)
  {

    $id = $request->param('id');

    // Verificar que el usuario exista
    $usuario = $this->userModel->getUserById($id);
    if (!$usuario) {
      return Response::json([
        'status' => 'error',
        'message' => 'Usuario no encontrado'
      ], 404);
    }

    // verificar que no sea el ultimo usuario administrador
    if ($this->userModel->esUltimoAdministrador($id)) {
      return Response::json([
        'status' => 'error',
        'message' => 'No se puede eliminar el último usuario administrador'
      ]);
    }

    // Eliminar el usuario
    $eliminar = $this->userModel->eliminarUsuario($id);

    // Eliminar el avatar del usuario si no es el default
    if ($usuario->usuario_avatar && $usuario->usuario_avatar !== 'default.jpg') {
      $baseDir = APP_ROOT . 'public/photos';
      $originalDir = $baseDir . '/original';
      $thumbnailDir = $baseDir . '/thumbnail';

      $rutaOriginal = $originalDir . '/' . $usuario->usuario_avatar;
      $rutaThumbnail = $thumbnailDir . '/' . $usuario->usuario_avatar;

      // Eliminar archivos si existen
      if (file_exists($rutaOriginal)) {
        unlink($rutaOriginal);
      }
      if (file_exists($rutaThumbnail)) {
        unlink($rutaThumbnail);
      }
    }

    if ($eliminar) {
      return Response::json([
        'status' => 'success',
        'message' => 'Usuario eliminado correctamente'
      ]);
    } else {
      return Response::json([
        'status' => 'error',
        'message' => 'Error al eliminar el usuario'
      ]);
    }
  }
}
