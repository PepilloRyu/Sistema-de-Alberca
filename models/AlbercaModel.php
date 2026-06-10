<?php
declare(strict_types=1);

final class AlbercaModel extends Model {
 public function all(): array {
   return $this->query("SELECT a.*,e.nombre estado_nombre,e.clase_ui,e.bloquea_aforo,
       COALESCE((SELECT SUM(CASE WHEN m.tipo_movimiento='entrada' THEN m.cantidad ELSE -m.cantidad END)
                 FROM aforo_movimientos m WHERE m.idAlberca=a.idAlberca AND DATE(m.registrado_en)=CURDATE()),0) ocupacion_actual
     FROM albercas a
     JOIN catalogo_estados_alberca e ON e.idEstadoAlberca=a.idEstadoAlberca
     ORDER BY a.idAlberca");
 }

 public function dashboard(): array {
   $rows = $this->query("SELECT a.idAlberca,a.nombre,a.capacidad_maxima,a.ubicacion,a.uso_eventos,a.horario_apertura,a.horario_cierre,
       e.idEstadoAlberca,e.nombre estado_nombre,e.clase_ui,e.bloquea_aforo,
       COALESCE(SUM(CASE WHEN m.tipo_movimiento='entrada' THEN m.cantidad ELSE -m.cantidad END),0) ocupacion_actual,
       (SELECT CONCAT(c.cloro_ppm,' ppm · pH ',c.ph,' · ',c.temperatura_c,'°C')
        FROM calidad_agua_registros c
        WHERE c.idAlberca=a.idAlberca
        ORDER BY c.registrado_en DESC,c.idCalidadAgua DESC
        LIMIT 1) ultimo_quimico
     FROM albercas a
     JOIN catalogo_estados_alberca e ON e.idEstadoAlberca=a.idEstadoAlberca
     LEFT JOIN aforo_movimientos m ON m.idAlberca=a.idAlberca AND DATE(m.registrado_en)=CURDATE()
     GROUP BY a.idAlberca,a.nombre,a.capacidad_maxima,a.ubicacion,a.uso_eventos,a.horario_apertura,a.horario_cierre,e.idEstadoAlberca,e.nombre,e.clase_ui,e.bloquea_aforo
     ORDER BY a.idAlberca");
   return array_map(function($x){
     $x['ocupacion_actual'] = max(0,(int)($x['ocupacion_actual'] ?? 0));
     $x['ultimo_quimico'] = $x['ultimo_quimico'] ?: 'Sin registro';
     return $x;
   }, $rows);
 }

 public function estados(): array {
   return $this->query("SELECT * FROM catalogo_estados_alberca ORDER BY idEstadoAlberca");
 }

 public function status(int $id,int $estado, ?int $user=null): bool {
   if($id<=0 || $estado<=0) return false;
   $exists = $this->row("SELECT idEstadoAlberca FROM catalogo_estados_alberca WHERE idEstadoAlberca=:e",['e'=>$estado]);
   if(!$exists) return false;
   $affected = $this->execAffected("UPDATE albercas SET idEstadoAlberca=:e,actualizado_en=NOW() WHERE idAlberca=:id",['e'=>$estado,'id'=>$id]);
   $ok = $affected > 0 || ($affected === 0 && (bool)$this->row("SELECT idAlberca FROM albercas WHERE idAlberca=:id AND idEstadoAlberca=:e",['id'=>$id,'e'=>$estado]));
   if($ok) $this->audit($user ?? (int)($_SESSION['usuario_id'] ?? 0),'albercas','actualizar_estado',['idAlberca'=>$id,'idEstadoAlberca'=>$estado]);
   return (bool)$ok;
 }
}
