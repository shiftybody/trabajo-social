<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';

// imprimir _SESSION
echo "<pre>";
print_r($_SESSION);
// imprimir lo que se encuentra dentro de ultima_actividad convertido a timestamp
echo date("Y-m-d H:i:s", $_SESSION[APP_SESSION_NAME]['ultima_actividad']);
echo "</pre>";

$cookieValues = base64_decode($_COOKIE[APP_SESSION_NAME]);
$cookieData = json_decode($cookieValues, true);

echo "<pre>";
print_r($cookieData);
echo "</pre>";

