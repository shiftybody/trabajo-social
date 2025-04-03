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
 * - encripta & desencriptar contraseñas.
 */
class mainModel
{

  private $server = MYSQL_SERVER;
  private $port = MYSQL_PORT;
  private $db = MYSQL_DATABASE;
  private $user = MYSQL_USER;
  private $pass = MYSQL_ROOT_PASSWORD;

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
      // Arrays para almacenar nombres de campos y marcadores
      $campos = [];
      $marcadores = [];
      $parametros = [];

      // Separar datos en arrays para mejor manipulación
      foreach ($datos as $dato) {
        $campos[] = $dato["campo_nombre"];
        $marcadores[] = $dato["campo_marcador"];
        $parametros[$dato["campo_marcador"]] = $dato["campo_valor"];
      }

      // Construir la consulta de forma más eficiente
      $query = "INSERT INTO $tabla (" . implode(", ", $campos) .
        ") VALUES (" . implode(", ", $marcadores) . ")";

      // Usar la función ejecutarConsulta existente para preparar, bindear y ejecutar
      $sql = $this->ejecutarConsulta($query, $parametros);

      return $sql;
    } catch (Exception $e) {
      // Registrar el error en lugar de terminar la ejecución
      error_log("Error en guardarDatos: " . $e->getMessage());
      throw new Exception("Error al guardar datos en la tabla $tabla");
    }
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
   * Sanitiza una cadena para prevenir ataques XSS y otros tipos de inyección.
   * 
   * @param string $cadena La cadena a sanitizar.
   * @return string La cadena sanitizada.
   * 
   * @description
   * Esta función elimina o neutraliza código potencialmente malicioso
   * de una cadena de entrada, como scripts, inyecciones SQL y otros ataques.
   * Usar para sanitizar entradas de usuario antes de mostrarlas, no para SQL.
   */
  public function sanitizarEntrada($cadena)
  {
    if (is_null($cadena) || !is_string($cadena)) {
      return '';
    }

    // Patrones peligrosos a eliminar
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
      '/\?>/i'
    ];

    // Primero filtramos con una expresión regular
    $cadena = preg_replace($patrones, '', $cadena);

    // Luego eliminamos caracteres peligrosos
    $cadena = htmlspecialchars($cadena, ENT_QUOTES, 'UTF-8');

    return trim($cadena);
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
    $salt = bin2hex(openssl_random_pseudo_bytes(22));
    $salt = sprintf(TOKEN_SECRET_KEY, $salt);
    return crypt($password, $salt);
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
  function validarContraseña($password, $hashed_password)
  {
    return crypt($password, $hashed_password) === $hashed_password;
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
}
