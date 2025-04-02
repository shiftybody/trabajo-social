<?php

/**
 * Configuración de sesiones y cookies
 */

// Tiempo de inactividad para cerrar sesión (en segundos)
// Por defecto: 5 minutos = 300 segundos
define('SESSION_INACTIVE_TIMEOUT', 120);

// Duración de la cookie de "recordar sesión" (en segundos)
// Por defecto: 30 días = 2592000 segundos
define('REMEMBER_COOKIE_DURATION', 30 * 24 * 60 * 60);

// Configurar los parámetros de sesión PHP
ini_set('session.gc_maxlifetime', SESSION_INACTIVE_TIMEOUT);
ini_set('session.cookie_lifetime', 0); // 0 = hasta que se cierre el navegador
ini_set('session.use_strict_mode', 1); // Seguridad mejorada
ini_set('session.use_only_cookies', 1); // Usar solo cookies para sesiones
ini_set('session.use_trans_sid', 0); // Desactivar transmisión de ID de sesión en URL
ini_set('session.cookie_httponly', 1); // Cookies accesibles solo por HTTP (no JavaScript)
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Cookies seguras en HTTPS
ini_set('session.cookie_samesite', 'Lax'); // Protección contra CSRF