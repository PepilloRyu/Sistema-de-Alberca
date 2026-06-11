<?php
declare(strict_types=1);
final class Seguridad {
 public static function csrfToken(): string { if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
 public static function verifyCsrfOrFail(string $t): void { if(empty($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$t)){http_response_code(419); exit('Token CSRF inválido');} }
 public static function activity(): void { $_SESSION['last_activity']=time(); }
 public static function enforceIdle(): void {
   if(empty($_SESSION['usuario_id']))return; $timeout=(int)system_setting('session_timeout_seconds', config('session_timeout',900)); $last=(int)($_SESSION['last_activity']??time());
   if(time()-$last>$timeout){ self::destroy(); redirect(page_url('login',['error'=>'sesion_expirada'])); } self::activity();
 }
 public static function destroy(): void { $_SESSION=[]; if(ini_get('session.use_cookies')){$p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain']??'',(bool)$p['secure'],(bool)$p['httponly']);} if(session_status()===PHP_SESSION_ACTIVE) session_destroy(); }
 public static function securePassword(string $p): bool { return strlen($p)>=8 && preg_match('/[A-Z]/',$p) && preg_match('/[a-z]/',$p) && preg_match('/[0-9]/',$p); }
}
