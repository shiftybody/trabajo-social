<?php

/**
 *  En este archivo se definen las constantes de configuración 
 *  y se cargan las variables de entorno desde el archivo .env
 */

$dotenv =  new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

define('MYSQL_SERVER', $_ENV['MYSQL_SERVER']);
define('MYSQL_PORT', $_ENV['MYSQL_PORT']);
define('MYSQL_USER', $_ENV['MYSQL_USER']);
define('MYSQL_ROOT_PASSWORD', $_ENV['MYSQL_ROOT_PASSWORD']);
define('MYSQL_DATABASE', $_ENV['MYSQL_DATABASE']);

define('APP_NAME', $_ENV['APP_NAME']);
define('APP_URL', $_ENV['APP_URL']);
define('APP_ROOT', $_ENV['APP_ROOT']);
define('APP_SESSION_NAME', $_ENV['APP_SESSION_NAME']);

define('TOKEN_SECRET_KEY', $_ENV['TOKEN_SECRET_KEY']);
date_default_timezone_set($_ENV['APP_TIMEZONE']);
