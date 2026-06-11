<?php
declare(strict_types=1);

final class OperacionModel extends Model {
 public function kpis(): array {
   $r = $this->row("SELECT COALESCE(SUM(CASE WHEN tipo_movimiento='entrada' THEN cantidad ELSE 0 END),0) entradas,
       COALESCE(SUM(CASE WHEN tipo_movimiento='salida' THEN cantidad ELSE 0 END),0) salidas,
       COALESCE(SUM(CASE WHEN tipo_movimiento='entrada' THEN cantidad ELSE -cantidad END),0) ocupacion
     FROM aforo_movimientos
     WHERE DATE(registrado_en)=CURDATE()");
   return ['entradas'=>(int)($r['entradas'] ?? 0),'salidas'=>(int)($r['salidas'] ?? 0),'ocupacion'=>max(0,(int)($r['ocupacion'] ?? 0))];
 }

 public function aforo(int $pool,string $tipo,int $cant,int $user): array {
   if(!in_array($tipo,['entrada','salida'],true) || $cant<1) return ['ok'=>false,'msg'=>'Movimiento invalido.'];
   if($pool<=0 || $user<=0) return ['ok'=>false,'msg'=>'Datos incompletos.'];
   $msg = 'No se pudo guardar el aforo.';

   $ok = $this->transaction(function(PDO $db) use ($pool,$tipo,$cant,$user,&$msg){
     $q = $db->prepare("SELECT a.idAlberca,a.nombre,a.capacidad_maxima,a.horario_apertura,a.horario_cierre,e.nombre estado_nombre,e.bloquea_aforo
       FROM albercas a
       JOIN catalogo_estados_alberca e ON e.idEstadoAlberca=a.idEstadoAlberca
       WHERE a.idAlberca=:id
       LIMIT 1
       FOR UPDATE");
     $q->execute(['id'=>$pool]);
     $selected = $q->fetch();
     if(!$selected){ $msg='Alberca no encontrada.'; return false; }

     $now = date('H:i:s');
     $open = (string)($selected['horario_apertura'] ?? '07:00:00');
     $close = (string)($selected['horario_cierre'] ?? '21:00:00');
     if($now < $open || $now >= $close){ $msg='Fuera del horario operativo: '.substr($open,0,5).' a '.substr($close,0,5).'.'; return false; }

     $occStmt = $db->prepare("SELECT COALESCE(SUM(CASE WHEN tipo_movimiento='entrada' THEN cantidad ELSE -cantidad END),0) ocupacion_actual
       FROM aforo_movimientos
       WHERE idAlberca=:id AND DATE(registrado_en)=CURDATE()");
     $occStmt->execute(['id'=>$pool]);
     $actual = max(0,(int)($occStmt->fetch()['ocupacion_actual'] ?? 0));
     $capacidad = max(0,(int)($selected['capacidad_maxima'] ?? 0));

     if($tipo==='entrada'){
       if((int)($selected['bloquea_aforo'] ?? 0)===1){ $msg='No se permiten entradas porque '.$selected['nombre'].' esta en estado: '.$selected['estado_nombre'].'.'; return false; }
       if($actual + $cant > $capacidad){ $msg='El movimiento excede la capacidad de '.$selected['nombre'].'.'; return false; }
     }
     if($tipo==='salida' && $actual - $cant < 0){ $msg='La salida excede la ocupacion actual de '.$selected['nombre'].'.'; return false; }

     $s = $db->prepare("INSERT INTO aforo_movimientos(idAlberca,tipo_movimiento,cantidad,registrado_por,registrado_en) VALUES(:a,:t,:c,:u,NOW())");
     $s->execute(['a'=>$pool,'t'=>$tipo,'c'=>$cant,'u'=>$user]);
     $newOcc = max(0,$actual + ($tipo==='entrada' ? $cant : -$cant));
     if($capacidad>0 && $newOcc/$capacidad >= 0.85){
       $exists = $db->prepare("SELECT idAlerta FROM alertas_alberca WHERE idAlberca=:a AND estado='abierta' AND titulo LIKE 'Aforo alto%' AND DATE(creada_en)=CURDATE() LIMIT 1");
       $exists->execute(['a'=>$pool]);
       if(!$exists->fetch()){
         $alertMsg = 'Ocupacion al '.round(($newOcc/$capacidad)*100).'% ('.$newOcc.'/'.$capacidad.')';
         $al = $db->prepare("INSERT INTO alertas_alberca(idAlberca,titulo,descripcion,nivel,estado,creada_por,creada_en) VALUES(:a,'Aforo alto detectado',:d,'alta','abierta',:u,NOW())");
         $al->execute(['a'=>$pool,'d'=>$alertMsg,'u'=>$user]);
       }
     }
     $msg='Aforo registrado correctamente.';
     return true;
   });
   if($ok) $this->audit($user,'aforo','registrar_movimiento',['idAlberca'=>$pool,'tipo'=>$tipo,'cantidad'=>$cant]);
   return ['ok'=>$ok,'msg'=>$ok?$msg:$msg];
 }

 public function recentAforo(int $limit=10): array {
   $limit=max(1,min(25,$limit));
   return $this->query("SELECT m.idMovimiento,m.tipo_movimiento,m.cantidad,m.registrado_en,a.nombre alberca,COALESCE(u.nombre,'Sistema') usuario
     FROM aforo_movimientos m
     JOIN albercas a ON a.idAlberca=m.idAlberca
     LEFT JOIN usuarios u ON u.idUsuario=m.registrado_por
     WHERE DATE(m.registrado_en)=CURDATE()
     ORDER BY m.registrado_en DESC,m.idMovimiento DESC
     LIMIT {$limit}");
 }

 public function quality(): array {
   return $this->query("SELECT c.*,a.nombre alberca
     FROM calidad_agua_registros c
     JOIN albercas a ON a.idAlberca=c.idAlberca
     INNER JOIN (
       SELECT idAlberca,MAX(idCalidadAgua) idMax
       FROM calidad_agua_registros
       GROUP BY idAlberca
     ) q ON q.idAlberca=c.idAlberca AND q.idMax=c.idCalidadAgua
     ORDER BY a.idAlberca");
 }

 public function saveQuality(array $d,int $u): bool {
   $pool=(int)($d['idAlberca'] ?? 0);
   $cloro=(float)($d['cloro_ppm'] ?? -1);
   $ph=(float)($d['ph'] ?? -1);
   $temp=(float)($d['temperatura_c'] ?? -100);
   $obs=trim((string)($d['observaciones'] ?? ''));
   if($pool<=0 || $u<=0 || $cloro<0 || $ph<0 || $ph>14 || $temp<0 || $temp>60) return false;
   if(!$this->row("SELECT idAlberca FROM albercas WHERE idAlberca=:a",['a'=>$pool])) return false;
   if(!$this->row("SELECT idUsuario FROM usuarios WHERE idUsuario=:u AND estado='activo'",['u'=>$u])) return false;
   $ok = $this->transaction(function(PDO $db) use ($pool,$cloro,$ph,$temp,$obs,$u){
     $s=$db->prepare("INSERT INTO calidad_agua_registros(idAlberca,cloro_ppm,ph,temperatura_c,observaciones,registrado_por,registrado_en) VALUES(:a,:c,:p,:t,:o,:u,NOW())");
     $s->execute(['a'=>$pool,'c'=>$cloro,'p'=>$ph,'t'=>$temp,'o'=>$obs,'u'=>$u]);
     return true;
   });
   if($ok) $this->audit($u,'calidad_agua','registrar_lectura',['idAlberca'=>$pool,'cloro'=>$cloro,'ph'=>$ph,'temperatura'=>$temp]);
   return $ok;
 }

 public function alerts(int $limit=12): array {
   $limit=max(1,min(50,$limit));
   return $this->query("SELECT al.*,a.nombre alberca
     FROM alertas_alberca al
     JOIN albercas a ON a.idAlberca=al.idAlberca
     WHERE al.estado='abierta'
     ORDER BY FIELD(al.nivel,'critica','alta','media','baja'),al.creada_en DESC
     LIMIT {$limit}");
 }

 public function hourlyFlow(): array {
   $labels = ['07','08','09','10','11','12','13','14','15','16','17','18','19','20'];
   $entries = array_fill_keys($labels, 0);
   $exits = array_fill_keys($labels, 0);
   $rows = $this->query("SELECT LPAD(HOUR(registrado_en),2,'0') hora,tipo_movimiento,COALESCE(SUM(cantidad),0) total
     FROM aforo_movimientos
     WHERE DATE(registrado_en)=CURDATE() AND HOUR(registrado_en) BETWEEN 7 AND 20
     GROUP BY HOUR(registrado_en),tipo_movimiento
     ORDER BY HOUR(registrado_en)");
   foreach($rows as $r){
     $h=(string)$r['hora'];
     if(!isset($entries[$h])) continue;
     if($r['tipo_movimiento']==='entrada') $entries[$h]=(int)$r['total'];
     if($r['tipo_movimiento']==='salida') $exits[$h]=(int)$r['total'];
   }
   return ['labels'=>array_values($labels),'entradas'=>array_values($entries),'salidas'=>array_values($exits)];
 }
}
