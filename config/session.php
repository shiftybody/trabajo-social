<?php

/**
 * Configuración de sesiones y cookies
 */

define('SESSION_EXPIRATION_TIMOUT', 30); // 1 30
define('REMEMBER_COOKIE_DURATION', 60); // 1 min

ini_set('session.cookie_lifetime', 0); // 0 = hasta que se cierre el navegador 
ini_set('session.use_strict_mode', 1); // Seguridad mejorada
ini_set('session.use_only_cookies', 1); // Usar solo cookies para sesiones
ini_set('session.use_trans_sid', 0); // Desactivar transmisión de ID de sesión en URL
ini_set('session.cookie_httponly', 1); // Cookies accesibles solo por HTTP (no JavaScript)
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Cookies seguras en HTTPS
ini_set('session.cookie_samesite', 'Lax'); // Protección contra CSRF