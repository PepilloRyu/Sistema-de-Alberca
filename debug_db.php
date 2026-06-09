<?php
// debug_db.php - Script para verificar conexión y datos de usuario
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 DEBUG - Verificación de Base de Datos</h1>";

// 1. Verificar conexión a la base de datos
echo "<h2>1. Probando conexión a MySQL...</h2>";

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'albercas';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("<span style='color:red'>❌ Error de conexión: " . $conn->connect_error . "</span>");
}
echo "<span style='color:green'>✅ Conexión exitosa a la base de datos</span><br>";

// 2. Verificar si la base de datos existe
echo "<h2>2. Verificando base de datos 'nuestras_albercas'...</h2>";
$result = $conn->query("SELECT DATABASE()");
$row = $result->fetch_row();
echo "Base de datos actual: <strong>" . $row[0] . "</strong><br>";

// 3. Verificar tablas necesarias
echo "<h2>3. Verificando tablas...</h2>";
$tables = ['usuarios', 'cat_roles'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<span style='color:green'>✅ Tabla '$table' existe</span><br>";
    } else {
        echo "<span style='color:red'>❌ Tabla '$table' NO existe</span><br>";
    }
}

// 4. Mostrar todos los usuarios en la tabla
echo "<h2>4. Usuarios registrados en la tabla 'usuarios':</h2>";
$result = $conn->query("SELECT id, nombre, email, id_rol, activo FROM usuarios");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr style='background:#333;color:white'><th>ID</th><th>Nombre</th><th>Email</th><th>id_rol</th><th>Activo</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['nombre'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['id_rol'] . "</td>";
        echo "<td>" . ($row['activo'] ? '✅ Activo' : '❌ Inactivo') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<span style='color:red'>❌ No hay usuarios registrados en la tabla 'usuarios'</span><br>";
}

// 5. Verificar roles
echo "<h2>5. Roles disponibles en 'cat_roles':</h2>";
$result = $conn->query("SELECT * FROM cat_roles");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id_rol'] . " - Nombre: " . $row['nombre_rol'] . "<br>";
    }
} else {
    echo "<span style='color:red'>❌ No hay roles registrados</span><br>";
}

// 6. Probar login con un email específico
echo "<h2>6. Probando login con un email específico:</h2>";
$test_email = "limpieza@hotmail.com"; // Cambia este email por el que estás usando
echo "Probando con email: <strong>$test_email</strong><br>";

$stmt = $conn->prepare("SELECT id, nombre, email, password, id_rol, activo FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $test_email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "<span style='color:green'>✅ Usuario encontrado</span><br>";
    echo "ID: " . $row['id'] . "<br>";
    echo "Nombre: " . $row['nombre'] . "<br>";
    echo "Email: " . $row['email'] . "<br>";
    echo "Rol ID: " . $row['id_rol'] . "<br>";
    echo "Activo: " . ($row['activo'] ? 'Si' : 'No') . "<br>";
    echo "Password hash: " . $row['password'] . "<br>";
    
    // Probar con una contraseña específica
    $test_password = "12345678"; // Cambia por la contraseña que estás usando
    echo "<br>Probando contraseña: <strong>$test_password</strong><br>";
    
    if (password_verify($test_password, $row['password'])) {
        echo "<span style='color:green;font-weight:bold'>✅ La contraseña es CORRECTA</span><br>";
    } else {
        echo "<span style='color:red;font-weight:bold'>❌ La contraseña es INCORRECTA</span><br>";
        
        // Mostrar información adicional sobre el hash
        $info = password_get_info($row['password']);
        echo "Información del hash:<br>";
        echo "- Algoritmo: " . $info['algoName'] . "<br>";
        echo "- Es Bcrypt? " . (str_starts_with($row['password'], '$2y$') ? 'Si' : 'No') . "<br>";
    }
} else {
    echo "<span style='color:red'>❌ Usuario NO encontrado con el email: $test_email</span><br>";
}
$stmt->close();

// 7. Sugerencia para crear un usuario de prueba
echo "<h2>7. Crear usuario de prueba (si no existe):</h2>";
$check_email = "prueba@test.com";
$check_stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$check_stmt->bind_param("s", $check_email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows == 0) {
    echo "No existe usuario de prueba. Puedes ejecutar este SQL para crear uno:<br>";
    echo "<pre style='background:#f4f4f4;padding:10px;border:1px solid #ddd;overflow:auto'>";
    echo "INSERT INTO usuarios (nombre, email, password, id_rol, activo) VALUES (\n";
    echo "    'Usuario Prueba',\n";
    echo "    'prueba@test.com',\n";
    echo "    '" . password_hash('12345678', PASSWORD_DEFAULT) . "',\n";
    echo "    2,\n";
    echo "    1\n";
    echo ");";
    echo "</pre>";
} else {
    echo "<span style='color:green'>✅ Ya existe un usuario de prueba</span>";
}
$check_stmt->close();

$conn->close();

// 8. Información de configuración
echo "<h2>8. Configuración actual:</h2>";
echo "Archivo de configuración: includes/config.php<br>";
if (file_exists('includes/config.php')) {
    echo "<span style='color:green'>✅ includes/config.php existe</span><br>";
    include 'includes/config.php';
    echo "DB_HOST: " . DB_HOST . "<br>";
    echo "DB_USER: " . DB_USER . "<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
} else {
    echo "<span style='color:red'>❌ includes/config.php NO existe</span><br>";
}

echo "<h2>9. Soluciones posibles:</h2>";
echo "<ul>";
echo "<li>Verifica que el email esté escrito exactamente igual en la base de datos</li>";
echo "<li>Verifica que la contraseña sea correcta (distingue mayúsculas/minúsculas)</li>";
echo "<li>Verifica que el campo 'activo' esté en 1</li>";
echo "<li>Verifica que la tabla 'usuarios' tenga datos</li>";
echo "<li>Verifica que 'id_rol' sea válido (exista en cat_roles)</li>";
echo "<li>Si el hash no se reconoce, puede que la contraseña no esté hasheada correctamente</li>";
echo "</ul>";
?>