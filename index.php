<?php
// index.php - Enrutador principal del sistema interno
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si el usuario ya inició sesión, enviarlo a su área de trabajo
if (Auth::isLoggedIn()) {
    $user = Auth::getCurrentUser();
    $rol_id = (int)($user['rol_id'] ?? 0);
    
    switch ($rol_id) {
        case 1: header("Location: usuarios/Admin/index.php"); break;
        case 2: header("Location: usuarios/EncargadoDeAlberca/index.php"); break;
        case 3: header("Location: usuarios/PersonalDeLimpieza/index.php"); break;
        case 4: header("Location: usuarios/TecnicoDeMantenimiento/index.php"); break;
        default: 
            // Si acaba de registrarse y no tiene rol asignado
            header("Location: login.php?error=sin_rol"); 
            break;
    }
} else {
    // Si no ha iniciado sesión, directo a la pantalla de acceso
    header("Location: login.php");
}
exit();
?>