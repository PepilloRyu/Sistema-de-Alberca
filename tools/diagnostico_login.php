<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');
require dirname(__DIR__).'/app/helpers/functions.php';
if(!is_local_request()){ http_response_code(403); exit('Herramienta disponible solo en CLI o localhost.'); }
date_default_timezone_set(config('timezone','America/Mexico_City'));
require ALBERCAS_ROOT.'/auth/Seguridad.php';
require ALBERCAS_ROOT.'/app/core/Database.php';
require ALBERCAS_ROOT.'/app/core/Model.php';
require ALBERCAS_ROOT.'/models/UsuarioModel.php';

$c = require ALBERCAS_ROOT.'/config/db.php';
echo "Diagnostico backend - Sistema de Albercas\n";
echo "Fecha: ".date('Y-m-d H:i:s')."\n\n";
echo "Base configurada:\n";
echo "host={$c['host']}\nport={$c['port']}\ndatabase={$c['database']}\nusername={$c['username']}\n\n";

$pdo = Database::connection();
if (!$pdo) {
  echo "ERROR: No hay conexion con MySQL.\n";
  echo "Detalle: ".(Database::error() ?: 'sin detalle')."\n\n";
  echo "Que revisar:\n";
  echo "1) Que MySQL este encendido en XAMPP.\n";
  echo "2) Que config/db.php tenga el puerto correcto: 3306 o 3307.\n";
  echo "3) Que exista la base albercas y hayas importado database/schema.sql.\n";
  exit;
}

echo "OK: conexion a MySQL establecida.\n\n";
$tables = ['usuarios','roles','albercas','aforo_movimientos','calidad_agua_registros','tickets_mantenimiento','ticket_seguimientos','turnos_limpieza','checklist_limpieza','mantenimientos_programados','equipos_alberca','equipo_revisiones','auditoria_sistema','notificaciones'];
echo "Tablas clave:\n";
foreach($tables as $table){
  try{
    $stmt=$pdo->query("SELECT COUNT(*) total FROM {$table}");
    $row=$stmt->fetch();
    echo "- {$table}: OK ({$row['total']} registros)\n";
  }catch(Throwable $e){
    echo "- {$table}: ERROR {$e->getMessage()}\n";
  }
}

echo "\nUsuarios demo y verificacion contra Admin123!:\n";
$usuarios = (new UsuarioModel())->queryForDiagnostics();
if (!$usuarios) {
  echo "ERROR: No pude leer usuarios o la tabla esta vacia.\n";
  echo "Detalle: ".(Model::lastError() ?: 'sin detalle')."\n";
  exit;
}
foreach ($usuarios as $u) {
  $ok = password_verify('Admin123!', (string)$u['password_hash']) ? 'OK' : 'FALLA';
  $rol = $u['idRol'] ?? 'NULL';
  echo "- {$u['email']} | idRol={$rol} | estado={$u['estado']} | hash_len={$u['hash_len']} | verify={$ok}\n";
}
