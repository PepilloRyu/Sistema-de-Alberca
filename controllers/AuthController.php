<?php
// controllers/AuthController.php
require_once __DIR__ . '/../includes/database.php';

class AuthController {
    private $conn;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
    }
    
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, nombre, email, password, id_rol, activo FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['usuario_id'] = $row['id'];
                $_SESSION['usuario_nombre'] = $row['nombre'];
                $_SESSION['usuario_email'] = $row['email'];
                $_SESSION['rol_id'] = $row['id_rol'];
                
                // Obtener nombre del rol
                $stmtRol = $this->conn->prepare("SELECT nombre_rol FROM cat_roles WHERE id_rol = ?");
                $stmtRol->bind_param("i", $row['id_rol']);
                $stmtRol->execute();
                $rolResult = $stmtRol->get_result();
                if ($rol = $rolResult->fetch_assoc()) {
                    $_SESSION['rol_nombre'] = $rol['nombre_rol'];
                }
                $stmtRol->close();
                
                // Actualizar último acceso
                $stmtUpdate = $this->conn->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
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
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['usuario_id']);
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
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
    
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: ../login.php');
            exit;
        }
    }
    
    public function requireRol($rol_id) {
        $this->requireAuth();
        if ($_SESSION['rol_id'] != $rol_id) {
            header('Location: ../index.php');
            exit;
        }
    }
}
?>