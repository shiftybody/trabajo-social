<?php

/**
 * Configuración de sesiones, cookies y otros parámetros de PHP.
 */

define('SESSION_EXPIRATION_TIMEOUT', 60 * 5);
define('REMEMBER_COOKIE_DURATION', 60 * 60 * 24 * 30);

ini_set('session.cookie_lifetime', 0);
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Lax');

ini_set('file_uploads', 1);
ini_set('max_execution_time', 30);
ini_set('memory_limit', '1024M');

ini_set('log_errors', 1);
ini_set('error_log', APP_ROOT . 'logs/php_errors.log');
