<?php
// includes/auth.php
require_once __DIR__ . '/database.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    
    // ==========================================
    // NUEVA FUNCIÓN: REGISTRO DE USUARIOS
    // ==========================================
    public static function register($nombre, $email, $password) {
        $conn = Database::getInstance()->getConnection();
        
        // 1. Verificar si el correo ya existe
        $stmtCheck = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmtCheck->bind_param("s", $email);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        
        if ($resultCheck->num_rows > 0) {
            $stmtCheck->close();
            return false; // El correo ya está registrado
        }
        $stmtCheck->close();
        
        // 2. Hashear (encriptar) la contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // 3. Insertar el nuevo usuario (Se guarda sin rol específico por ahora)
        $stmtInsert = $conn->prepare("INSERT INTO usuarios (nombre, email, password, activo) VALUES (?, ?, ?, 1)");
        $stmtInsert->bind_param("sss", $nombre, $email, $hashedPassword);
        
        $success = $stmtInsert->execute();
        $stmtInsert->close();
        
        return $success;
    }

    // ==========================================
    // FUNCIÓN: INICIO DE SESIÓN
    // ==========================================
    public static function login($email, $password) {
        $conn = Database::getInstance()->getConnection();
        
        $stmt = $conn->prepare("SELECT id, nombre, email, password, id_rol, activo FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['usuario_id'] = $row['id'];
                $_SESSION['usuario_nombre'] = $row['nombre'];
                $_SESSION['usuario_email'] = $row['email'];
                $_SESSION['rol_id'] = $row['id_rol'];
                
                $stmtRol = $conn->prepare("SELECT nombre_rol FROM cat_roles WHERE id_rol = ?");
                $stmtRol->bind_param("i", $row['id_rol']);
                $stmtRol->execute();
                $rolResult = $stmtRol->get_result();
                if ($rol = $rolResult->fetch_assoc()) {
                    $_SESSION['rol_nombre'] = $rol['nombre_rol'];
                }
                $stmtRol->close();
                
                $stmtUpdate = $conn->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                $stmtUpdate->bind_param("i", $row['id']);
                $stmtUpdate->execute();
                $stmtUpdate->close();
                
                $stmt->close();
                return true;
            }
        }
        $stmt->close();
        return false;
    }
    
    public static function logout() {
        session_destroy();
        return true;
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['usuario_id']);
    }
    
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'email' => $_SESSION['usuario_email'],
                'rol_id' => $_SESSION['rol_id'],
                'rol_nombre' => $_SESSION['rol_nombre'] ?? null
            ];
        }
        return null;
    }
}

function isLoggedIn() {
    return Auth::isLoggedIn();
}
?>