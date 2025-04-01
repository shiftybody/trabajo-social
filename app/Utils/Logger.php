<?php

namespace App\Utils;

class Logger
{
  /**
   * Directorio base para los logs
   * @var string
   */
  private $logDir;

  /**
   * Nombre del archivo de log
   * @var string
   */
  private $logFile;

  /**
   * Constructor de la clase Logger
   *
   * @param string $logDir Directorio para los logs (opcional)
   * @param string $logFile Nombre del archivo de log (opcional)
   */
  public function __construct($logDir = null, $logFile = null)
  {
    // Establecer directorio de logs por defecto
    $this->logDir = $logDir !== null ? $logDir : dirname(dirname(__DIR__)) . '/storage/logs';

    // Establecer nombre de archivo por defecto con formato: log-YYYY-MM-DD.log
    $this->logFile = $logFile !== null ? $logFile : 'log-' . date('Y-m-d') . '.log';

    // Crear el directorio si no existe
    if (!is_dir($this->logDir)) {
      mkdir($this->logDir, 0755, true);
    }
  }

  /**
   * Registra una excepción en el archivo de log
   *
   * @param Exception $exception La excepción a registrar
   * @param string $context Contexto adicional (opcional)
   * @return bool True si se registró correctamente, False en caso contrario
   */
  public function logException($exception, $context = '')
  {
    $timestamp = date('Y-m-d H:i:s');
    $message = $exception->getMessage();
    $code = $exception->getCode();
    $file = $exception->getFile();
    $line = $exception->getLine();

    // Formato del log
    $logMessage = sprintf(
      "[%s] EXCEPTION: %s (Code: %s) in %s on line %d\n",
      $timestamp,
      $message,
      $code,
      $file,
      $line
    );

    // Agregar información de la traza
    $logMessage .= sprintf("Stack Trace:\n%s\n", $exception->getTraceAsString());

    // Agregar contexto si existe
    if (!empty($context)) {
      $logMessage .= sprintf("Context: %s\n", $context);
    }

    $logMessage .= str_repeat('-', 80) . "\n\n";

    // Escribir en el archivo de log
    return $this->writeLog($logMessage);
  }

  /**
   * Registra un mensaje de error en el archivo de log
   *
   * @param string $message Mensaje de error
   * @param string $context Contexto adicional (opcional)
   * @return bool True si se registró correctamente, False en caso contrario
   */
  public function logError($message, $context = '')
  {
    $timestamp = date('Y-m-d H:i:s');

    // Formato del log
    $logMessage = sprintf(
      "[%s] ERROR: %s\n",
      $timestamp,
      $message
    );

    // Agregar contexto si existe
    if (!empty($context)) {
      $logMessage .= sprintf("Context: %s\n", $context);
    }

    $logMessage .= str_repeat('-', 80) . "\n\n";

    // Escribir en el archivo de log
    return $this->writeLog($logMessage);
  }

  /**
   * Registra un mensaje informativo en el archivo de log
   *
   * @param string $message Mensaje informativo
   * @param string $context Contexto adicional (opcional)
   * @return bool True si se registró correctamente, False en caso contrario
   */
  public function logInfo($message, $context = '')
  {
    $timestamp = date('Y-m-d H:i:s');

    // Formato del log
    $logMessage = sprintf(
      "[%s] INFO: %s\n",
      $timestamp,
      $message
    );

    // Agregar contexto si existe
    if (!empty($context)) {
      $logMessage .= sprintf("Context: %s\n", $context);
    }

    $logMessage .= str_repeat('-', 40) . "\n\n";

    // Escribir en el archivo de log
    return $this->writeLog($logMessage);
  }

  /**
   * Escribe en el archivo de log
   *
   * @param string $message Mensaje a escribir
   * @return bool True si se escribió correctamente, False en caso contrario
   */
  private function writeLog($message)
  {
    $logPath = $this->logDir . '/' . $this->logFile;

    return file_put_contents($logPath, $message, FILE_APPEND) !== false;
  }

  /**
   * Establece el manejador de excepciones global
   *
   * @return void
   */
  public function registerExceptionHandler()
  {
    $logger = $this;
    set_exception_handler(function ($exception) use ($logger) {
      $logger->logException($exception);

      // Respuesta controlada para producción
      header('HTTP/1.1 500 Internal Server Error');
      echo "Ha ocurrido un error. Por favor, contacte al administrador.";
      exit(1);
    });
  }

  /**
   * Establece el manejador de errores global
   *
   * @return void
   */
  public function registerErrorHandler()
  {
    $logger = $this;
    set_error_handler(function ($severity, $message, $file, $line) use ($logger) {
      if (!(error_reporting() & $severity)) {
        // Este error no está incluido en error_reporting
        return;
      }

      $logger->logError(
        sprintf("%s in %s on line %d", $message, $file, $line),
        "Severity: $severity"
      );

      // Devuelve false para que PHP maneje el error de manera estándar también
      return false;
    });
  }
}
