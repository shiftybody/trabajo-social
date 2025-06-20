<?php

namespace App\Models;

use \PDO;
use \PDOException;
use \Exception;

/**
 * Class mainModel
 * @package app\models
 *
 * Esta clase maneja la conexión a la base de datos y proporciona métodos para 
 * - ejecutar consultas SQL.
 * - limpiar cadenas,
 * - validar datos y 
 * - encripta & desencriptar cadenas.
 */
class mainModel
{

  private $server = MYSQL_SERVER;
  private $port = MYSQL_PORT;
  private $db = MYSQL_DATABASE;
  private $user = MYSQL_USER;
  private $pass = MYSQL_ROOT_PASSWORD;

  protected $lastInsertId = 0;

  /**
   * Conecta a la base de datos MySQL utilizando PDO.
   * 
   * @return PDO
   * @throws Exception Si hay un error de conexión.
   * 
   */
  protected function conectarBD()
  {
    try {
      $conexion = new PDO(
        "mysql:host=" . $this->server . ";port=" . $this->port . ";dbname=" . $this->db,
        $this->user,
        $this->pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
      );
      $conexion->exec("SET CHARACTER SET utf8");
      return $conexion;
    } catch (PDOException $e) {
      error_log("Error de conexión: " . $e->getMessage());
      throw new Exception("Error al conectar con la base de datos");
    }
  }

  /**
   * Ejecuta una consulta SQL preparada.
   * 
   * @param string $consulta La consulta SQL a ejecutar.
   * @param array $parametros Los parámetros a asociar a la consulta.
   * @return PDOStatement El resultado de la consulta.
   * @trows Exception Si hay un error al ejecutar la consulta.
   * 
   */
  protected function ejecutarConsulta($consulta, $parametros = [])
  {
    try {
      $conexion = $this->conectarBD();
      $sql = $conexion->prepare($consulta);

      // Asociar parámetros, si los hay
      foreach ($parametros as $clave => $valor) {
        $sql->bindValue($clave, $valor);
      }

      $sql->execute();
      return $sql;
    } catch (Exception $e) {
      error_log("Error en ejecutarConsulta: " . $e->getMessage());
      throw new Exception("Error al ejecutar la consulta");
    }
  }



  /**
   * Inserta datos en una tabla de la base de datos.
   * 
   * @param string $tabla El nombre de la tabla.
   * @param array $datos Los datos a insertar.
   * @return PDOStatement El resultado de la consulta.
   */
  protected function insertarDatos($tabla, $datos)
  {
    try {
      // Usar una sola conexión para toda la operación
      $conexion = $this->conectarBD();

      // Arrays para almacenar nombres de campos y marcadores
      $campos = array_keys($datos);
      $marcadores = [];
      $parametros = [];

      // Crear marcadores y parámetros
      foreach ($campos as $campo) {
        $marcador = ":{$campo}";
        $marcadores[] = $marcador;
        $parametros[$marcador] = $datos[$campo];
      }

      // Construir la consulta
      $query = "INSERT INTO $tabla (" . implode(", ", $campos) .
        ") VALUES (" . implode(", ", $marcadores) . ")";

      // Preparar y ejecutar
      $sql = $conexion->prepare($query);

      // Asociar parámetros
      foreach ($parametros as $clave => $valor) {
        $sql->bindValue($clave, $valor);
      }

      $sql->execute();

      // Almacenar el lastInsertId para uso posterior
      $this->lastInsertId = $conexion->lastInsertId();

      return $sql;
    } catch (Exception $e) {
      error_log("Error en insertarDatos: " . $e->getMessage());
      throw new Exception("Error al guardar datos en la tabla $tabla");
    }
  }

  /**
   * Obtiene el ID del último registro insertado
   * 
   * @return int ID del último registro insertado
   */
  protected function getLastInsertId()
  {
    return (int)$this->lastInsertId;
  }

  /**
   * Selecciona datos de una tabla de la base de datos.
   * 
   * @param string $tipo El tipo de consulta (unico, normal, contar).
   * @param string $tabla El nombre de la tabla.
   * @param string $campo El campo a seleccionar.
   * @param mixed $id El valor del campo a seleccionar (opcional).
   * @return PDOStatement El resultado de la consulta.
   * 
   */
  public function seleccionarDatos($tipo, $tabla, $campo, $id = null, $orden = null)
  {
    switch ($tipo) {
      case "unico":
        $query = "SELECT * FROM $tabla WHERE $campo = :id";
        return $this->ejecutarConsulta($query, [':id' => $id]);
      case "normal":
        $query = "SELECT $campo FROM $tabla";
        if ($orden) {
          $query .= " ORDER BY $orden";
        }
        return $this->ejecutarConsulta($query, []);
      case "contar":
        $query = "SELECT COUNT($campo) AS total FROM $tabla";
        return $this->ejecutarConsulta($query, [])->fetchColumn();
      default:
        throw new Exception("Tipo de consulta no válido");
    }
  }

  /**
   * Actualiza datos en una tabla de la base de datos.
   * 
   * @param string $tabla El nombre de la tabla.
   * @param array $datos Los datos a actualizar.
   * @param array $condicion La condición para la actualización.
   * @return PDOStatement El resultado de la consulta.
   * 
   */
  protected function actualizarDatos($tabla, $datos, $condicion)
  {
    try {
      // Arrays para construcción de consulta
      $actualizaciones = [];
      $parametros = [];

      // Construir array de asignaciones
      foreach ($datos as $dato) {
        $actualizaciones[] = $dato["campo_nombre"] . "=" . $dato["campo_marcador"];
        $parametros[$dato["campo_marcador"]] = $dato["campo_valor"];
      }

      // Agregar condición
      $query = "UPDATE $tabla SET " . implode(", ", $actualizaciones) .
        " WHERE " . $condicion["condicion_campo"] . "=" . $condicion["condicion_marcador"];

      // Agregar valor de condición a parámetros
      $parametros[$condicion["condicion_marcador"]] = $condicion["condicion_valor"];

      // Ejecutar consulta
      return $this->ejecutarConsulta($query, $parametros);
    } catch (Exception $e) {
      error_log("Error en actualizarDatos: " . $e->getMessage());
      throw new Exception("Error al actualizar datos en la tabla $tabla");
    }
  }

  /**
   * Elimina un registro de una tabla de la base de datos.
   * 
   * @param string $tabla El nombre de la tabla.
   * @param string $campo El campo por el cual se eliminará el registro.
   * @param mixed $id El valor del campo a eliminar.
   * @return PDOStatement El resultado de la consulta.
   * 
   */
  protected function eliminarRegistro($tabla, $campo, $id)
  {
    $query = "DELETE FROM $tabla WHERE $campo = :id";
    return $this->ejecutarConsulta($query, [':id' => $id]);
  }

  /**
   * Encripta una contraseña utilizando un algoritmo de hash seguro.
   * 
   * @param string $password La contraseña a encriptar.
   * @return string La contraseña encriptada.
   * 
   * @description
   * Esta función utiliza el algoritmo bcrypt para encriptar la contraseña.
   * Se genera un "salt" aleatorio y se utiliza para crear un hash seguro.
   */
  protected function hashearContraseña($password)
  {

    $salt = '';
    $saltChars = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $saltLength = 22;

    for ($i = 0; $i < $saltLength; $i++) {
      $salt .= $saltChars[mt_rand(0, strlen($saltChars) - 1)];
    }

    $saltFormateado = '$2a$10$' . $salt;

    if (defined('TOKEN_SECRET_KEY') && strpos(TOKEN_SECRET_KEY, '%s') !== false) {
      $saltFormateado = sprintf(TOKEN_SECRET_KEY, $salt);
    }

    return crypt($password, $saltFormateado);
  }

  /**
   * Verifica si una contraseña coincide con su hash.
   * 
   * @param string $password La contraseña a verificar.
   * @param string $hashed_password El hash de la contraseña.
   * 
   * @return bool True si la contraseña coincide, false en caso contrario.
   * 
   * @description
   * Esta función utiliza el algoritmo bcrypt para verificar si la contraseña
   * coincide con su hash.
   */

  function validarContraseña($passwordIngresado, $passwordHasheado)
  {
    // La mejor manera de validar en PHP 5.1
    $hashGenerado = crypt($passwordIngresado, $passwordHasheado);

    // Implementación manual de comparación de tiempo constante
    if (strlen($hashGenerado) !== strlen($passwordHasheado)) {
      return false;
    }

    $diferencia = 0;
    for ($i = 0; $i < strlen($hashGenerado); $i++) {
      $diferencia |= (ord($hashGenerado[$i]) ^ ord($passwordHasheado[$i]));
    }

    // Solo será 0 si todas las comparaciones fueron exactas
    return $diferencia === 0;
  }

  /**
   * Verifica si un correo electrónico ya existe en la base de datos.
   * 
   * @param string $correo El correo electrónico a verificar.
   * @return bool True si el correo electrónico existe, false en caso contrario.
   */
  public function localizarCorreo($correo)
  {
    $query = "SELECT COUNT(*) as total FROM usuario WHERE usuario_email = :correo";
    $result = $this->ejecutarConsulta($query, [':correo' => $correo]);
    $data = $result->fetch(PDO::FETCH_ASSOC);
    return $data['total'] > 0;
  }

  /**
   * Verificar si un username ya existe en la base de datos.
   * 
   * @param string $username El username a verificar.
   * @return bool True si el username existe, false en caso contrario.
   *
   */
  public function localizarUsername($username)
  {
    $query = "SELECT COUNT(*) as total FROM usuario WHERE usuario_usuario = :username";
    $result = $this->ejecutarConsulta($query, [':username' => $username]);
    $data = $result->fetch(PDO::FETCH_ASSOC);
    return $data['total'] > 0;
  }

  /**
   * Sanitiza y valida un array de datos según reglas específicas.
   * 
   * @param array $datos Array de datos a sanitizar y validar
   * @param array $reglas Array asociativo con reglas de validación
   * @return array Array con datos sanitizados y errors encontrados
   */
  public function validarDatos($datos, $reglas)
  {
    $resultado = [
      'datos' => [],
      'errors' => []
    ];

    foreach ($reglas as $campo => $regla) {
      // Obtener el valor o usar valor por defecto
      $valor = isset($datos[$campo]) ? $datos[$campo] : null;

      // Aplicar sanitización básica si se especifica
      if (isset($regla['sanitizar']) && $regla['sanitizar'] === true) {
        $valor = $this->sanitizarEntrada($valor);
      }

      // Validar si es obligatorio
      if (isset($regla['requerido']) && $regla['requerido'] === true && ($valor === null || $valor === '')) {
        $resultado['errors'][$campo] = "El campo $campo es requerido";
        continue;
      }

      // Validar longitud mínima
      if (isset($regla['min']) && strlen($valor) < $regla['min']) {
        $resultado['errors'][$campo] = "El campo $campo debe tener al menos {$regla['min']} caracteres";
        continue;
      }

      // Validar longitud máxima
      if (isset($regla['max']) && strlen($valor) > $regla['max']) {
        $resultado['errors'][$campo] = "El campo $campo no debe exceder {$regla['max']} caracteres";
        continue;
      }

      // Validar formatos específicos
      if (isset($regla['formato'])) {
        switch ($regla['formato']) {
          case 'email':
            if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
              $resultado['errors'][$campo] = "El formato de correo electrónico no es válido";
              continue 2; // Sale del switch y del foreach actual
            }
            break;

          case 'numero':
            if (!is_numeric($valor)) {
              $resultado['errors'][$campo] = "El campo $campo debe ser un número";
              continue 2;
            }
            // Convertir a número si es válido
            $valor = (float)$valor;
            break;

          case 'entero':
            $validatedValue = filter_var($valor, FILTER_VALIDATE_INT, [
              'options' => ['min_range' => 0]
            ]);
            if ($validatedValue === false) {
              $resultado['errors'][$campo] = "El campo $campo debe ser un número entero mayor o igual a 0";
              continue 2;
            }
            // Convertir a entero si es válido
            $valor = $validatedValue;
            break;

          case 'alfa':
            if (!ctype_alpha($valor)) {
              $resultado['errors'][$campo] = "El campo $campo solo debe contener letras";
              continue 2;
            }
            break;

          case 'alfanumerico':
            if (!ctype_alnum($valor)) {
              $resultado['errors'][$campo] = "El campo $campo solo debe contener letras y números";
              continue 2;
            }
            break;

          default:
            // Si es una expresión regular personalizada
            if (!$this->validarFormato($regla['formato'], $valor)) {
              $resultado['errors'][$campo] = "El formato del campo $campo no es válido";
              continue 2;
            }
        }
      }

      // Aplicar filtros específicos
      if (isset($regla['filtro'])) {
        switch ($regla['filtro']) {
          case 'striptags':
            $valor = strip_tags($valor);
            break;

          case 'lower':
            $valor = strtolower($valor);
            break;

          case 'upper':
            $valor = strtoupper($valor);
            break;

          case 'trim':
            $valor = trim($valor);
            break;
        }
      }

      // Guardar valor sanitizado y validado
      $resultado['datos'][$campo] = $valor;
    }

    return $resultado;
  }


  /**
   * Verifica si una cadena cumple con un patrón específico.
   * @param string $regEx El patrón a verificar.
   * @param string $cadena La cadena a verificar.
   * @return bool True si la cadena cumple con el patrón, false en caso contrario.
   * 
   * @description
   * Esta función utiliza expresiones regulares para verificar si una cadena
   * cumple con un patrón específico.
   * Por ejemplo, se puede usar para validar correos electrónicos, números de teléfono, etc.
   */
  protected function validarFormato($regEx, $cadena)
  {
    return preg_match("/^" . $regEx . "$/", $cadena) === 1;
  }

  /**
   * Sanitiza datos para inserción segura en base de datos
   * 
   * @param array $datos Array asociativo con datos a sanitizar
   * @return array Datos sanitizados
   */
  public function sanitizarParaBD($datos)
  {
    $sanitizados = [];

    foreach ($datos as $clave => $valor) {
      if (is_string($valor)) {
        // Sanitizar strings
        $sanitizados[$clave] = $this->sanitizarEntrada($valor);
      } elseif (is_array($valor)) {
        // Recursivamente sanitizar arrays
        $sanitizados[$clave] = $this->sanitizarParaBD($valor);
      } else {
        // Mantener otros tipos sin cambios (números, booleanos, etc.)
        $sanitizados[$clave] = $valor;
      }
    }

    return $sanitizados;
  }

  /**
   * Valida y sanitiza un archivo subido
   * 
   * @param array $archivo Elemento de $_FILES a validar
   * @param array $opciones Opciones de validación (tipos, tamaño máximo, etc.)
   * @return array Resultado con estado y message
   */
  public function validarArchivo($archivo, $opciones = [])
  {
    $resultado = [
      'valido' => false,
      'message' => '',
      'ruta_temp' => '',
      'nombre_original' => '',
      'extension' => ''
    ];

    // si en el archivo error es diferente a 0, entonces hubo un error en la subida que no es 0 
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
      switch ($archivo['error']) {
        case UPLOAD_ERR_INI_SIZE:
          $max_size = ini_get('upload_max_filesize');
          $resultado['message'] = "El archivo excede el tamaño máximo permitido por el servidor ($max_size)";
          break;
        case UPLOAD_ERR_FORM_SIZE:
          $resultado['message'] = "El archivo excede el tamaño máximo permitido por el formulario";
          break;
        case UPLOAD_ERR_PARTIAL:
          $resultado['message'] = "El archivo se subió parcialmente. Intente nuevamente";
          break;
        case UPLOAD_ERR_NO_FILE:
          // es valido poque no se subio ningun archivo por lo que se usara el valor por defecto
          $resultado['valido'] = true;
          break;
        case UPLOAD_ERR_NO_TMP_DIR:
          $resultado['message'] = "No se encuentra la carpeta temporal en el servidor";
          break;
        case UPLOAD_ERR_CANT_WRITE:
          $resultado['message'] = "No se pudo guardar el archivo en el servidor";
          break;
        case UPLOAD_ERR_EXTENSION:
          $resultado['message'] = "Una extensión de PHP detuvo la carga del archivo";
          break;
        default:
          $resultado['message'] = "Error desconocido al subir el archivo (código: {$archivo['error']})";
      }
      return $resultado;
    }


    // Verificar el tipo MIME
    if (isset($opciones['tipos']) && !empty($opciones['tipos'])) {
      $finfo = new \finfo(FILEINFO_MIME_TYPE);
      $tipo_mime = $finfo->file($archivo['tmp_name']);

      if (!in_array($tipo_mime, $opciones['tipos'])) {
        $tipos_permitidos = implode(', ', $opciones['tipos']);
        $resultado['message'] = "El tipo de archivo no es válido ($tipo_mime). Se esperaba: $tipos_permitidos";
        return $resultado;
      }
    }

    // Verificar tamaño máximo
    if (isset($opciones['tamano_max']) && $archivo['size'] > $opciones['tamano_max']) {
      $tamano_mb = round($opciones['tamano_max'] / (1024 * 1024), 2);
      $tamano_actual_mb = round($archivo['size'] / (1024 * 1024), 2);
      $resultado['message'] = "El archivo excede el tamaño máximo permitido: {$tamano_actual_mb}MB (máximo: {$tamano_mb}MB)";
      return $resultado;
    }

    // Obtener extensión
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);

    // Verificar extensiones permitidas
    if (isset($opciones['extensiones']) && !in_array(strtolower($extension), array_map('strtolower', $opciones['extensiones']))) {
      $extensiones_permitidas = implode(', ', $opciones['extensiones']);
      $resultado['message'] = "La extensión del archivo ($extension) no es válida. Se esperaba: $extensiones_permitidas";
      return $resultado;
    }

    // Si llegamos aquí, el archivo es válido
    $resultado['valido'] = true;
    $resultado['ruta_temp'] = $archivo['tmp_name'];
    $resultado['nombre_original'] = $archivo['name'];
    $resultado['extension'] = $extension;

    return $resultado;
  }

  /**
   * Mejora de la función sanitizarEntrada para mayor seguridad
   */
  public function sanitizarEntrada($cadena)
  {
    if (is_null($cadena) || !is_string($cadena)) {
      return '';
    }

    // Eliminar caracteres invisibles y potencialmente peligrosos
    $cadena = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cadena);

    // Patrones peligrosos a eliminar (mantener los que ya tenías)
    $patrones = [
      // Scripts
      '/<script\b[^>]*>(.*?)<\/script>/is',
      '/<\s*script\s*>(.*?)<\s*\/\s*script\s*>/is',
      // SQL Injection
      '/SELECT\s+.*?\s+FROM/is',
      '/INSERT\s+INTO/is',
      '/UPDATE\s+.*?\s+SET/is',
      '/DELETE\s+FROM/is',
      '/DROP\s+.*?/is',
      '/TRUNCATE\s+TABLE/is',
      '/SHOW\s+TABLES/is',
      '/SHOW\s+DATABASES/is',
      // PHP Tags
      '/<\?php/i',
      '/\?>/i',
      // Más patrones HTML peligrosos
      '/<iframe/i',
      '/<object/i',
      '/<embed/i',
      '/javascript:/i',
      '/vbscript:/i',
      '/onclick/i',
      '/onload/i',
      '/onerror/i'
    ];

    // Aplicar patrones
    $cadena = preg_replace($patrones, '', $cadena);

    // Convertir caracteres especiales a entidades HTML
    $cadena = htmlspecialchars($cadena, ENT_QUOTES, 'UTF-8');

    return trim($cadena);
  }
}
