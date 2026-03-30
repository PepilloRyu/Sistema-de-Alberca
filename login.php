<?php
// login.php
// Corregir las rutas - usar __DIR__ para asegurar la ruta correcta
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya está logueado, redirigir según su rol
if (isset($_SESSION['usuario_id'])) {
    $rol_id = $_SESSION['rol_id'] ?? 0;
    switch ($rol_id) {
        case 1:
            header("Location: usuarios/EncargadoDeAlberca/index.php");
            break;
        case 2:
            header("Location: usuarios/PersonalDeLimpieza/index.php");
            break;
        case 3:
            header("Location: usuarios/TecnicoDeMantenimiento/index.php");
            break;
        default:
            header("Location: index.php");
            break;
    }
    exit();
}

$error = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['Email'] ?? '');
    $password = $_POST['Password'] ?? '';
    $rememberMe = isset($_POST['RememberMe']);
    
    if (empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
        $errorMessage = 'Todos los campos son obligatorios';
    } else {
        // Conectar a la base de datos
        $conn = Database::getInstance()->getConnection();
        
        // Buscar usuario por email
        $stmt = $conn->prepare("SELECT id, nombre, email, password, id_rol, activo FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Verificar contraseña
            if (password_verify($password, $row['password'])) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $row['id'];
                $_SESSION['usuario_nombre'] = $row['nombre'];
                $_SESSION['usuario_email'] = $row['email'];
                $_SESSION['rol_id'] = $row['id_rol'];
                
                // Obtener nombre del rol
                $stmtRol = $conn->prepare("SELECT nombre_rol FROM cat_roles WHERE id_rol = ?");
                $stmtRol->bind_param("i", $row['id_rol']);
                $stmtRol->execute();
                $rolResult = $stmtRol->get_result();
                if ($rol = $rolResult->fetch_assoc()) {
                    $_SESSION['rol_nombre'] = $rol['nombre_rol'];
                }
                $stmtRol->close();
                
                // Actualizar último acceso
                $stmtUpdate = $conn->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                $stmtUpdate->bind_param("i", $row['id']);
                $stmtUpdate->execute();
                $stmtUpdate->close();
                
                $stmt->close();
                $conn->close();
                
                // Redirigir según el rol
                switch ($row['id_rol']) {
                    case 1:
                        header("Location: usuarios/EncargadoDeAlberca/index.php");
                        break;
                    case 2:
                        header("Location: usuarios/PersonalDeLimpieza/index.php");
                        break;
                    case 3:
                        header("Location: usuarios/TecnicoDeMantenimiento/index.php");
                        break;
                    default:
                        header("Location: index.php");
                        break;
                }
                exit();
            } else {
                $error = 'Email o contraseña incorrectos';
                $errorMessage = 'Email o contraseña incorrectos';
            }
        } else {
            $error = 'Email o contraseña incorrectos';
            $errorMessage = 'Email o contraseña incorrectos';
        }
        $stmt->close();
        $conn->close();
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
    
    <!-- CSS específico para login -->
    <link rel="stylesheet" href="css/login.css">
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

                <!-- CHECKBOX DE RECORDAR -->
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

    // Validación del formulario en el lado del cliente
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const password = document.getElementById('passwordField').value;
        let isValid = true;
        
        if (!email) {
            document.getElementById('emailError').textContent = 'El correo electrónico es obligatorio';
            isValid = false;
        } else {
            document.getElementById('emailError').textContent = '';
        }
        
        if (!password) {
            document.getElementById('passwordError').textContent = 'La contraseña es obligatoria';
            isValid = false;
        } else {
            document.getElementById('passwordError').textContent = '';
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    </script>
    <script src="js/login.js"></script>
</body>
</html>