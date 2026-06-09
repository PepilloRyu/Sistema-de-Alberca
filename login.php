<?php
// login.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$errorMessage = '';

// 1. PRIMERO: Si viene rebotado por no tener rol, lo deslogueamos inmediatamente para romper el bucle
if (isset($_GET['error']) && $_GET['error'] === 'sin_rol') {
    if (isset($_GET['error']) && $_GET['error'] === 'timeout') {
        $errorMessage = 'Por tu seguridad, tu sesión ha sido cerrada automáticamente después de 15 minutos de inactividad.';
    }
    Auth::logout();
    $errorMessage = 'Tu cuenta ha sido creada, pero un Administrador debe asignarte tu rol (Limpieza, Mantenimiento, etc.) antes de poder ingresar.';
}

// 2. SEGUNDO: AHORA SÍ, si ya está logueado validamente, que el index decida a dónde enviarlo
if (Auth::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['Email'] ?? '');
    $password = $_POST['Password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $errorMessage = 'Todos los campos son obligatorios';
    } else {
        if (Auth::login($email, $password)) {
            // Si el login es exitoso, el index lo mandará a su panel correcto
            header("Location: index.php");
            exit();
        } else {
            $errorMessage = 'Correo electrónico o contraseña incorrectos, o cuenta inactiva.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Empleado - Sistema de Albercas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-id-badge" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 15px;"></i>
                <h2>Portal del Empleado</h2>
                <p>Ingresa tus credenciales de acceso</p>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php" id="loginForm">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Correo electrónico institucional</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="Email" id="email" placeholder="usuario@albercas.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="input-icon password-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="Password" id="passwordField" placeholder="••••••••" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword" onclick="togglePasswordVisibility()"></i>
                    </div>
                </div>

                <button type="submit" class="btn-auth" id="submitBtn">
                    <span>Acceder al sistema</span>
                    <i class="fas fa-sign-in-alt"></i>
                </button>
            </form>

            <div class="auth-footer">
                <p>¿Eres un nuevo empleado? <a href="registro.php">Solicita tu cuenta aquí</a></p>
            </div>
        </div>
    </div>

    <script>
    function togglePasswordVisibility() {
        const passwordField = document.getElementById('passwordField');
        const toggleIcon = document.getElementById('togglePassword');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    </script>
</body>
</html>

