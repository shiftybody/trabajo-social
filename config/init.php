<?php
require_once '../vendor/autoload.php';

use Dotenv\Dotenv;

try {
  $dotenv = new Dotenv(__DIR__ . '/../');
  $dotenv->load();
} catch (Exception $e) {
  echo $e->getMessage();
}

require_once 'app.php';
require_once 'server.php';

// TODO: Pasar esto a un servicio para que se cree cuando se 
// tenga que almacenar un archivo temporal
// Crear directorio de almacenamiento de archivos temporales
if (!file_exists(APP_ROOT . 'storage/tmp')) {
  mkdir(APP_URL . '/storage/tmp', 0777, true);
}

// Crear directorio de almacenamiento de logs
if (!file_exists(APP_ROOT . 'storage/logs')) {
  mkdir(APP_URL . '/storage/logs', 0777, true);
}

$logger = new App\Utils\Logger(APP_URL . '/storage/logs', 'log-' . date('Y-m-d') . '.log');
