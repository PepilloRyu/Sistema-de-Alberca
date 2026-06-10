<?php
declare(strict_types=1);

abstract class Model {
 protected ?PDO $db;
 protected static ?string $lastError = null;

 public function __construct(){
   $this->db = Database::connection();
 }

 public static function lastError(): ?string { return self::$lastError; }
 public static function clearLastError(): void { self::$lastError = null; }
 protected function connected(): bool { return $this->db instanceof PDO; }

 protected function query(string $sql, array $p=[]): array {
   self::$lastError = null;
   if(!$this->db){ self::$lastError = Database::error() ?: 'Sin conexion a la base de datos.'; return []; }
   try{
     $s = $this->db->prepare($sql);
     foreach($p as $k=>$v){
       $param = is_int($k) ? $k + 1 : ':'.ltrim((string)$k, ':');
       $type = is_int($v) ? PDO::PARAM_INT : (is_bool($v) ? PDO::PARAM_BOOL : (is_null($v) ? PDO::PARAM_NULL : PDO::PARAM_STR));
       $s->bindValue($param, $v, $type);
     }
     $s->execute();
     return $s->fetchAll();
   }catch(Throwable $e){
     $this->fail($e, $sql);
     return [];
   }
 }

 protected function row(string $sql,array $p=[]): ?array {
   $r = $this->query($sql,$p);
   return $r[0] ?? null;
 }

 protected function execSql(string $sql,array $p=[]): bool {
   self::$lastError = null;
   if(!$this->db){ self::$lastError = Database::error() ?: 'Sin conexion a la base de datos.'; return false; }
   try{
     $s = $this->db->prepare($sql);
     foreach($p as $k=>$v){
       $param = is_int($k) ? $k + 1 : ':'.ltrim((string)$k, ':');
       $type = is_int($v) ? PDO::PARAM_INT : (is_bool($v) ? PDO::PARAM_BOOL : (is_null($v) ? PDO::PARAM_NULL : PDO::PARAM_STR));
       $s->bindValue($param, $v, $type);
     }
     return $s->execute();
   }catch(Throwable $e){
     $this->fail($e, $sql);
     return false;
   }
 }


 protected function execAffected(string $sql,array $p=[]): int {
   self::$lastError = null;
   if(!$this->db){ self::$lastError = Database::error() ?: 'Sin conexion a la base de datos.'; return -1; }
   try{
     $s = $this->db->prepare($sql);
     foreach($p as $k=>$v){
       $param = is_int($k) ? $k + 1 : ':'.ltrim((string)$k, ':');
       $type = is_int($v) ? PDO::PARAM_INT : (is_bool($v) ? PDO::PARAM_BOOL : (is_null($v) ? PDO::PARAM_NULL : PDO::PARAM_STR));
       $s->bindValue($param, $v, $type);
     }
     $s->execute();
     return $s->rowCount();
   }catch(Throwable $e){
     $this->fail($e, $sql);
     return -1;
   }
 }

 protected function transaction(callable $callback): bool {
   self::$lastError = null;
   if(!$this->db){ self::$lastError = Database::error() ?: 'Sin conexion a la base de datos.'; return false; }
   try{
     $this->db->beginTransaction();
     $ok = (bool)$callback($this->db);
     if($ok){ $this->db->commit(); return true; }
     $this->db->rollBack();
     return false;
   }catch(Throwable $e){
     if($this->db->inTransaction()) $this->db->rollBack();
     $this->fail($e, 'transaction');
     return false;
   }
 }

 protected function audit(?int $user, string $entity, string $action, array $detail=[]): void {
   if(!$this->db) return;
   try{
     $stmt = $this->db->prepare("INSERT INTO auditoria_sistema(idUsuario,entidad,accion,detalle,ip,user_agent,creado_en) VALUES(:u,:e,:a,:d,:ip,:ua,NOW())");
     $stmt->execute([
       'u'=>$user ?: null,
       'e'=>mb_substr($entity,0,80),
       'a'=>mb_substr($action,0,80),
       'd'=>json_encode($detail, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
       'ip'=>$_SERVER['REMOTE_ADDR'] ?? 'cli',
       'ua'=>mb_substr($_SERVER['HTTP_USER_AGENT'] ?? 'cli',0,255),
     ]);
   }catch(Throwable $e){
     @file_put_contents(ALBERCAS_ROOT.'/logs/audit.log','['.date('c').'] '.$e->getMessage().PHP_EOL,FILE_APPEND);
   }
 }

 protected function fail(Throwable $e, string $sql=''): void {
   self::$lastError = $e->getMessage();
   @file_put_contents(ALBERCAS_ROOT.'/logs/sql.log','['.date('c').'] '.$e->getMessage().PHP_EOL.$sql.PHP_EOL,FILE_APPEND);
 }
}
