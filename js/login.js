// js/login.js

// Función para mostrar/ocultar contraseña
function togglePasswordVisibility() {
    const passwordField = document.getElementById('passwordField');
    const toggleIcon = document.getElementById('togglePassword');
    
    if (passwordField) {
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            if (toggleIcon) {
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        } else {
            passwordField.type = 'password';
            if (toggleIcon) {
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    }
}

// Validación del formulario en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('passwordField');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const loginForm = document.getElementById('loginForm');
    
    // Validación de email en tiempo real
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const email = this.value.trim();
            if (email === '') {
                if (emailError) emailError.textContent = 'El correo electrónico es obligatorio';
            } else if (!isValidEmail(email)) {
                if (emailError) emailError.textContent = 'Ingrese un correo electrónico válido';
            } else {
                if (emailError) emailError.textContent = '';
            }
        });
    }
    
    // Validación de contraseña en tiempo real
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            if (password === '') {
                if (passwordError) passwordError.textContent = 'La contraseña es obligatoria';
            } else if (password.length < 6) {
                if (passwordError) passwordError.textContent = 'La contraseña debe tener al menos 6 caracteres';
            } else {
                if (passwordError) passwordError.textContent = '';
            }
        });
    }
    
    // Validación al enviar el formulario
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = emailInput ? emailInput.value.trim() : '';
            const password = passwordInput ? passwordInput.value : '';
            let isValid = true;
            
            if (!email) {
                if (emailError) emailError.textContent = 'El correo electrónico es obligatorio';
                isValid = false;
            } else if (!isValidEmail(email)) {
                if (emailError) emailError.textContent = 'Ingrese un correo electrónico válido';
                isValid = false;
            } else {
                if (emailError) emailError.textContent = '';
            }
            
            if (!password) {
                if (passwordError) passwordError.textContent = 'La contraseña es obligatoria';
                isValid = false;
            } else if (password.length < 6) {
                if (passwordError) passwordError.textContent = 'La contraseña debe tener al menos 6 caracteres';
                isValid = false;
            } else {
                if (passwordError) passwordError.textContent = '';
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});

// Función para validar formato de email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Función para mostrar mensajes de error
function showError(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.style.display = 'block';
    }
}

// Función para ocultar mensajes de error
function hideError(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = '';
        element.style.display = 'none';
    }
}

// Animación de carga al enviar formulario
document.addEventListener('submit', function(e) {
    const submitBtn = e.target.querySelector('.btn-auth');
    if (submitBtn && e.target.id === 'loginForm') {
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ingresando...';
        submitBtn.disabled = true;
        
        // Restaurar después de 3 segundos en caso de error
        setTimeout(function() {
            if (submitBtn.disabled) {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }, 3000);
    }
});