<?php
// hash.php
// Genera un hash bcrypt correcto para la contraseña demo: Admin123!
// Uso: abre este archivo en el navegador local o ejecútalo con: php hash.php

require __DIR__ . '/app/helpers/functions.php';
if(!is_local_request()){ http_response_code(403); exit('Herramienta disponible solo en CLI o localhost.'); }

$password = 'Admin123!';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

header('Content-Type: text/plain; charset=utf-8');

echo "Contraseña original: " . $password . PHP_EOL;
echo "Hash bcrypt generado:" . PHP_EOL;
echo $hash . PHP_EOL . PHP_EOL;

echo "Verificación:" . PHP_EOL;
echo password_verify($password, $hash) ? "OK: el hash coincide con Admin123!" : "ERROR: el hash no coincide";
echo PHP_EOL . PHP_EOL;

echo "SQL para actualizar usuarios demo:" . PHP_EOL;
echo "UPDATE usuarios SET password_hash = '" . $hash . "' WHERE email IN (" . PHP_EOL;
echo "  'admin@albercas.local'," . PHP_EOL;
echo "  'encargado@albercas.local'," . PHP_EOL;
echo "  'limpieza@albercas.local'," . PHP_EOL;
echo "  'tecnico@albercas.local'," . PHP_EOL;
echo "  'pendiente@albercas.local'" . PHP_EOL;
echo ");" . PHP_EOL;