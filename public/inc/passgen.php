<?php
// Generador de contraseñas para crear al usuaro administrador 
// desde la base de datos por primera vez ( o si lo borran 🤧)

// Cambia esto por tu contraseña deseada
$password = 'Administrador@1234';

// Generando el hash usando el método del mainModel
$salt = bin2hex(openssl_random_pseudo_bytes(22));
$salt = sprintf('$2y$12$%s$', $salt);
$hash = crypt($password, $salt);

echo "Contraseña: $password\n";
echo "Hash: $hash\n";
