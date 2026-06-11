<?php
declare(strict_types=1);

final class LimpiezaModel extends Model {
 public function checklist(?int $user=null): array {
   $where = $user ? ' AND (cl.asignado_a=:u OR cl.asignado_a IS NULL)' : '';
   $params = $user ? ['u'=>$user] : [];
   return $this->query("SELECT cl.*,a.nombre alberca,ar.nombre area,ta.nombre tarea,COALESCE(u.nombre,'Equipo general') responsable
     FROM checklist_limpieza cl
     JOIN albercas a ON a.idAlberca=cl.idAlberca
     JOIN catalogo_areas_limpieza ar ON ar.idAreaLimpieza=cl.idAreaLimpieza
     JOIN catalogo_tareas_limpieza ta ON ta.idTareaLimpieza=cl.idTareaLimpieza
     LEFT JOIN usuarios u ON u.idUsuario=cl.asignado_a
     WHERE cl.fecha=CURDATE() {$where}
     ORDER BY cl.completado ASC,cl.hora_limite ASC,cl.idChecklist ASC",$params);
 }

 public function shifts(?int $user=null): array {
   $where = $user ? ' AND tl.idUsuario=:u' : '';
   $params = $user ? ['u'=>$user] : [];
   return $this->query("SELECT tl.*,u.nombre empleado,a.nombre alberca,ar.nombre area
     FROM turnos_limpieza tl
     JOIN usuarios u ON u.idUsuario=tl.idUsuario
     JOIN albercas a ON a.idAlberca=tl.idAlberca
     JOIN catalogo_areas_limpieza ar ON ar.idAreaLimpieza=tl.idAreaLimpieza
     WHERE tl.fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 7 DAY) {$where}
     ORDER BY tl.fecha,tl.hora_inicio",$params);
 }

 public function complete(int $id,string $obs, ?int $user=null): bool {
   if($id<=0) return false;
   $where = $user ? ' AND (asignado_a=:u OR asignado_a IS NULL)' : '';
   $params = ['o'=>mb_substr(trim($obs),0,255),'id'=>$id];
   if($user) $params['u']=$user;
   $affected = $this->execAffected("UPDATE checklist_limpieza SET completado=1,completado_en=NOW(),observaciones=:o,actualizado_en=NOW() WHERE idChecklist=:id {$where} AND completado=0",$params);
   $ok = $affected > 0;
   if($ok) $this->audit($user ?? (int)($_SESSION['usuario_id'] ?? 0),'limpieza','completar_checklist',['idChecklist'=>$id]);
   return $ok;
 }

 public function tasks(): array {
   return $this->query("SELECT * FROM catalogo_tareas_limpieza ORDER BY nombre");
 }

 public function areas(): array {
   return $this->query("SELECT * FROM catalogo_areas_limpieza ORDER BY nombre");
 }

 public function assignShift(array $d,int $admin): bool {
   $user=(int)($d['idUsuario'] ?? 0);
   $pool=(int)($d['idAlberca'] ?? 0);
   $area=(int)($d['idAreaLimpieza'] ?? 0);
   $fecha=(string)($d['fecha'] ?? '');
   $inicio=(string)($d['hora_inicio'] ?? '');
   $fin=(string)($d['hora_fin'] ?? '');
   if($user<=0 || $pool<=0 || $area<=0 || !$this->validDate($fecha) || !$this->validTime($inicio) || !$this->validTime($fin) || $inicio>=$fin) return false;
   if(!$this->row("SELECT idUsuario FROM usuarios WHERE idUsuario=:u AND idRol=3 AND estado='activo'",['u'=>$user])) return false;
   if(!$this->row("SELECT idAlberca FROM albercas WHERE idAlberca=:a",['a'=>$pool])) return false;
   if(!$this->row("SELECT idAreaLimpieza FROM catalogo_areas_limpieza WHERE idAreaLimpieza=:ar",['ar'=>$area])) return false;
   $ok = $this->transaction(function(PDO $db) use ($user,$pool,$area,$fecha,$inicio,$fin,$admin){
     $s=$db->prepare("INSERT INTO turnos_limpieza(idUsuario,idAlberca,idAreaLimpieza,fecha,hora_inicio,hora_fin,estado,creado_por,creado_en) VALUES(:u,:a,:ar,:f,:hi,:hf,'asignado',:c,NOW())");
     $s->execute(['u'=>$user,'a'=>$pool,'ar'=>$area,'f'=>$fecha,'hi'=>$inicio,'hf'=>$fin,'c'=>$admin]);
     $tasks=$db->query("SELECT idTareaLimpieza FROM catalogo_tareas_limpieza ORDER BY idTareaLimpieza")->fetchAll(PDO::FETCH_COLUMN);
     $ins=$db->prepare("INSERT INTO checklist_limpieza(fecha,idAlberca,idAreaLimpieza,idTareaLimpieza,asignado_a,hora_limite,completado,observaciones,creado_en)
       SELECT :f,:a,:ar,:t,:u,:lim,0,'Generado por turno asignado',NOW()
       WHERE NOT EXISTS (
         SELECT 1 FROM checklist_limpieza
         WHERE fecha=:f2 AND idAlberca=:a2 AND idAreaLimpieza=:ar2 AND idTareaLimpieza=:t2 AND asignado_a=:u2
       )");
     foreach($tasks as $taskId){
       $ins->execute(['f'=>$fecha,'a'=>$pool,'ar'=>$area,'t'=>(int)$taskId,'u'=>$user,'lim'=>$fin,'f2'=>$fecha,'a2'=>$pool,'ar2'=>$area,'t2'=>(int)$taskId,'u2'=>$user]);
     }
     $n=$db->prepare("INSERT INTO notificaciones(idUsuario,titulo,mensaje,creada_en) VALUES(:u,'Turno de limpieza asignado',:m,NOW())");
     $n->execute(['u'=>$user,'m'=>'Tienes turno el '.$fecha.' de '.$inicio.' a '.$fin]);
     return true;
   });
   if($ok) $this->audit($admin,'limpieza','asignar_turno',['idUsuario'=>$user,'idAlberca'=>$pool,'idAreaLimpieza'=>$area,'fecha'=>$fecha,'inicio'=>$inicio,'fin'=>$fin]);
   return $ok;
 }

 public function history(?int $user=null,int $days=30): array {
   $days=max(1,min(365,$days));
   $where=$user?' AND (cl.asignado_a=:u OR cl.asignado_a IS NULL)':'';
   $params=$user?['u'=>$user]:[];
   return $this->query("SELECT cl.*,a.nombre alberca,ar.nombre area,ta.nombre tarea,COALESCE(u.nombre,'Equipo general') responsable,
       CASE WHEN cl.completado=1 THEN 'Completada' ELSE 'Pendiente' END estado_operativo
     FROM checklist_limpieza cl
     JOIN albercas a ON a.idAlberca=cl.idAlberca
     JOIN catalogo_areas_limpieza ar ON ar.idAreaLimpieza=cl.idAreaLimpieza
     JOIN catalogo_tareas_limpieza ta ON ta.idTareaLimpieza=cl.idTareaLimpieza
     LEFT JOIN usuarios u ON u.idUsuario=cl.asignado_a
     WHERE cl.fecha >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY) {$where}
     ORDER BY cl.fecha DESC, cl.completado ASC, cl.hora_limite DESC
     LIMIT 100",$params);
 }

 public function historyMetrics(?int $user=null,int $days=30): array {
   $days=max(1,min(365,$days));
   $where=$user?' AND (asignado_a=:u OR asignado_a IS NULL)':'';
   $params=$user?['u'=>$user]:[];
   $m=$this->row("SELECT COUNT(*) total,
       COALESCE(SUM(completado=1),0) completas,
       COALESCE(SUM(completado=0),0) pendientes,
       COUNT(DISTINCT idAlberca) albercas_cubiertas
     FROM checklist_limpieza
     WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY) {$where}",$params);
   return ['total'=>(int)($m['total'] ?? 0),'completas'=>(int)($m['completas'] ?? 0),'pendientes'=>(int)($m['pendientes'] ?? 0),'albercas_cubiertas'=>(int)($m['albercas_cubiertas'] ?? 0)];
 }

 public function historyByPool(?int $user=null,int $days=30): array {
   $days=max(1,min(365,$days));
   $where=$user?' AND (cl.asignado_a=:u OR cl.asignado_a IS NULL)':'';
   $params=$user?['u'=>$user]:[];
   return $this->query("SELECT a.nombre alberca,COUNT(cl.idChecklist) total,COALESCE(SUM(cl.completado=1),0) completas
     FROM albercas a
     LEFT JOIN checklist_limpieza cl ON cl.idAlberca=a.idAlberca AND cl.fecha>=DATE_SUB(CURDATE(),INTERVAL {$days} DAY) {$where}
     GROUP BY a.idAlberca,a.nombre
     ORDER BY a.idAlberca",$params);
 }

 public function historyByArea(?int $user=null,int $days=30): array {
   $days=max(1,min(365,$days));
   $where=$user?' AND (cl.asignado_a=:u OR cl.asignado_a IS NULL)':'';
   $params=$user?['u'=>$user]:[];
   return $this->query("SELECT ar.nombre area,COUNT(cl.idChecklist) total,COALESCE(SUM(cl.completado=1),0) completas
     FROM catalogo_areas_limpieza ar
     LEFT JOIN checklist_limpieza cl ON cl.idAreaLimpieza=ar.idAreaLimpieza AND cl.fecha>=DATE_SUB(CURDATE(),INTERVAL {$days} DAY) {$where}
     GROUP BY ar.idAreaLimpieza,ar.nombre
     ORDER BY total DESC, ar.nombre
     LIMIT 8",$params);
 }

 public function metrics(): array {
   $row = $this->row("SELECT COUNT(*) total,
       COALESCE(SUM(completado=1),0) completas,
       COALESCE(SUM(completado=0),0) pendientes
     FROM checklist_limpieza
     WHERE fecha=CURDATE()");
   return ['total'=>(int)($row['total'] ?? 0),'completas'=>(int)($row['completas'] ?? 0),'pendientes'=>(int)($row['pendientes'] ?? 0)];
 }

 private function validDate(string $date): bool {
   $d=DateTime::createFromFormat('Y-m-d',$date);
   return $d && $d->format('Y-m-d')===$date;
 }
 private function validTime(string $time): bool {
   foreach(['H:i','H:i:s'] as $fmt){
     $t=DateTime::createFromFormat($fmt,$time);
     $errors=DateTime::getLastErrors();
     if($t && ($errors===false || ((int)$errors['warning_count']===0 && (int)$errors['error_count']===0)) && $t->format($fmt)===$time) return true;
   }
   return false;
 }
}
