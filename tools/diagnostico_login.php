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
echo "Diagnóstico backend - Sistema de Albercas\n";
echo "Fecha: ".date('Y-m-d H:i:s')."\n\n";
echo "Base configurada:\n";
echo "host={$c['host']}\nport={$c['port']}\ndatabase={$c['database']}\nusername={$c['username']}\n\n";

$pdo = Database::connection();
if (!$pdo) {
  echo "ERROR: No hay conexión con MySQL.\n";
  echo "Detalle: ".(Database::error() ?: 'sin detalle')."\n\n";
  echo "Qué revisar:\n";
  echo "1) Que MySQL esté encendido en XAMPP.\n";
  echo "2) Que config/db.php tenga el puerto correcto: 3306 o 3307.\n";
  echo "3) Que exista la base albercas y hayas importado database/schema.sql.\n";
  exit(1);
}

echo "OK: conexión a MySQL establecida.\n\n";
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

echo "\nUsuarios reales registrados:\n";
$usuarios = (new UsuarioModel())->queryForDiagnostics();
if (!$usuarios) {
  echo "\n- No hay usuarios. Crea el primer administrador con: php tools/crear_admin_inicial.php\n";
  exit(0);
}
$admins = 0;
foreach ($usuarios as $u) {
  $rol = $u['idRol'] ?? 'NULL';
  $estado = (string)($u['estado'] ?? '');
  if ((int)($u['idRol'] ?? 0) === 1 && $estado === 'activo') $admins++;
  echo "\n- {$u['email']} | idRol={$rol} | estado={$estado} | hash_len={$u['hash_len']}";
}
echo "\n\nAdministradores activos: {$admins}\n";
if ($admins < 1) echo "ACCIÓN: crea o activa un administrador real antes de operar el sistema.\n";
