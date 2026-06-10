<?php
declare(strict_types=1);

final class UsuarioModel extends Model {
 public function findByEmail(string $email): ?array {
   return $this->row("SELECT u.*,r.nombre rol_nombre FROM usuarios u LEFT JOIN roles r ON r.idRol=u.idRol WHERE LOWER(u.email)=LOWER(:e) LIMIT 1",['e'=>trim($email)]);
 }

 public function createPending(string $n,string $e,string $p): bool {
   $name = trim($n);
   $email = strtolower(trim($e));
   if($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($p)<8) return false;
   $ok = $this->execSql("INSERT INTO usuarios(nombre,email,password_hash,idRol,estado,creado_en) VALUES(:n,:e,:p,NULL,'pendiente',NOW())",[
     'n'=>$name,'e'=>$email,'p'=>password_hash($p,PASSWORD_BCRYPT,['cost'=>12])
   ]);
   if($ok) $this->audit(null,'usuarios','registro_pendiente',['email'=>$email]);
   return $ok;
 }

 public function updateAccess(int $id): void {
   $this->execSql("UPDATE usuarios SET ultimo_acceso=NOW() WHERE idUsuario=:id",['id'=>$id]);
   $this->audit($id,'auth','login_ok',['idUsuario'=>$id]);
 }

 public function roles(): array {
   return $this->query("SELECT idRol,nombre,descripcion,activo FROM roles WHERE activo=1 ORDER BY idRol");
 }

 public function users(): array {
   return $this->query("SELECT u.idUsuario,u.nombre,u.email,u.idRol,COALESCE(r.nombre,'Sin Rol') rol,u.estado,u.ultimo_acceso,u.creado_en,u.actualizado_en
     FROM usuarios u
     LEFT JOIN roles r ON r.idRol=u.idRol
     ORDER BY FIELD(u.estado,'pendiente','activo','inactivo'),u.creado_en DESC,u.nombre ASC");
 }

 public function assign(int $id,int $rol,string $estado, ?int $admin=null): bool {
   $estado = in_array($estado,['pendiente','activo','inactivo'],true) ? $estado : 'pendiente';
   $adminId = $admin ?? (int)($_SESSION['usuario_id'] ?? 0);
   if($id<=0 || $rol<=0) return false;
   $exists = $this->row("SELECT idRol FROM roles WHERE idRol=:r AND activo=1",['r'=>$rol]);
   if(!$exists) return false;

   $target = $this->row("SELECT idUsuario,idRol,estado FROM usuarios WHERE idUsuario=:id LIMIT 1",['id'=>$id]);
   if(!$target) return false;

   $wouldRemainAdmin = ($rol === 1 && $estado === 'activo');
   $isActiveAdmin = ((int)($target['idRol'] ?? 0) === 1 && ($target['estado'] ?? '') === 'activo');

   if($id === $adminId && !$wouldRemainAdmin) return false;

   if($isActiveAdmin && !$wouldRemainAdmin){
     $row = $this->row("SELECT COUNT(*) total FROM usuarios WHERE idRol=1 AND estado='activo'");
     if((int)($row['total'] ?? 0) <= 1) return false;
   }

   $affected = $this->execAffected("UPDATE usuarios SET idRol=:r,estado=:e,actualizado_en=NOW() WHERE idUsuario=:id",['r'=>$rol,'e'=>$estado,'id'=>$id]);
   $ok = $affected > 0 || ($affected === 0 && $this->row("SELECT idUsuario FROM usuarios WHERE idUsuario=:id AND idRol=:r AND estado=:e",['id'=>$id,'r'=>$rol,'e'=>$estado]));
   if($ok) $this->audit($adminId,'usuarios','asignar_rol',['idUsuario'=>$id,'idRol'=>$rol,'estado'=>$estado]);
   return (bool)$ok;
 }

 public function byRole(int $rol): array {
   return $this->query("SELECT idUsuario,nombre,email FROM usuarios WHERE idRol=:r AND estado='activo' ORDER BY nombre",['r'=>$rol]);
 }

 public function queryForDiagnostics(): array {
   return $this->query("SELECT idUsuario,nombre,email,password_hash,CHAR_LENGTH(password_hash) hash_len,idRol,estado,ultimo_acceso FROM usuarios ORDER BY idUsuario");
 }

 public function metrics(): array {
   $row = $this->row("SELECT COUNT(*) total,COALESCE(SUM(estado='activo'),0) activos,COALESCE(SUM(estado='pendiente'),0) pendientes FROM usuarios");
   return ['total'=>(int)($row['total'] ?? 0),'activos'=>(int)($row['activos'] ?? 0),'pendientes'=>(int)($row['pendientes'] ?? 0)];
 }

 public function securityMetrics(): array {
   $row = $this->row("SELECT COUNT(*) total,
       COALESCE(SUM(estado='activo'),0) activos,
       COALESCE(SUM(estado='pendiente'),0) pendientes,
       COALESCE(SUM(estado='inactivo'),0) inactivos,
       COALESCE(SUM(idRol IS NULL),0) sin_rol,
       COALESCE(SUM(ultimo_acceso IS NULL),0) sin_acceso,
       COALESCE(SUM(idRol=1 AND estado='activo'),0) admins
     FROM usuarios");
   return [
    'total'=>(int)($row['total'] ?? 0),'activos'=>(int)($row['activos'] ?? 0),'pendientes'=>(int)($row['pendientes'] ?? 0),
    'inactivos'=>(int)($row['inactivos'] ?? 0),'sin_rol'=>(int)($row['sin_rol'] ?? 0),'sin_acceso'=>(int)($row['sin_acceso'] ?? 0),'admins'=>(int)($row['admins'] ?? 0),
   ];
 }

 public function auditTrail(int $limit=12): array {
   $limit = max(1,min(40,$limit));
   return $this->query("SELECT a.entidad,a.accion,a.ip,a.creado_en,COALESCE(u.nombre,'Sistema') usuario
     FROM auditoria_sistema a
     LEFT JOIN usuarios u ON u.idUsuario=a.idUsuario
     ORDER BY a.creado_en DESC
     LIMIT {$limit}");
 }

 public function roleDistribution(): array {
   return $this->query("SELECT COALESCE(r.nombre,'Sin Rol') rol,COUNT(*) total
     FROM usuarios u
     LEFT JOIN roles r ON r.idRol=u.idRol
     GROUP BY COALESCE(r.nombre,'Sin Rol')
     ORDER BY total DESC, rol ASC");
 }
}
