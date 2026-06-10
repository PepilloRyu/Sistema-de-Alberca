<?php
declare(strict_types=1);
if (!function_exists('mb_strtolower')) { function mb_strtolower(string $s, ?string $enc=null): string { return strtolower($s); } }
if (!function_exists('mb_strtoupper')) { function mb_strtoupper(string $s, ?string $enc=null): string { return strtoupper($s); } }
if (!function_exists('mb_substr')) { function mb_substr(string $s, int $start, ?int $length=null, ?string $enc=null): string { return $length===null ? substr($s,$start) : substr($s,$start,$length); } }
if (!function_exists('mb_strimwidth')) { function mb_strimwidth(string $s, int $start, int $width, string $trim_marker='', ?string $enc=null): string { $slice = substr($s, $start, $width); return strlen($s) > $start + $width ? rtrim($slice).$trim_marker : $slice; } }
define('ALBERCAS_ROOT', dirname(__DIR__, 2));
function config(string $key, mixed $default=null): mixed { static $c=null; $c ??= require ALBERCAS_ROOT.'/config/app.php'; return $c[$key] ?? $default; }
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function url(string $path=''): string {
  $base=config('base_url','');
  if($base===''){ $dir=dirname($_SERVER['SCRIPT_NAME'] ?? '/'); $base=($dir==='/'||$dir==='\\'||$dir==='.')?'/':rtrim(str_replace('\\','/',$dir),'/').'/'; }
  return rtrim($base,'/').'/'.ltrim($path,'/');
}
function asset(string $p): string { return url($p); }
function asset_version(string $p): string { $f=ALBERCAS_ROOT.'/'.ltrim($p,'/'); return is_file($f)?(string)filemtime($f):(string)time(); }
function page_url(string $page, array $params=[]): string { return 'index.php?'.http_build_query(array_merge(['page'=>$page],$params)); }
function redirect(string $to): never { header('Location: '.$to); exit; }
function default_route_for_role(?int $r): string { return match($r){1=>'admin-dashboard',2=>'encargado-dashboard',3=>'limpieza-dashboard',4=>'mantenimiento-dashboard',default=>'sin-rol'}; }
function role_slug(?int $r): string { return match($r){1=>'admin',2=>'encargado',3=>'limpieza',4=>'mantenimiento',default=>'sin-rol'}; }
function role_name(?int $r): string { return match($r){1=>'Administrador',2=>'Encargado de Alberca',3=>'Personal de Limpieza',4=>'Técnico de Mantenimiento',default=>'Sin Rol'}; }
function current_user(): array { return ['id'=>$_SESSION['usuario_id']??null,'idRol'=>$_SESSION['idRol']??null,'nombre'=>$_SESSION['usuario_nombre']??'Empleado','email'=>$_SESSION['usuario_email']??'','rol'=>$_SESSION['rol_nombre']??role_name($_SESSION['idRol']??null)]; }
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="'.e(Seguridad::csrfToken()).'">'; }
function flash(string $type,string $message): void { $_SESSION['flash'][]=['type'=>$type,'message'=>$message]; }
function flashes(): array { $f=$_SESSION['flash']??[]; unset($_SESSION['flash']); return $f; }
function pct(int|float $v,int|float $m): int { return $m>0?max(0,min(100,(int)round($v/$m*100))):0; }
function fdt(?string $d): string { if(!$d)return 'Sin fecha'; $t=strtotime($d); return $t?date('d/m/Y H:i',$t):$d; }
function sev(string $v): string { $s=mb_strtolower($v); return str_contains($s,'alta')||str_contains($s,'crit')||str_contains($s,'cerr')?'danger':(str_contains($s,'media')||str_contains($s,'limpieza')||str_contains($s,'mant')?'warning':(str_contains($s,'baja')||str_contains($s,'disp')||str_contains($s,'concl')?'success':'info')); }

function is_local_request(): bool { $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'; return PHP_SAPI === 'cli' || in_array($ip, ['127.0.0.1','::1','localhost'], true); }
