<?php
declare(strict_types=1);

final class Database {
 private static ?PDO $pdo = null;
 private static bool $done = false;
 private static ?string $error = null;

 public static function connection(): ?PDO {
   if(self::$done) return self::$pdo;
   self::$done = true;
   $c = require ALBERCAS_ROOT.'/config/db.php';
   try{
     $host = (string)($c['host'] ?? '127.0.0.1');
     $port = (string)($c['port'] ?? '3306');
     $db = (string)($c['database'] ?? 'albercas');
     $charset = (string)($c['charset'] ?? 'utf8mb4');
     $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
     self::$pdo = new PDO($dsn,(string)($c['username'] ?? 'root'),(string)($c['password'] ?? ''),[
       PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
       PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
       PDO::ATTR_EMULATE_PREPARES=>false,
     ]);
     self::$pdo->exec("SET time_zone = '-06:00'");
   }catch(Throwable $e){
     self::$error = $e->getMessage();
     @file_put_contents(ALBERCAS_ROOT.'/logs/db.log','['.date('c').'] '.self::$error.PHP_EOL,FILE_APPEND);
     if(!empty($c['strict'])) exit('Error de conexión a la base de datos. Revisa config/db.php y el servicio MySQL.');
   }
   return self::$pdo;
 }

 public static function error(): ?string { return self::$error; }
 public static function ok(): bool { return self::connection() instanceof PDO; }
 public static function info(): array {
   $c = require ALBERCAS_ROOT.'/config/db.php';
   return [
    'ok'=>self::ok(),
    'error'=>self::$error,
    'host'=>(string)($c['host'] ?? ''),
    'port'=>(string)($c['port'] ?? ''),
    'database'=>(string)($c['database'] ?? ''),
   ];
 }
}
