<?php
// includes/auth.php
require_once __DIR__ . '/database.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
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