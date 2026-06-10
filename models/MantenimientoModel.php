<?php
declare(strict_types=1);

final class MantenimientoModel extends Model {
 public function schedule(?int $tech=null, bool $futureOnly=true, int $limit=30): array {
   $where=[]; $params=[];
   if($futureOnly) $where[]="mp.fecha_programada>=CURDATE()";
   if($tech){ $where[]="mp.asignado_a=:u"; $params['u']=$tech; }
   $sqlWhere = $where ? 'WHERE '.implode(' AND ',$where) : '';
   $limit=max(1,min(100,$limit));
   return $this->query("SELECT mp.*,a.nombre alberca,u.nombre tecnico,tm.nombre tipo
     FROM mantenimientos_programados mp
     JOIN albercas a ON a.idAlberca=mp.idAlberca
     JOIN usuarios u ON u.idUsuario=mp.asignado_a
     JOIN catalogo_tipos_mantenimiento tm ON tm.idTipoMantenimiento=mp.idTipoMantenimiento
     {$sqlWhere}
     ORDER BY mp.fecha_programada ASC,mp.hora_inicio ASC
     LIMIT {$limit}",$params);
 }

 public function types(): array {
   return $this->query("SELECT * FROM catalogo_tipos_mantenimiento ORDER BY nombre");
 }

 public function program(array $d,int $admin): bool {
   $pool=(int)($d['idAlberca'] ?? 0);
   $type=(int)($d['idTipoMantenimiento'] ?? 0);
   $tech=(int)($d['asignado_a'] ?? 0);
   $date=(string)($d['fecha_programada'] ?? '');
   $start=(string)($d['hora_inicio'] ?? '');
   $end=(string)($d['hora_fin'] ?? '');
   $desc=mb_substr(trim((string)($d['descripcion'] ?? '')),0,255);
   if($pool<=0 || $type<=0 || $tech<=0 || !$this->validDate($date) || !$this->validTime($start) || !$this->validTime($end) || $start>=$end) return false;
   if(!$this->row("SELECT idAlberca FROM albercas WHERE idAlberca=:a",['a'=>$pool])) return false;
   if(!$this->row("SELECT idTipoMantenimiento FROM catalogo_tipos_mantenimiento WHERE idTipoMantenimiento=:t",['t'=>$type])) return false;
   if(!$this->row("SELECT idUsuario FROM usuarios WHERE idUsuario=:u AND idRol=4 AND estado='activo'",['u'=>$tech])) return false;

   $ok = $this->transaction(function(PDO $db) use ($pool,$type,$tech,$date,$start,$end,$desc,$admin){
     $s=$db->prepare("INSERT INTO mantenimientos_programados(idAlberca,idTipoMantenimiento,asignado_a,fecha_programada,hora_inicio,hora_fin,estado,descripcion,creado_por,creado_en) VALUES(:a,:t,:u,:f,:hi,:hf,'programado',:d,:c,NOW())");
     $s->execute(['a'=>$pool,'t'=>$type,'u'=>$tech,'f'=>$date,'hi'=>$start,'hf'=>$end,'d'=>$desc,'c'=>$admin]);
     $mId=(int)$db->lastInsertId();
     $n=$db->prepare("INSERT INTO notificaciones(idUsuario,titulo,mensaje,creada_en) VALUES(:u,'Mantenimiento asignado',:m,NOW())");
     $n->execute(['u'=>$tech,'m'=>'Tienes mantenimiento programado para '.$date.' de '.$start.' a '.$end]);
     return $mId>0;
   });
   if($ok) $this->audit($admin,'mantenimiento','programar',['idAlberca'=>$pool,'idTipoMantenimiento'=>$type,'asignado_a'=>$tech,'fecha'=>$date,'inicio'=>$start,'fin'=>$end]);
   return $ok;
 }

 public function equipment(): array {
   return $this->query("SELECT eq.*,a.nombre alberca,
       (SELECT er.comentario FROM equipo_revisiones er WHERE er.idEquipo=eq.idEquipo ORDER BY er.revisado_en DESC,er.idRevision DESC LIMIT 1) ultimo_comentario,
       (SELECT u.nombre FROM equipo_revisiones er LEFT JOIN usuarios u ON u.idUsuario=er.revisado_por WHERE er.idEquipo=eq.idEquipo ORDER BY er.revisado_en DESC,er.idRevision DESC LIMIT 1) ultimo_tecnico
     FROM equipos_alberca eq
     JOIN albercas a ON a.idAlberca=eq.idAlberca
     ORDER BY FIELD(eq.estado,'critico','fuera_servicio','revision','operativo'),COALESCE(eq.proxima_revision,'2999-12-31'),a.idAlberca,eq.tipo,eq.nombre");
 }

 public function history(?int $tech=null, int $limit=40): array {
   $where = $tech ? "WHERE mp.asignado_a=:u" : "";
   $params = $tech ? ['u'=>$tech] : [];
   $limit=max(1,min(100,$limit));
   return $this->query("SELECT mp.*,a.nombre alberca,u.nombre tecnico,tm.nombre tipo,
       TIMESTAMPDIFF(MINUTE,CONCAT(mp.fecha_programada,' ',mp.hora_inicio),CONCAT(mp.fecha_programada,' ',mp.hora_fin)) duracion_min
     FROM mantenimientos_programados mp
     JOIN albercas a ON a.idAlberca=mp.idAlberca
     JOIN usuarios u ON u.idUsuario=mp.asignado_a
     JOIN catalogo_tipos_mantenimiento tm ON tm.idTipoMantenimiento=mp.idTipoMantenimiento
     {$where}
     ORDER BY mp.fecha_programada DESC,mp.hora_inicio DESC
     LIMIT {$limit}",$params);
 }

 public function updateEquipment(int $id,string $estado,string $ultima,string $proxima,string $comentario='',?int $user=null): bool {
   $allowed=['operativo','revision','critico','fuera_servicio'];
   if($id<=0 || !in_array($estado,$allowed,true)) return false;
   if(!$this->row("SELECT idEquipo FROM equipos_alberca WHERE idEquipo=:id",['id'=>$id])) return false;
   $ultima = $this->validDate($ultima) ? $ultima : date('Y-m-d');
   $proxima = $this->validDate($proxima) ? $proxima : date('Y-m-d',strtotime('+30 days'));
   $comment = mb_substr(trim($comentario),0,500);
   $userId = $user ?: (int)($_SESSION['usuario_id'] ?? 0);
   $ok = $this->transaction(function(PDO $db) use ($id,$estado,$ultima,$proxima,$comment,$userId){
     $s=$db->prepare("UPDATE equipos_alberca SET estado=:estado,ultima_revision=:ultima,proxima_revision=:proxima WHERE idEquipo=:id");
     $s->execute(['estado'=>$estado,'ultima'=>$ultima,'proxima'=>$proxima,'id'=>$id]);
     $r=$db->prepare("INSERT INTO equipo_revisiones(idEquipo,estado,ultima_revision,proxima_revision,comentario,revisado_por,revisado_en) VALUES(:id,:estado,:ultima,:proxima,:comentario,:u,NOW())");
     $r->execute(['id'=>$id,'estado'=>$estado,'ultima'=>$ultima,'proxima'=>$proxima,'comentario'=>$comment,'u'=>$userId ?: null]);
     return true;
   });
   if($ok) $this->audit($userId,'equipos','actualizar_revision',['idEquipo'=>$id,'estado'=>$estado,'ultima_revision'=>$ultima,'proxima_revision'=>$proxima]);
   return $ok;
 }

 public function metrics(): array {
   $row = $this->row("SELECT COUNT(*) total,
       COALESCE(SUM(fecha_programada=CURDATE()),0) hoy,
       COALESCE(SUM(estado IN ('programado','en_proceso')),0) activos
     FROM mantenimientos_programados
     WHERE fecha_programada BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 14 DAY)");
   return ['total'=>(int)($row['total'] ?? 0),'hoy'=>(int)($row['hoy'] ?? 0),'activos'=>(int)($row['activos'] ?? 0)];
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
