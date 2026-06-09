<?php
// logout.php - Cerrar sesión con mensaje
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

// Guardar nombre del usuario para el mensaje (opcional)
$user_name = $_SESSION['user_name'] ?? 'Usuario';

// Cerrar sesión
Auth::logout();

// Establecer mensaje de éxito en la sesión para mostrarlo en index.php
session_start(); // Iniciar nueva sesión para el mensaje
$_SESSION['success_message'] = "¡Hasta pronto, $user_name! Has cerrado sesión correctamente.";

// Redirigir al inicio
header("Location: index.php");
exit();
?>