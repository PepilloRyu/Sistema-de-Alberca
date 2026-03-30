<?php
// includes/auth.php
session_start();
require_once 'database.php';

class Auth {
    
    // Verificar si el usuario está logueado
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    // Iniciar sesión de usuario
    public static function login($email, $password) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Preparar consulta para evitar inyección SQL
        $stmt = $conn->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verificar contraseña
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_rol'] = $user['rol'] ?? 'usuario';
                $_SESSION['login_time'] = time();
                
                // Actualizar último acceso
                $updateStmt = $conn->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                
                return true;
            }
        }
        
        return false;
    }
    
    // Registrar nuevo usuario
    public static function register($nombre, $email, $password, $rol = 'usuario') {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Verificar si el email ya existe
        $checkStmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            return false; // Email ya registrado
        }
        
        // Hashear contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar nuevo usuario
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol, fecha_registro) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $nombre, $email, $hashedPassword, $rol);
        
        if ($stmt->execute()) {
            return $conn->insert_id;
        }
        
        return false;
    }
    
    // Cerrar sesión
    public static function logout() {
        session_unset();
        session_destroy();
        return true;
    }
    
    // Obtener datos del usuario actual
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("SELECT id, nombre, email, rol, fecha_registro, ultimo_acceso FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                return $result->fetch_assoc();
            }
        }
        
        return null;
    }
    
    // Redireccionar si no está logueado
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header("Location: login.php");
            exit();
        }
    }
    
    // Redireccionar si no es admin
    public static function requireAdmin() {
        self::requireLogin();
        $user = self::getCurrentUser();
        if (!$user || $user['rol'] !== 'admin') {
            header("Location: index.php");
            exit();
        }
    }
    
    // Verificar si la sesión ha expirado
    public static function isSessionExpired($timeout = 3600) {
        if (isset($_SESSION['login_time'])) {
            return (time() - $_SESSION['login_time']) > $timeout;
        }
        return true;
    }
}

// ==================== FUNCIONES HELPER ====================

// Verificar si está logueado
function isLoggedIn() {
    return Auth::isLoggedIn();
}

// Redireccionar si no está logueado
function requireLogin() {
    Auth::requireLogin();
}

// Redireccionar si no es admin
function requireAdmin() {
    Auth::requireAdmin();
}

// Obtener usuario actual (para usar en index.php y otras páginas)
function getCurrentUser() {
    return Auth::getCurrentUser();
}

// Alias de getCurrentUser para compatibilidad con tu index.php
function usuarioActual() {
    return Auth::getCurrentUser();
}

// Verificar si el usuario actual es admin
function esAdmin() {
    $user = Auth::getCurrentUser();
    return $user && isset($user['rol']) && $user['rol'] === 'admin';
}

// Función helper para login (compatibilidad)
function loginUsuario($email, $password) {
    $result = [
        'success' => false,
        'message' => 'Email o contraseña incorrectos'
    ];
    
    if (Auth::login($email, $password)) {
        $result['success'] = true;
        $result['message'] = 'Login exitoso';
    }
    
    return $result;
}

// Función helper para registro (compatibilidad)
function registrarUsuario($nombre, $email, $password) {
    $result = [
        'success' => false,
        'message' => 'Error al registrar usuario'
    ];
    
    $userId = Auth::register($nombre, $email, $password);
    
    if ($userId) {
        $result['success'] = true;
        $result['message'] = 'Usuario registrado exitosamente';
        $result['user_id'] = $userId;
    } else {
        $result['message'] = 'El correo electrónico ya está registrado';
    }
    
    return $result;
}
?>