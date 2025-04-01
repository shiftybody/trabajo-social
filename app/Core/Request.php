<?php

/**
 * Clase Request
 * 
 * Encapsula la información de una petición HTTP
 * y proporciona métodos para acceder a sus datos
 */

namespace App\Core;

class Request
{
  /**
   * Parámetros de ruta
   * @var array
   */
  private $params = array();

  /**
   * URI de la petición
   * @var string
   */
  private $uri;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->uri = $this->parseUri();
  }

  /**
   * Parsea la URI de la petición actual
   * @return string URI normalizada
   */
  private function parseUri()
  {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return $uri ? $uri : '/';
  }

  /**
   * Obtiene el método HTTP
   * @return string Método HTTP (GET, POST, PUT, DELETE)
   */
  public function getMethod()
  {
    if (isset($_SERVER['REQUEST_METHOD'])) {
      // Si se está usando un formulario con _method para simular PUT/DELETE
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
        return strtoupper($_POST['_method']);
      }
      return $_SERVER['REQUEST_METHOD'];
    }
    return 'GET';
  }

  /**
   * Obtiene la URI de la petición
   * @return string URI actual
   */
  public function getUri()
  {
    return $this->uri;
  }

  /**
   * Establece la URI manualmente
   * @param string $uri Nueva URI
   * @return Request Instancia actual para encadenamiento
   */
  public function setUri($uri)
  {
    $this->uri = '/' . trim($uri, '/');
    return $this;
  }

  /**
   * Verifica si la petición espera JSON
   * @return bool True si es una petición que espera JSON
   */
  public function expectsJson()
  {
    if (
      !empty($_SERVER['HTTP_ACCEPT']) &&
      strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    ) {
      return true;
    }

    if (
      !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
      return true;
    }

    return false;
  }

  /**
   * Verifica si es una petición AJAX
   * @return bool True si es una petición AJAX
   */
  public function ajax()
  {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  }

  /**
   * Obtiene un valor de la petición GET
   * @param string|null $key Nombre del parámetro (null para todos)
   * @param mixed $default Valor por defecto si no existe
   * @return mixed Valor del parámetro o todos los parámetros
   */
  public function get($key = null, $default = null)
  {
    if ($key === null) {
      return $_GET;
    }

    return isset($_GET[$key]) ? $_GET[$key] : $default;
  }

  /**
   * Obtiene un valor de la petición POST
   * @param string|null $key Nombre del parámetro (null para todos)
   * @param mixed $default Valor por defecto si no existe
   * @return mixed Valor del parámetro o todos los parámetros
   */
  public function post($key = null, $default = null)
  {
    if ($key === null) {
      return $_POST;
    }

    return isset($_POST[$key]) ? $_POST[$key] : $default;
  }

  /**
   * Obtiene el cuerpo de la petición como JSON
   * @param string|null $key Nombre del parámetro (null para todos)
   * @param mixed $default Valor por defecto si no existe
   * @return mixed Datos JSON decodificados
   */
  public function json($key = null, $default = null)
  {
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

    if (strpos($contentType, 'application/json') === false) {
      return $default;
    }

    $json = json_decode(file_get_contents('php://input'), true);

    if ($json === null) {
      return $default;
    }

    if ($key === null) {
      return $json;
    }

    return isset($json[$key]) ? $json[$key] : $default;
  }

  /**
   * Obtiene todos los datos de la petición (GET, POST, JSON)
   * @return array Datos combinados
   */
  public function all()
  {
    $data = $_GET;

    if ($this->getMethod() === 'POST') {
      $data = array_merge($data, $_POST);

      $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
      if (strpos($contentType, 'application/json') !== false) {
        $json = json_decode(file_get_contents('php://input'), true);
        if ($json) {
          $data = array_merge($data, $json);
        }
      }
    }

    return $data;
  }

  /**
   * Establece los parámetros de ruta
   * @param array $params Parámetros de ruta
   * @return Request Instancia actual para encadenamiento
   */
  public function setParams(array $params)
  {
    $this->params = $params;
    return $this;
  }

  /**
   * Obtiene un parámetro de ruta
   * @param int|null $index Índice del parámetro (null para todos)
   * @return mixed Valor del parámetro o todos los parámetros
   */
  public function param($index = null)
  {
    if ($index === null) {
      return $this->params;
    }

    return isset($this->params[$index]) ? $this->params[$index] : null;
  }

  /**
   * Obtiene la IP del cliente
   * @return string Dirección IP
   */
  public function getIp()
  {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }

    return $ip;
  }

  /**
   * Obtiene el user agent
   * @return string User agent
   */
  public function getUserAgent()
  {
    return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
  }

  /**
   * Obtiene el valor de una cabecera HTTP
   * @param string $name Nombre de la cabecera
   * @param mixed $default Valor por defecto si no existe
   * @return string Valor de la cabecera
   */
  public function header($name, $default = null)
  {
    $name = str_replace('-', '_', strtoupper($name));
    $headerName = 'HTTP_' . $name;

    return isset($_SERVER[$headerName]) ? $_SERVER[$headerName] : $default;
  }

  /**
   * Verifica si la solicitud se realizó con HTTPS
   * @return bool True si es HTTPS
   */
  public function isSecure()
  {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
      return true;
    }

    if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
      return true;
    }

    return false;
  }

  /**
   * Obtiene la URL base (sin query string)
   * @return string URL base
   */
  public function getBaseUrl()
  {
    $protocol = $this->isSecure() ? 'https://' : 'http://';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    return $protocol . $host;
  }

  /**
   * Obtiene la URL completa
   * @return string URL completa
   */
  public function getFullUrl()
  {
    $query = isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    return $this->getBaseUrl() . $this->getUri() . $query;
  }
}
