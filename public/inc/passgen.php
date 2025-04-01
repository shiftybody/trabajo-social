<?php
// Guarda este código en un archivo llamado generate_hash.php

$password = 'Administrador@1234'; // Cambia esto por tu contraseña deseada

// Generando el hash usando el método del mainModel
$salt = bin2hex(openssl_random_pseudo_bytes(22));
$salt = sprintf('$2y$12$%s$', $salt);
$hash = crypt($password, $salt);

echo "Contraseña: $password\n";
echo "Hash: $hash\n";
