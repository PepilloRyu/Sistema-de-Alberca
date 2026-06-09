<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

// Si ya está logueado, redirigir
if (isLoggedIn()) {  // Cambiado de estaLogueado() a isLoggedIn()
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$errors = [
    'Email' => '',
    'Password' => '',
    'ConfirmPassword' => ''
];
$formData = [
    'nombre' => '',  // Agregado nombre
    'Email' => '',
    'Password' => '',
    'ConfirmPassword' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $password = $_POST['Password'] ?? '';
    $confirm = $_POST['ConfirmPassword'] ?? '';
    
    // Guardar datos del formulario para mostrarlos en caso de error
    $formData = [
        'nombre' => $nombre,
        'Email' => $email,
        'Password' => $password,
        'ConfirmPassword' => $confirm
    ];
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Todos los campos son obligatorios';
    } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden';
        $errors['ConfirmPassword'] = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
        $errors['Password'] = 'Mínimo 6 caracteres';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo electrónico válido';
        $errors['Email'] = 'Correo electrónico inválido';
    } else {
        // Usar la función register de la clase Auth
        $result = Auth::register($nombre, $email, $password);
        
        if ($result) {
            $success = '¡Registro exitoso! <a href="login.php">Inicia sesión aquí</a>';
            // Limpiar datos del formulario
            $formData = [
                'nombre' => '',
                'Email' => '',
                'Password' => '',
                'ConfirmPassword' => ''
            ];
        } else {
            $error = 'El correo electrónico ya está registrado';
            $errors['Email'] = 'Este correo ya está registrado';
        }
    }
}

// Función para mantener valores en el formulario
function old($field) {
    global $formData;
    return htmlspecialchars($formData[$field] ?? '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Nuestras Albercas</title>
    
    <!-- Fuentes y librerías externas -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- CSS específico para login/registro -->
    <link rel="stylesheet" href="css/login.css">

</head>
<body>
    <div class="auth-container animate__animated animate__fadeIn">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-user-plus"></i>
                <h2>Crear cuenta</h2>
                <p>Regístrate para comenzar</p>
            </div>

            <!-- Mensaje de error general -->
            <?php if (!empty($error)): ?>
                <div class="alert-error error-shake">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- Mensaje de éxito -->
            <?php if (!empty($success)): ?>
                <div class="alert-success animate__animated animate__bounceIn">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                    <div style="margin-top: 10px; font-size: 0.9rem;">
                        <i class="fas fa-spinner fa-spin"></i> Redirigiendo al login...
                    </div>
                </div>
                <script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 3000);
                </script>
            <?php endif; ?>

            <form method="post" action="registro.php" id="registerForm" <?php echo !empty($success) ? 'style="display:none;"' : ''; ?>>
                <!-- NOMBRE COMPLETO -->
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nombre completo</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" 
                               name="nombre" 
                               id="nombre" 
                               placeholder="Juan Pérez" 
                               value="<?php echo old('nombre'); ?>"
                               required>
                    </div>
                </div>

                <!-- EMAIL -->
                <div class="form-group <?php echo !empty($errors['Email']) ? 'input-error' : ''; ?>">
                    <label><i class="fas fa-envelope"></i> Correo electrónico</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" 
                               name="Email" 
                               id="email" 
                               placeholder="ejemplo@correo.com" 
                               value="<?php echo old('Email'); ?>"
                               required>
                    </div>
                    <?php if (!empty($errors['Email'])): ?>
                        <span class="text-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $errors['Email']; ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- CONTRASEÑA -->
                <div class="form-group <?php echo !empty($errors['Password']) ? 'input-error' : ''; ?>">
                    <label><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="input-icon password-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               name="Password" 
                               id="passwordField" 
                               placeholder="••••••••" 
                               value="<?php echo old('Password'); ?>"
                               required>
                        <i class="fas fa-eye toggle-password" id="togglePassword" onclick="togglePasswordVisibility()"></i>
                    </div>
                    <?php if (!empty($errors['Password'])): ?>
                        <span class="text-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $errors['Password']; ?>
                        </span>
                    <?php endif; ?>
                    
                    <!-- Requisitos de contraseña -->
                    <div class="password-requirements" id="passwordRequirements">
                        <div class="requirement" id="reqLength">
                            <i class="fas fa-circle"></i> Mínimo 6 caracteres
                        </div>
                        <div class="requirement" id="reqNumber">
                            <i class="fas fa-circle"></i> Al menos un número
                        </div>
                        <div class="requirement" id="reqUppercase">
                            <i class="fas fa-circle"></i> Al menos una mayúscula
                        </div>
                        <div class="requirement" id="reqLowercase">
                            <i class="fas fa-circle"></i> Al menos una minúscula
                        </div>
                    </div>
                </div>

                <!-- CONFIRMAR CONTRASEÑA -->
                <div class="form-group <?php echo !empty($errors['ConfirmPassword']) ? 'input-error' : ''; ?>">
                    <label><i class="fas fa-lock"></i> Confirmar contraseña</label>
                    <div class="input-icon password-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               name="ConfirmPassword" 
                               id="confirmPasswordField" 
                               placeholder="••••••••" 
                               value="<?php echo old('ConfirmPassword'); ?>"
                               required>
                    </div>
                    <?php if (!empty($errors['ConfirmPassword'])): ?>
                        <span class="text-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $errors['ConfirmPassword']; ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Términos y condiciones -->
                <div class="checkbox-group" style="margin-bottom: 20px;">
                    <input type="checkbox" name="Terms" id="terms" required>
                    <label for="terms">Acepto los <a href="#" style="color: #ffaa00;">Términos y Condiciones</a></label>
                </div>

                <!-- BOTÓN DE REGISTRO -->
                <button type="submit" class="btn-auth" id="submitBtn">
                    <span>Registrarse</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- ENLACES ADICIONALES -->
            <div class="auth-footer" <?php echo !empty($success) ? 'style="margin-top: 20px;"' : ''; ?>>
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
                <div class="back-link">
                    <a href="index.php"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para mostrar/ocultar contraseña
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

        // Validación en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const nombreInput = document.getElementById('nombre');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('passwordField');
            const confirmInput = document.getElementById('confirmPasswordField');
            const submitBtn = document.getElementById('submitBtn');
            const termsCheckbox = document.getElementById('terms');
            
            // Elementos de requisitos
            const reqBox = document.getElementById('passwordRequirements');
            const reqLength = document.getElementById('reqLength');
            const reqNumber = document.getElementById('reqNumber');
            const reqUppercase = document.getElementById('reqUppercase');
            const reqLowercase = document.getElementById('reqLowercase');

            // Mostrar requisitos al enfocar contraseña
            if (passwordInput) {
                passwordInput.addEventListener('focus', function() {
                    reqBox.classList.add('show');
                });

                // Ocultar requisitos al hacer clic fuera
                passwordInput.addEventListener('blur', function() {
                    setTimeout(() => {
                        reqBox.classList.remove('show');
                    }, 200);
                });

                // Validar requisitos de contraseña en tiempo real
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    
                    // Longitud mínima
                    if (password.length >= 6) {
                        reqLength.classList.add('met');
                        reqLength.innerHTML = '<i class="fas fa-check-circle"></i> Mínimo 6 caracteres';
                    } else {
                        reqLength.classList.remove('met');
                        reqLength.innerHTML = '<i class="fas fa-circle"></i> Mínimo 6 caracteres';
                    }
                    
                    // Número
                    if (/\d/.test(password)) {
                        reqNumber.classList.add('met');
                        reqNumber.innerHTML = '<i class="fas fa-check-circle"></i> Al menos un número';
                    } else {
                        reqNumber.classList.remove('met');
                        reqNumber.innerHTML = '<i class="fas fa-circle"></i> Al menos un número';
                    }
                    
                    // Mayúscula
                    if (/[A-Z]/.test(password)) {
                        reqUppercase.classList.add('met');
                        reqUppercase.innerHTML = '<i class="fas fa-check-circle"></i> Al menos una mayúscula';
                    } else {
                        reqUppercase.classList.remove('met');
                        reqUppercase.innerHTML = '<i class="fas fa-circle"></i> Al menos una mayúscula';
                    }
                    
                    // Minúscula
                    if (/[a-z]/.test(password)) {
                        reqLowercase.classList.add('met');
                        reqLowercase.innerHTML = '<i class="fas fa-check-circle"></i> Al menos una minúscula';
                    } else {
                        reqLowercase.classList.remove('met');
                        reqLowercase.innerHTML = '<i class="fas fa-circle"></i> Al menos una minúscula';
                    }
                });
            }

            // Validar coincidencia de contraseñas
            if (confirmInput) {
                confirmInput.addEventListener('input', function() {
                    if (this.value !== passwordInput.value) {
                        this.style.borderColor = '#dc3545';
                        this.parentElement.style.borderColor = '#dc3545';
                        this.parentElement.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
                    } else {
                        this.style.borderColor = '#28a745';
                        this.parentElement.style.borderColor = '#28a745';
                        this.parentElement.style.boxShadow = '0 0 0 3px rgba(40, 167, 69, 0.1)';
                    }
                });
            }

            // Validación antes de enviar
            if (form) {
                form.addEventListener('submit', function(e) {
                    let isValid = true;
                    let errorMessages = [];

                    // Validar nombre
                    if (nombreInput && nombreInput.value.trim().length < 3) {
                        isValid = false;
                        errorMessages.push('El nombre debe tener al menos 3 caracteres');
                        nombreInput.style.borderColor = '#dc3545';
                    }

                    // Validar email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (emailInput && !emailRegex.test(emailInput.value)) {
                        isValid = false;
                        errorMessages.push('Ingresa un correo electrónico válido');
                        emailInput.parentElement.style.borderColor = '#dc3545';
                    }

                    // Validar contraseña
                    if (passwordInput && passwordInput.value.length < 6) {
                        isValid = false;
                        errorMessages.push('La contraseña debe tener al menos 6 caracteres');
                        passwordInput.parentElement.style.borderColor = '#dc3545';
                    }

                    // Validar coincidencia
                    if (confirmInput && passwordInput && passwordInput.value !== confirmInput.value) {
                        isValid = false;
                        errorMessages.push('Las contraseñas no coinciden');
                        confirmInput.parentElement.style.borderColor = '#dc3545';
                    }

                    // Validar términos
                    if (termsCheckbox && !termsCheckbox.checked) {
                        isValid = false;
                        errorMessages.push('Debes aceptar los términos y condiciones');
                        termsCheckbox.parentElement.style.color = '#dc3545';
                    }

                    if (!isValid) {
                        e.preventDefault();
                        alert('Por favor corrige los siguientes errores:\n- ' + errorMessages.join('\n- '));
                    } else {
                        // Deshabilitar botón para evitar doble envío
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span>Procesando...</span><i class="fas fa-spinner fa-spin"></i>';
                    }
                });
            }

            // Restaurar estilos al escribir
            const inputs = [emailInput, passwordInput, confirmInput, nombreInput];
            inputs.forEach(input => {
                if (input) {
                    input.addEventListener('input', function() {
                        this.style.borderColor = '';
                        if (this.parentElement) {
                            this.parentElement.style.borderColor = '';
                            this.parentElement.style.boxShadow = '';
                        }
                    });
                }
            });
            
            if (termsCheckbox) {
                termsCheckbox.addEventListener('change', function() {
                    this.parentElement.style.color = '';
                });
            }
        });
    </script>
</body>
</html>