<?php
// check.php - Verificar conexión a BD y sesión
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

echo "<h2>🔍 Verificación del Sistema</h2>";

// 1. Verificar conexión a MySQL
echo "<h3>📊 1. Conexión a Base de Datos:</h3>";
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "✅ Conexión exitosa a MySQL<br>";
        echo "📁 Base de datos: " . DB_NAME . "<br>";
        echo "🖥️ Servidor: " . DB_HOST . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
}

// 2. Verificar sesión
echo "<h3>👤 2. Estado de Sesión:</h3>";
session_start();

if (isset($_SESSION['user_id'])) {
    echo "✅ Sesión activa<br>";
    echo "👋 Usuario ID: " . $_SESSION['user_id'] . "<br>";
    echo "📧 Email: " . ($_SESSION['user_email'] ?? 'No disponible') . "<br>";
    echo "👤 Nombre: " . ($_SESSION['user_name'] ?? 'No disponible') . "<br>";
} else {
    echo "❌ No hay sesión activa<br>";
    echo "<a href='login.php'>🔐 Iniciar sesión</a><br>";
}

// 3. Verificar tabla usuarios
echo "<h3>📋 3. Tabla de Usuarios:</h3>";
try {
    $result = $conn->query("SHOW TABLES LIKE 'usuarios'");
    if ($result->num_rows > 0) {
        echo "✅ Tabla 'usuarios' existe<br>";
        
        // Contar usuarios
        $count = $conn->query("SELECT COUNT(*) as total FROM usuarios");
        $total = $count->fetch_assoc();
        echo "📊 Total de usuarios registrados: " . $total['total'] . "<br>";
        
        // Mostrar usuarios
        $users = $conn->query("SELECT id, nombre, email, rol FROM usuarios");
        if ($users->num_rows > 0) {
            echo "<br><strong>Usuarios registrados:</strong><br>";
            echo "<ul>";
            while ($user = $users->fetch_assoc()) {
                echo "<li>ID: {$user['id']} - {$user['nombre']} ({$user['email']}) - Rol: {$user['rol']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "❌ Tabla 'usuarios' NO existe<br>";
        echo "💡 Ejecuta este SQL para crearla:<br>";
        echo "<pre>CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(50) DEFAULT 'usuario',
    activo BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);</pre>";
    }
} catch (Exception $e) {
    echo "❌ Error al verificar tabla: " . $e->getMessage() . "<br>";
}

// 4. Enlaces rápidos
echo "<h3>🔗 4. Enlaces útiles:</h3>";
echo "<a href='index.php'>🏠 Ir al inicio</a><br>";
echo "<a href='login.php'>🔐 Iniciar sesión</a><br>";
echo "<a href='registro.php'>📝 Registrarse</a><br>";
if (isset($_SESSION['user_id'])) {
    echo "<a href='logout.php'>🚪 Cerrar sesión</a><br>";
}
?>