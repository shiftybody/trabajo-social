<?php

/**
 * Configuración de sesiones, cookies y otros parámetros de PHP.
 */

define('SESSION_EXPIRATION_TIMOUT', 60 * 5); // 5 minutos
define('REMEMBER_COOKIE_DURATION', 60 * 60 * 24 * 30); // 30 días

ini_set('session.cookie_lifetime', 0); // Hasta que se cierre el navegador
ini_set('session.use_strict_mode', 1); // Seguridad mejorada
ini_set('session.use_only_cookies', 1); // usar solo cookies para sesiones
ini_set('session.use_trans_sid', 0); // Desactivar transmisión de ID de sesión en URL
ini_set('session.cookie_httponly', 1); // Cookies accesibles solo por HTTP (no JavaScript)
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Cookies seguras en HTTPS
ini_set('session.cookie_samesite', 'Lax'); // Protección contra CSRF

ini_set('file_uploads', 1); // Habilitar subida de archivos
ini_set('max_execution_time', 30); // tiempo maximo de ejecucion
ini_set('memory_limit', '1024M'); // limite de memoria

ini_set('log_errors', 1); // Habilitar registro de errores
ini_set('error_log', APP_ROOT . 'logs/php_errors.log');
