<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');
require dirname(__DIR__).'/app/helpers/functions.php';
if(!is_local_request()){ http_response_code(403); exit('Herramienta disponible solo en CLI o localhost.'); }
require ALBERCAS_ROOT.'/app/core/Database.php';
require ALBERCAS_ROOT.'/app/core/Model.php';

echo "Auditoria backend - Sistema de Albercas\n";
echo "Fecha: ".date('Y-m-d H:i:s')."\n";
echo "PHP: ".PHP_VERSION."\n";
echo "PDO MySQL: ".(extension_loaded('pdo_mysql')?'OK':'FALTA')."\n";
echo "mbstring: ".(extension_loaded('mbstring')?'OK':'FALTA (hay polyfills basicos)')."\n\n";

$info = Database::info();
echo "Conexion configurada: {$info['host']}:{$info['port']} / {$info['database']}\n";
if(!$info['ok']){
  echo "ERROR conexion: ".($info['error'] ?: 'sin detalle')."\n";
  echo "Revisa XAMPP/MySQL, puerto 3306/3307, usuario, password e importacion de database/schema.sql.\n";
  exit(1);
}
echo "Conexion MySQL: OK\n\n";

$pdo = Database::connection();
$required = [
 'roles'=>['idRol','nombre','activo'],
 'usuarios'=>['idUsuario','nombre','email','password_hash','idRol','estado','ultimo_acceso'],
 'catalogo_estados_alberca'=>['idEstadoAlberca','nombre','clase_ui','bloquea_aforo'],
 'albercas'=>['idAlberca','nombre','capacidad_maxima','idEstadoAlberca','horario_apertura','horario_cierre'],
 'aforo_movimientos'=>['idMovimiento','idAlberca','tipo_movimiento','cantidad','registrado_por','registrado_en'],
 'calidad_agua_registros'=>['idCalidadAgua','idAlberca','cloro_ppm','ph','temperatura_c','registrado_por','registrado_en'],
 'alertas_alberca'=>['idAlerta','idAlberca','titulo','descripcion','nivel','estado','creada_por','creada_en'],
 'catalogo_tipos_incidencia'=>['idTipoIncidencia','nombre'],
 'catalogo_prioridades'=>['idPrioridad','nombre','nivel'],
 'catalogo_estados_ticket'=>['idEstadoTicket','nombre','es_final'],
 'tickets_mantenimiento'=>['idTicket','folio','idTipoIncidencia','idAlberca','descripcion','idPrioridad','idEstadoTicket','reportado_por','asignado_a','creado_en','asignado_en','ultimo_seguimiento_en','actualizado_en','cerrado_en','cierre_motivo'],
 'ticket_seguimientos'=>['idSeguimiento','idTicket','idUsuario','comentario','creado_en'],
 'catalogo_tipos_mantenimiento'=>['idTipoMantenimiento','nombre'],
 'mantenimientos_programados'=>['idMantenimiento','idAlberca','idTipoMantenimiento','asignado_a','fecha_programada','hora_inicio','hora_fin','estado','descripcion','creado_por','creado_en'],
 'equipos_alberca'=>['idEquipo','idAlberca','nombre','tipo','numero_serie','estado','ultima_revision','proxima_revision'],
 'equipo_revisiones'=>['idRevision','idEquipo','estado','ultima_revision','proxima_revision','comentario','revisado_por','revisado_en'],
 'catalogo_areas_limpieza'=>['idAreaLimpieza','nombre'],
 'catalogo_tareas_limpieza'=>['idTareaLimpieza','nombre','descripcion'],
 'turnos_limpieza'=>['idTurno','idUsuario','idAlberca','idAreaLimpieza','fecha','hora_inicio','hora_fin','estado','creado_por','creado_en'],
 'checklist_limpieza'=>['idChecklist','fecha','idAlberca','idAreaLimpieza','idTareaLimpieza','asignado_a','hora_limite','completado','completado_en','observaciones'],
 'configuraciones_sistema'=>['clave','valor'],
 'notificaciones'=>['idNotificacion','idUsuario','titulo','mensaje','leida','creada_en'],
 'auditoria_sistema'=>['idAuditoria','idUsuario','entidad','accion','detalle','ip','user_agent','creado_en'],
];

$errors = 0;
foreach($required as $table=>$columns){
  $stmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:t");
  $stmt->execute(['t'=>$table]);
  $present = array_map('strval',$stmt->fetchAll(PDO::FETCH_COLUMN));
  if(!$present){ echo "[ERROR] Tabla faltante: {$table}\n"; $errors++; continue; }
  $missing = array_values(array_diff($columns,$present));
  if($missing){ echo "[ERROR] {$table}: faltan columnas ".implode(', ',$missing)."\n"; $errors++; }
  else { echo "[OK] {$table}\n"; }
}

echo "\nConteos clave:\n";
foreach(array_keys($required) as $table){
  try{ $row=$pdo->query("SELECT COUNT(*) total FROM {$table}")->fetch(); echo "- {$table}: ".(int)($row['total'] ?? 0)."\n"; }
  catch(Throwable $e){ echo "- {$table}: ERROR {$e->getMessage()}\n"; $errors++; }
}

$warnings = 0;
echo "\nValidación sin datos demo:\n";
$demoChecks = [
  'usuarios demo por nombre/correo' => "SELECT COUNT(*) total FROM usuarios WHERE nombre LIKE '%Demo%' OR email IN ('admin@albercas.com','encargado@albercas.com','limpieza@albercas.com','tecnico@albercas.com','pendiente@albercas.com','admin@albercas.local','encargado@albercas.local','limpieza@albercas.local','tecnico@albercas.local','pendiente@albercas.local')",
  'tickets semilla conocidos' => "SELECT COUNT(*) total FROM tickets_mantenimiento WHERE folio IN ('TK-20260609-0001','TK-20260609-0002','TK-20260608-AX401','TK-20260608-BF210','TK-20260607-CM090','TK-20260606-VM311','TK-20260605-INF77')",
  'equipos semilla conocidos' => "SELECT COUNT(*) total FROM equipos_alberca WHERE numero_serie IN ('FLT-PR-001','BMP-PR-001','DOS-PR-001','BMP-FM-001','FLT-FM-001','DOS-INF-001','BMP-INF-001','FLT-VM-001','CAL-VM-001','BMP-DPT-001','FLT-DPT-001')",
  'auditoría artificial de seed' => "SELECT COUNT(*) total FROM auditoria_sistema WHERE accion IN ('seed_inicial','migracion_backend_real','migracion_backend_audit_fixes')",
];
foreach($demoChecks as $label=>$sql){
  try{
    $row=$pdo->query($sql)->fetch();
    $total=(int)($row['total'] ?? 0);
    if($total>0){ echo "[ERROR] {$label}: {$total}\n"; $errors++; }
    else echo "[OK] {$label}: 0\n";
  }catch(Throwable $e){ echo "[ERROR] {$label}: {$e->getMessage()}\n"; $errors++; }
}

try{
  $row=$pdo->query("SELECT COUNT(*) total FROM usuarios WHERE idRol=1 AND estado='activo'")->fetch();
  $admins=(int)($row['total'] ?? 0);
  if($admins<1){ echo "[AVISO] No hay administrador activo. Ejecuta: php tools/crear_admin_inicial.php\n"; $warnings++; }
  else echo "[OK] administradores activos: {$admins}\n";
}catch(Throwable $e){ echo "[ERROR] No se pudo validar administrador activo: {$e->getMessage()}\n"; $errors++; }

$result = $errors===0 ? 'OK, esquema compatible y sin datos demo detectados.' : 'ERROR, corrige tablas/columnas o limpia datos demo marcados.';
if($warnings>0 && $errors===0) $result .= ' Hay avisos operativos por atender.';
echo "\nResultado: {$result}\n";
exit($errors===0?0:1);
