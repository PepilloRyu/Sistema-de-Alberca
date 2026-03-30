<?php
require_once 'includes/config.php';
require_once 'includes/database.php'; // Asegúrate de incluir database.php
require_once 'includes/auth.php';

// Si ya está logueado, redirigir
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
$errorMessage = ''; // Para la variable que usas en el HTML

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['Email'] ?? ''; // Cambiado a 'Email' como está en tu formulario
    $password = $_POST['Password'] ?? ''; // Cambiado a 'Password' como está en tu formulario
    
    if (empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
        $errorMessage = 'Todos los campos son obligatorios';
    } else {
        // Usar la función login de la clase Auth
        if (Auth::login($email, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email o contraseña incorrectos';
            $errorMessage = 'Email o contraseña incorrectos';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Nuestras Albercas</title>
    
    <!-- Fuentes y librerías externas -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="../css/Login.css">
    
    <!-- CSS específico para login -->
    <link rel="stylesheet" href="css/Login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-user-lock"></i>
                <h2>¡Hola de nuevo!</h2>
                <p>Accede a tu cuenta para continuar</p>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i> <?php 
                        echo htmlspecialchars($_SESSION['success_message']); 
                        unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php" id="loginForm">
                <!-- EMAIL con icono dentro -->
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Correo electrónico</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" 
                               name="Email" 
                               id="email" 
                               placeholder="ejemplo@correo.com" 
                               value="<?php echo htmlspecialchars($_POST['Email'] ?? ''); ?>"
                               required>
                    </div>
                    <span class="text-danger" id="emailError"></span>
                </div>

                <!-- CONTRASEÑA con icono y botón mostrar/ocultar -->
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="input-icon password-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               name="Password" 
                               id="passwordField" 
                               placeholder="••••••••" 
                               required>
                        <i class="fas fa-eye toggle-password" id="togglePassword" onclick="togglePasswordVisibility()"></i>
                    </div>
                    <span class="text-danger" id="passwordError"></span>
                </div>

                <!-- CHECKBOX DE RECORDAR (con estilo personalizado) -->
                <div class="checkbox-group">
                    <input type="checkbox" name="RememberMe" id="rememberMe" <?php echo isset($_POST['RememberMe']) ? 'checked' : ''; ?>>
                    <label for="rememberMe">Recordar mi sesión</label>
                </div>

                <!-- BOTÓN DE INGRESAR -->
                <button type="submit" class="btn-auth" id="submitBtn">
                    <span>Ingresar al sistema</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- ENLACES ADICIONALES -->
            <div class="auth-footer">
                <p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
                <div class="back-link">
                    <a href="index.php"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para mostrar/ocultar contraseña -->
    <script>
    function togglePasswordVisibility() {
        const passwordField = document.getElementById('passwordField');
        const toggleIcon = document.getElementById('togglePassword');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
    </script>
    <script src="js/login.js"></script>
</body>
</html>