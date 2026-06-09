<?php
// generate_hash_12345678.php
$password = '12345678';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Contraseña: " . $password . "\n";
echo "Hash: " . $hash;
?>