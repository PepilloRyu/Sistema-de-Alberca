<?php
declare(strict_types=1);
header('X-Frame-Options: DENY'); header('X-Content-Type-Options: nosniff'); header('Referrer-Policy: strict-origin-when-cross-origin');
require __DIR__.'/app/helpers/functions.php';
date_default_timezone_set(config('timezone','America/Mexico_City'));
$dir=dirname($_SERVER['SCRIPT_NAME']??'/'); $dir=($dir==='/'||$dir==='\\'||$dir==='.')?'/':rtrim(str_replace('\\','/',$dir),'/').'/';
session_set_cookie_params(['lifetime'=>0,'path'=>$dir,'secure'=>(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off'),'httponly'=>true,'samesite'=>'Lax']);
session_status()===PHP_SESSION_NONE && session_start();
require ALBERCAS_ROOT.'/auth/Seguridad.php'; require ALBERCAS_ROOT.'/app/core/Database.php'; require ALBERCAS_ROOT.'/app/core/Model.php'; require ALBERCAS_ROOT.'/app/core/Controller.php';
spl_autoload_register(function($c){ foreach([ALBERCAS_ROOT.'/controllers/',ALBERCAS_ROOT.'/models/'] as $d){$f=$d.$c.'.php'; if(is_file($f)){require $f; return;}}});
$page=trim((string)($_GET['page']??'inicio')) ?: 'inicio';
if($page==='inicio') redirect(page_url(empty($_SESSION['usuario_id'])?'login':default_route_for_role($_SESSION['idRol']??null)));
$routes=[
 'login'=>['AuthController','login',null],'registro'=>['AuthController','register',null],'do-login'=>['AuthController','doLogin',null],'do-register'=>['AuthController','doRegister',null],'logout'=>['AuthController','logout',null],'sin-rol'=>['AuthController','sinRol',['auth']],
 'admin-dashboard'=>['AdminController','dashboard',[1]],'admin-usuarios'=>['AdminController','usuarios',[1]],'admin-albercas'=>['AdminController','albercas',[1]],'admin-catalogos'=>['AdminController','catalogos',[1]],'admin-limpieza'=>['AdminController','limpieza',[1]],'admin-mantenimiento'=>['AdminController','mantenimiento',[1]],'admin-reportes'=>['AdminController','reportes',[1]],'admin-seguridad'=>['AdminController','seguridad',[1]],
 'encargado-dashboard'=>['EncargadoController','dashboard',[2]],'encargado-aforo'=>['EncargadoController','aforo',[2]],'encargado-calidad-agua'=>['EncargadoController','calidad',[2]],'encargado-alertas'=>['EncargadoController','alertas',[2]],'encargado-incidencias'=>['EncargadoController','incidencias',[2]],'encargado-horarios'=>['EncargadoController','horarios',[2]],
 'limpieza-dashboard'=>['LimpiezaController','dashboard',[3]],'limpieza-turnos'=>['LimpiezaController','turnos',[3]],'limpieza-checklist'=>['LimpiezaController','checklist',[3]],'limpieza-incidencias'=>['LimpiezaController','incidencias',[3]],'limpieza-historial'=>['LimpiezaController','historial',[3]],
 'mantenimiento-dashboard'=>['MantenimientoController','dashboard',[4]],'mantenimiento-tickets'=>['MantenimientoController','tickets',[4]],'mantenimiento-agenda'=>['MantenimientoController','agenda',[4]],'mantenimiento-equipos'=>['MantenimientoController','equipos',[4]],'mantenimiento-historial'=>['MantenimientoController','historial',[4]],
];
if(!isset($routes[$page])){ http_response_code(404); exit('Página no encontrada'); }
[$class,$method,$roles]=$routes[$page];
if($roles!==null){
  if(empty($_SESSION['usuario_id'])) redirect(page_url('login'));
  Seguridad::enforceIdle();
  if(($roles!==['auth']) && (($_SESSION['usuario_estado'] ?? 'activo') !== 'activo')) redirect(page_url('sin-rol'));
  if($roles!==['auth'] && !in_array((int)($_SESSION['idRol']??0),$roles,true)) redirect(page_url(default_route_for_role($_SESSION['idRol']??null)));
}
(new $class())->$method();
