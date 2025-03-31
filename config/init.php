<?php

require_once '../vendor/autoload.php';

use Dotenv\Dotenv;

try {
  $dotenv = new Dotenv(__DIR__ . '/..');
  $dotenv->load();
} catch (Exception $e) {
  echo $e->getMessage();
}

require_once 'app.php';
require_once 'server.php';

// Crear directorio de almacenamiento de archivos temporales
if (!file_exists(APP_URL . '/storage/tmp')) {
  mkdir(APP_URL . '/storage/tmp', 0777, true);
}
