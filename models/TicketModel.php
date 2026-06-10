<?php
declare(strict_types=1);

final class TicketModel extends Model {
 private static bool $expiryChecked = false;

 private function folio(): string {
   return 'TK-'.date('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,8));
 }

 private function descriptionWithContext(array $d): string {
   $desc = trim((string)($d['descripcion'] ?? ''));
   $ctx = [];
   $area = trim((string)($d['area_afectada'] ?? ''));
   $evid = trim((string)($d['evidencia'] ?? ''));
   if($area!=='') $ctx[]='Area: '.$area;
   if($evid!=='') $ctx[]='Evidencia: '.$evid;
   if(isset($d['origen'])) $ctx[]='Origen: '.trim((string)$d['origen']);
   elseif(isset($_SESSION['idRol']) && (int)$_SESSION['idRol']===3) $ctx[]='Origen: Limpieza';
   $full = ($ctx ? '['.implode(' | ',$ctx).'] ' : '').$desc;
   return mb_substr($full,0,2000);
 }

 private function closeExpiredTickets(): void {
   if(self::$expiryChecked || !$this->db) return;
   self::$expiryChecked = true;
   try{
     $stmt = $this->db->prepare("UPDATE tickets_mantenimiento t
       JOIN catalogo_estados_ticket e ON e.idEstadoTicket=t.idEstadoTicket
       SET t.idEstadoTicket=5,
           t.cerrado_en=NOW(),
           t.cierre_motivo='Cerrado automáticamente por tiempo sin seguimiento',
           t.actualizado_en=NOW()
       WHERE e.es_final=0
         AND TIMESTAMPDIFF(HOUR,COALESCE(t.ultimo_seguimiento_en,t.creado_en),NOW()) >= COALESCE((SELECT CAST(valor AS UNSIGNED) FROM configuraciones_sistema WHERE clave='ticket_auto_close_hours' LIMIT 1),12)");
     $stmt->execute();
     $closed = $stmt->rowCount();
     if($closed > 0) $this->audit(null,'tickets','cierre_automatico',['total'=>$closed]);
   }catch(Throwable $e){
     $this->fail($e,'auto_close_tickets');
   }
 }

 public function create(array $d,int $u): bool {
   $tipo=(int)($d['idTipoIncidencia'] ?? 0);
   $pool=(int)($d['idAlberca'] ?? 0);
   $prio=(int)($d['idPrioridad'] ?? 0);
   $descripcion=$this->descriptionWithContext($d);
   if($u<=0 || $tipo<=0 || $pool<=0 || $prio<=0 || $descripcion==='') return false;
   if(!$this->row("SELECT idUsuario FROM usuarios WHERE idUsuario=:u AND estado='activo'",['u'=>$u])) return false;
   if(!$this->row("SELECT idTipoIncidencia FROM catalogo_tipos_incidencia WHERE idTipoIncidencia=:t",['t'=>$tipo])) return false;
   if(!$this->row("SELECT idAlberca FROM albercas WHERE idAlberca=:a",['a'=>$pool])) return false;
   if(!$this->row("SELECT idPrioridad FROM catalogo_prioridades WHERE idPrioridad=:p",['p'=>$prio])) return false;

   $folio=$this->folio();
   $ok = $this->transaction(function(PDO $db) use ($folio,$tipo,$pool,$descripcion,$prio,$u){
     $s=$db->prepare("INSERT INTO tickets_mantenimiento(folio,idTipoIncidencia,idAlberca,descripcion,idPrioridad,idEstadoTicket,reportado_por,creado_en) VALUES(:f,:t,:a,:d,:p,1,:u,NOW())");
     $s->execute(['f'=>$folio,'t'=>$tipo,'a'=>$pool,'d'=>$descripcion,'p'=>$prio,'u'=>$u]);
     $ticketId=(int)$db->lastInsertId();
     $n=$db->prepare("INSERT INTO notificaciones(idUsuario,titulo,mensaje,creada_en) VALUES(NULL,'Nuevo ticket FIFO',:m,NOW())");
     $n->execute(['m'=>'Ticket '.$folio.' creado y pendiente de asignacion tecnica.']);
     return $ticketId>0;
   });
   if($ok) $this->audit($u,'tickets','crear',['folio'=>$folio,'idAlberca'=>$pool,'idTipoIncidencia'=>$tipo,'idPrioridad'=>$prio]);
   return $ok;
 }

 public function cats(): array {
   return [
     'tipos'=>$this->query("SELECT * FROM catalogo_tipos_incidencia ORDER BY nombre"),
     'prioridades'=>$this->query("SELECT * FROM catalogo_prioridades ORDER BY nivel DESC"),
     'estados'=>$this->query("SELECT * FROM catalogo_estados_ticket ORDER BY idEstadoTicket"),
   ];
 }

 public function queue(?int $tech=null,int $limit=40): array {
   $this->closeExpiredTickets();
   $where = $tech ? ' AND (t.asignado_a=:u OR t.asignado_a IS NULL)' : '';
   $params = $tech ? ['u'=>$tech] : [];
   $limit=max(1,min(100,$limit));
   return $this->query("SELECT t.*,a.nombre alberca,ti.nombre tipo,p.nombre prioridad,p.nivel prioridad_nivel,et.nombre estado,et.es_final,COALESCE(u.nombre,'Sin asignar') tecnico,
       COALESCE(rep.nombre,'Sistema') reportado_por_nombre,
       TIMESTAMPDIFF(MINUTE,t.creado_en,NOW()) minutos_abierto
     FROM tickets_mantenimiento t
     JOIN albercas a ON a.idAlberca=t.idAlberca
     JOIN catalogo_tipos_incidencia ti ON ti.idTipoIncidencia=t.idTipoIncidencia
     JOIN catalogo_prioridades p ON p.idPrioridad=t.idPrioridad
     JOIN catalogo_estados_ticket et ON et.idEstadoTicket=t.idEstadoTicket
     LEFT JOIN usuarios u ON u.idUsuario=t.asignado_a
     LEFT JOIN usuarios rep ON rep.idUsuario=t.reportado_por
     WHERE et.es_final=0 {$where}
     ORDER BY p.nivel DESC,t.creado_en ASC
     LIMIT {$limit}",$params);
 }

 public function recentByReporter(int $user): array {
   $this->closeExpiredTickets();
   return $this->query("SELECT t.*,a.nombre alberca,ti.nombre tipo,p.nombre prioridad,p.nivel prioridad_nivel,et.nombre estado,et.es_final,COALESCE(u.nombre,'Sin asignar') tecnico
     FROM tickets_mantenimiento t
     JOIN albercas a ON a.idAlberca=t.idAlberca
     JOIN catalogo_tipos_incidencia ti ON ti.idTipoIncidencia=t.idTipoIncidencia
     JOIN catalogo_prioridades p ON p.idPrioridad=t.idPrioridad
     JOIN catalogo_estados_ticket et ON et.idEstadoTicket=t.idEstadoTicket
     LEFT JOIN usuarios u ON u.idUsuario=t.asignado_a
     WHERE t.reportado_por=:u
     ORDER BY t.creado_en DESC
     LIMIT 20",['u'=>$user]);
 }

 public function history(?int $tech=null, int $limit=60): array {
   $this->closeExpiredTickets();
   $where = $tech ? "WHERE (t.asignado_a=:u OR t.asignado_a IS NULL)" : "";
   $params = $tech ? ['u'=>$tech] : [];
   $limit=max(1,min(120,$limit));
   return $this->query("SELECT t.*,a.nombre alberca,ti.nombre tipo,p.nombre prioridad,p.nivel prioridad_nivel,et.nombre estado,et.es_final,COALESCE(rep.nombre,'Sistema') reportado_por_nombre,COALESCE(u.nombre,'Sin asignar') tecnico,
       (SELECT COUNT(*) FROM ticket_seguimientos s WHERE s.idTicket=t.idTicket) seguimientos,
       (SELECT MAX(s.creado_en) FROM ticket_seguimientos s WHERE s.idTicket=t.idTicket) ultimo_evento,
       TIMESTAMPDIFF(MINUTE,t.creado_en,COALESCE(t.cerrado_en,t.ultimo_seguimiento_en,t.actualizado_en,NOW())) minutos_atencion
     FROM tickets_mantenimiento t
     JOIN albercas a ON a.idAlberca=t.idAlberca
     JOIN catalogo_tipos_incidencia ti ON ti.idTipoIncidencia=t.idTipoIncidencia
     JOIN catalogo_prioridades p ON p.idPrioridad=t.idPrioridad
     JOIN catalogo_estados_ticket et ON et.idEstadoTicket=t.idEstadoTicket
     LEFT JOIN usuarios rep ON rep.idUsuario=t.reportado_por
     LEFT JOIN usuarios u ON u.idUsuario=t.asignado_a
     {$where}
     ORDER BY COALESCE(t.cerrado_en,t.actualizado_en,t.ultimo_seguimiento_en,t.creado_en) DESC,t.creado_en DESC
     LIMIT {$limit}",$params);
 }

 public function assign(int $ticket,int $tech): bool {
   if($ticket<=0 || $tech<=0) return false;
   if(!$this->row("SELECT idUsuario FROM usuarios WHERE idUsuario=:u AND idRol=4 AND estado='activo'",['u'=>$tech])) return false;
   $ok = $this->transaction(function(PDO $db) use ($ticket,$tech){
     $s=$db->prepare("UPDATE tickets_mantenimiento t
       JOIN catalogo_estados_ticket e ON e.idEstadoTicket=t.idEstadoTicket
       SET t.asignado_a=:u,t.idEstadoTicket=2,t.asignado_en=COALESCE(t.asignado_en,NOW()),t.actualizado_en=NOW()
       WHERE t.idTicket=:t AND e.es_final=0 AND (t.asignado_a IS NULL OR t.asignado_a=:u2)");
     $s->execute(['u'=>$tech,'t'=>$ticket,'u2'=>$tech]);
     if($s->rowCount()<1) return false;
     $seg=$db->prepare("INSERT INTO ticket_seguimientos(idTicket,idUsuario,comentario,creado_en) VALUES(:t,:u,'Ticket tomado por tecnico',NOW())");
     $seg->execute(['t'=>$ticket,'u'=>$tech]);
     $n=$db->prepare("INSERT INTO notificaciones(idUsuario,titulo,mensaje,creada_en) VALUES(:u,'Ticket asignado','Tomaste un ticket FIFO para seguimiento.',NOW())");
     $n->execute(['u'=>$tech]);
     return true;
   });
   if($ok) $this->audit($tech,'tickets','asignar',['idTicket'=>$ticket,'asignado_a'=>$tech]);
   return $ok;
 }

 public function follow(int $ticket,int $user,string $comment,int $estado): bool {
   $comment=mb_substr(trim($comment),0,2000);
   if($ticket<=0 || $user<=0 || $estado<=0 || $comment==='') return false;
   if(!$this->row("SELECT idUsuario FROM usuarios WHERE idUsuario=:u AND idRol=4 AND estado='activo'",['u'=>$user])) return false;
   $ok = $this->transaction(function(PDO $db) use ($ticket,$user,$comment,$estado){
     $state=$db->prepare("SELECT idEstadoTicket,es_final,nombre FROM catalogo_estados_ticket WHERE idEstadoTicket=:e LIMIT 1");
     $state->execute(['e'=>$estado]);
     $st=$state->fetch();
     if(!$st) return false;
     $own=$db->prepare("SELECT t.idTicket FROM tickets_mantenimiento t JOIN catalogo_estados_ticket et ON et.idEstadoTicket=t.idEstadoTicket WHERE t.idTicket=:t AND et.es_final=0 AND (t.asignado_a=:u OR t.asignado_a IS NULL) LIMIT 1");
     $own->execute(['t'=>$ticket,'u'=>$user]);
     if(!$own->fetch()) return false;
     $s=$db->prepare("INSERT INTO ticket_seguimientos(idTicket,idUsuario,comentario,creado_en) VALUES(:t,:u,:c,NOW())");
     $s->execute(['t'=>$ticket,'u'=>$user,'c'=>$comment]);
     $final=(int)$st['es_final']===1;
     $sql="UPDATE tickets_mantenimiento SET asignado_a=COALESCE(asignado_a,:u),idEstadoTicket=:e,ultimo_seguimiento_en=NOW(),actualizado_en=NOW()".
       ($final ? ",cerrado_en=NOW(),cierre_motivo=:m" : "")." WHERE idTicket=:t";
     $params=['u'=>$user,'e'=>$estado,'t'=>$ticket];
     if($final) $params['m']=$comment;
     $up=$db->prepare($sql);
     $up->execute($params);
     return $up->rowCount() > 0;
   });
   if($ok) $this->audit($user,'tickets','seguimiento',['idTicket'=>$ticket,'idEstadoTicket'=>$estado]);
   return $ok;
 }

 public function metrics(): array {
   $this->closeExpiredTickets();
   $row=$this->row("SELECT COUNT(*) total,
       COALESCE(SUM(et.es_final=0),0) abiertos,
       COALESCE(SUM(p.nivel>=3 AND et.es_final=0),0) criticos
     FROM tickets_mantenimiento t
     JOIN catalogo_estados_ticket et ON et.idEstadoTicket=t.idEstadoTicket
     JOIN catalogo_prioridades p ON p.idPrioridad=t.idPrioridad");
   return ['total'=>(int)($row['total'] ?? 0),'abiertos'=>(int)($row['abiertos'] ?? 0),'criticos'=>(int)($row['criticos'] ?? 0)];
 }

 public function statusDistribution(): array {
   $this->closeExpiredTickets();
   return $this->query("SELECT et.nombre estado,COUNT(t.idTicket) total
     FROM catalogo_estados_ticket et
     LEFT JOIN tickets_mantenimiento t ON t.idEstadoTicket=et.idEstadoTicket
     GROUP BY et.idEstadoTicket,et.nombre
     ORDER BY et.idEstadoTicket");
 }

 public function priorityDistribution(): array {
   $this->closeExpiredTickets();
   return $this->query("SELECT p.nombre prioridad,p.nivel,COUNT(t.idTicket) total
     FROM catalogo_prioridades p
     LEFT JOIN tickets_mantenimiento t ON t.idPrioridad=p.idPrioridad AND t.idEstadoTicket IN (1,2,3)
     GROUP BY p.idPrioridad,p.nombre,p.nivel
     ORDER BY p.nivel DESC");
 }
}
