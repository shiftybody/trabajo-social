<?php

/**
 * Clase Response
 * 
 * Encapsula una respuesta HTTP y proporciona
 * métodos para configurarla y enviarla al cliente
 */

namespace App\Core;

class Response
{
  /**
   * Contenido de la respuesta
   * @var string
   */
  private $content;

  /**
   * Código de estado HTTP
   * @var int
   */
  private $statusCode;

  /**
   * Cabeceras HTTP
   * @var array
   */
  private $headers = array();

  /**
   * Cookies a enviar
   * @var array
   */
  private $cookies = array();

  /**
   * Constructor
   * 
   * @param string $content Contenido de la respuesta
   * @param int $statusCode Código de estado HTTP
   * @param array $headers Cabeceras HTTP
   */
  public function __construct($content = '', $statusCode = 200, $headers = array())
  {
    $this->content = $content;
    $this->statusCode = $statusCode;
    $this->headers = $headers;
  }

  /**
   * Crea una respuesta HTML
   * 
   * @param string $content Contenido HTML
   * @param int $statusCode Código de estado HTTP
   * @return Response Instancia de respuesta
   */
  public static function html($content, $statusCode = 200)
  {
    return new self($content, $statusCode, array('Content-Type' => 'text/html; charset=UTF-8'));
  }

  /**
   * Crea una respuesta JSON
   * 
   * @param mixed $data Datos a codificar como JSON
   * @param int $statusCode Código de estado HTTP
   * @return Response Instancia de respuesta
   */
  public static function json($data, $statusCode = 200)
  {
    return new self(
      json_encode($data, JSON_UNESCAPED_UNICODE),
      $statusCode,
      array('Content-Type' => 'application/json; charset=UTF-8')
    );
  }

  /**
   * Crea una redirección
   * 
   * @param string $url URL de destino
   * @param int $statusCode Código de estado HTTP
   * @return Response Instancia de respuesta
   */
  public static function redirect($url, $statusCode = 303)
  {
    return new self('', $statusCode, array('Location' => $url));
  }

  /**
   * Crea una respuesta de texto plano
   * 
   * @param string $text Texto a enviar
   * @param int $statusCode Código de estado HTTP
   * @return Response Instancia de respuesta
   */
  public static function text($text, $statusCode = 200)
  {
    return new self($text, $statusCode, array('Content-Type' => 'text/plain; charset=UTF-8'));
  }

  /**
   * Crea una respuesta XML
   * 
   * @param string $xml Contenido XML
   * @param int $statusCode Código de estado HTTP
   * @return Response Instancia de respuesta
   */
  public static function xml($xml, $statusCode = 200)
  {
    return new self($xml, $statusCode, array('Content-Type' => 'application/xml; charset=UTF-8'));
  }

  /**
   * Crea una respuesta con un archivo para descargar
   * 
   * @param string $filePath Ruta al archivo
   * @param string $fileName Nombre del archivo para la descarga
   * @param int $statusCode Código de estado HTTP
   * @return Response Instancia de respuesta
   */
  public static function download($filePath, $fileName = null, $statusCode = 200)
  {
    if (!file_exists($filePath)) {
      return new self('File not found', 404, array('Content-Type' => 'text/plain'));
    }

    $fileName = $fileName ?: basename($filePath);
    $content = file_get_contents($filePath);
    $mimeType = self::getMimeType($filePath);

    $headers = array(
      'Content-Type' => $mimeType,
      'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
      'Content-Length' => filesize($filePath)
    );

    return new self($content, $statusCode, $headers);
  }

  /**
   * Establece una cabecera HTTP
   * 
   * @param string $name Nombre de la cabecera
   * @param string $value Valor de la cabecera
   * @return Response Instancia actual para encadenamiento
   */
  public function header($name, $value)
  {
    $this->headers[$name] = $value;
    return $this;
  }

  /**
   * Establece una cookie
   * 
   * @param string $name Nombre de la cookie
   * @param string $value Valor de la cookie
   * @param int $expire Tiempo de expiración (en segundos desde ahora)
   * @param string $path Ruta de la cookie
   * @param string $domain Dominio de la cookie
   * @param bool $secure Cookie segura (HTTPS)
   * @param bool $httpOnly Cookie HTTP only
   * @return Response Instancia actual para encadenamiento
   */
  public function cookie(
    $name,
    $value,
    $expire = 0,
    $path = '/',
    $domain = '',
    $secure = false,
    $httpOnly = true
  ) {
    $this->cookies[$name] = array(
      'value' => $value,
      'expire' => $expire > 0 ? time() + $expire : 0,
      'path' => $path,
      'domain' => $domain,
      'secure' => $secure,
      'httpOnly' => $httpOnly
    );

    return $this;
  }

  /**
   * Establece el código de estado HTTP
   * 
   * @param int $statusCode Código de estado HTTP
   * @return Response Instancia actual para encadenamiento
   */
  public function setStatusCode($statusCode)
  {
    $this->statusCode = $statusCode;
    return $this;
  }

  /**
   * Establece el contenido de la respuesta
   * 
   * @param string $content Contenido de la respuesta
   * @return Response Instancia actual para encadenamiento
   */
  public function setContent($content)
  {
    $this->content = $content;
    return $this;
  }

  /**
   * Envía la respuesta al cliente
   */
  public function send()
  {
    // Establecer código de estado
    http_response_code($this->statusCode);

    // Establecer cabeceras
    foreach ($this->headers as $name => $value) {
      header("$name: $value");
    }

    // Establecer cookies
    foreach ($this->cookies as $name => $params) {
      setcookie(
        $name,
        $params['value'],
        $params['expire'],
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httpOnly']
      );
    }

    // Enviar contenido
    echo $this->content;
    exit;
  }

  /**
   * Obtiene el tipo MIME de un archivo
   * 
   * @param string $filePath Ruta al archivo
   * @return string Tipo MIME
   */
  private static function getMimeType($filePath)
  {
    $mimeTypes = array(
      'txt' => 'text/plain',
      'htm' => 'text/html',
      'html' => 'text/html',
      'php' => 'text/html',
      'css' => 'text/css',
      'js' => 'application/javascript',
      'json' => 'application/json',
      'xml' => 'application/xml',
      'swf' => 'application/x-shockwave-flash',
      'flv' => 'video/x-flv',

      // images
      'png' => 'image/png',
      'jpe' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'jpg' => 'image/jpeg',
      'gif' => 'image/gif',
      'bmp' => 'image/bmp',
      'ico' => 'image/vnd.microsoft.icon',
      'tiff' => 'image/tiff',
      'tif' => 'image/tiff',
      'svg' => 'image/svg+xml',
      'svgz' => 'image/svg+xml',

      // archives
      'zip' => 'application/zip',
      'rar' => 'application/x-rar-compressed',
      'exe' => 'application/x-msdownload',
      'msi' => 'application/x-msdownload',
      'cab' => 'application/vnd.ms-cab-compressed',

      // audio/video
      'mp3' => 'audio/mpeg',
      'qt' => 'video/quicktime',
      'mov' => 'video/quicktime',
      'mp4' => 'video/mp4',

      // adobe
      'pdf' => 'application/pdf',
      'psd' => 'image/vnd.adobe.photoshop',
      'ai' => 'application/postscript',
      'eps' => 'application/postscript',
      'ps' => 'application/postscript',

      // ms office
      'doc' => 'application/msword',
      'rtf' => 'application/rtf',
      'xls' => 'application/vnd.ms-excel',
      'ppt' => 'application/vnd.ms-powerpoint',
      'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',

      // open office
      'odt' => 'application/vnd.oasis.opendocument.text',
      'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    if (array_key_exists($ext, $mimeTypes)) {
      return $mimeTypes[$ext];
    }

    if (function_exists('finfo_open')) {
      $finfo = finfo_open(FILEINFO_MIME);
      $mimetype = finfo_file($finfo, $filePath);
      finfo_close($finfo);
      return $mimetype;
    }

    return 'application/octet-stream';
  }
}
