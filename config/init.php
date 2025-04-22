<?php

/**
 * Configuración de sesiones y cookies
 */

define('SESSION_EXPIRATION_TIMOUT', 60 * 5); // 5 minutos
define('REMEMBER_COOKIE_DURATION', 60 * 60 * 30 * 24); // 30 días

ini_set('session.cookie_lifetime', 0); // Hasta que se cierre el navegador
ini_set('session.use_strict_mode', 1); // Seguridad mejorada
ini_set('session.use_only_cookies', 1); // usar solo cookies para sesiones
ini_set('session.use_trans_sid', 0); // Desactivar transmisión de ID de sesión en URL
ini_set('session.cookie_httponly', 1); // Cookies accesibles solo por HTTP (no JavaScript)
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Cookies seguras en HTTPS
ini_set('session.cookie_samesite', 'Lax'); // Protección contra CSRF


ini_set('file_uploads', 1); // Habilitar subida de archivos
ini_set('log_errors', 1);      // Habilitar registro de errores

// Configurar tamaños máximos para subida de archivos
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');
ini_set('max_execution_time', 30);
ini_set('upload_tmp_dir', APP_ROOT . '/storage/tmp');
ini_set('error_log', APP_ROOT . '/storage/logs/php_errors.log');
