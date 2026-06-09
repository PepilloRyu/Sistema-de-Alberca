<?php
// controllers/DashboardController.php
require_once __DIR__ . '/../includes/auth.php';

class DashboardController {
    
    // TIEMPO LÍMITE DE INACTIVIDAD (En segundos) -> 15 minutos = 900
    private $timeout_seconds = 900; 
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // 1. Verificar si el usuario intentó saltarse el login
        if (!Auth::isLoggedIn()) {
            header('Location: ../../login.php');
            exit;
        }

        // 2. CONTROL DE INACTIVIDAD (TIMEOUT)
        if (isset($_SESSION['ultima_actividad'])) {
            // Calculamos cuánto tiempo ha pasado desde su último clic/recarga
            $tiempo_inactivo = time() - $_SESSION['ultima_actividad'];
            
            if ($tiempo_inactivo > $this->timeout_seconds) {
                // Si pasó más del tiempo límite, destruimos todo
                Auth::logout();
                header('Location: ../../login.php?error=timeout');
                exit;
            }
        }
        // Si todo está bien, actualizamos su reloj a la hora actual
        $_SESSION['ultima_actividad'] = time();
    }

    public function verificarAcceso($rolRequerido) {
        $user = Auth::getCurrentUser();
        if ($user['rol_id'] != 1 && $user['rol_id'] != $rolRequerido) {
            header('Location: ../../index.php');
            exit;
        }
        return $user;
    }
}
?>