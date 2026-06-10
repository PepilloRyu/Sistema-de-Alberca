<?php
declare(strict_types=1);

final class AuthController extends Controller {
 public function login(): void {
   if(!empty($_SESSION['usuario_id'])) $this->go(default_route_for_role($_SESSION['idRol']??null));
   $this->plain('auth/login',['pageTitle'=>'Iniciar sesión','csrf'=>Seguridad::csrfToken(),'error'=>$this->err((string)($_GET['error']??'')),'success'=>isset($_GET['registered'])?'Registro enviado. Un administrador debe asignarte un rol.':'']);
 }

 public function register(): void {
   $this->plain('auth/register',['pageTitle'=>'Registro interno','csrf'=>Seguridad::csrfToken(),'error'=>$this->regerr((string)($_GET['error']??''))]);
 }

 public function doLogin(): void {
   $this->requirePost();
   $email=strtolower(trim((string)($_POST['email']??'')));
   $pass=(string)($_POST['password']??'');
   if($email===''||$pass==='') $this->go('login',['error'=>'invalid']);
   Model::clearLastError();
   $model=new UsuarioModel();
   $u=$model->findByEmail($email);
   if(Database::error() || Model::lastError()) $this->go('login',['error'=>'db']);
   if(!$u) $this->go('login',['error'=>'invalid']);
   $hash=(string)($u['password_hash']??'');
   if(password_get_info($hash)['algo']===0 || !password_verify($pass,$hash)) $this->go('login',['error'=>'invalid']);
   if(($u['estado']??'')==='inactivo') $this->go('login',['error'=>'inactive']);
   session_regenerate_id(true);
   $_SESSION['usuario_id']=(int)$u['idUsuario'];
   $_SESSION['idRol']=($u['estado'] ?? '')==='activo' && isset($u['idRol']) && $u['idRol']!==null ? (int)$u['idRol'] : null;
   $_SESSION['usuario_nombre']=$u['nombre'];
   $_SESSION['usuario_email']=$u['email'];
   $_SESSION['usuario_estado']=$u['estado'];
   $_SESSION['rol_nombre']=$_SESSION['idRol'] ? ($u['rol_nombre'] ?: role_name((int)$_SESSION['idRol'])) : 'Sin Rol';
   Seguridad::activity();
   $model->updateAccess((int)$u['idUsuario']);
   redirect(page_url(default_route_for_role($_SESSION['idRol'])));
 }

 public function doRegister(): void {
   $this->requirePost();
   $n=trim((string)($_POST['nombre']??''));
   $e=strtolower(trim((string)($_POST['email']??'')));
   $p=(string)($_POST['password']??'');
   $c=(string)($_POST['password_confirm']??'');
   if(!$n||!$e||!$p||!$c)$this->go('registro',['error'=>'empty']);
   if(!filter_var($e,FILTER_VALIDATE_EMAIL))$this->go('registro',['error'=>'email']);
   if($p!==$c)$this->go('registro',['error'=>'confirm']);
   if(!Seguridad::securePassword($p))$this->go('registro',['error'=>'password']);
   $m=new UsuarioModel();
   if($m->findByEmail($e))$this->go('registro',['error'=>'duplicate']);
   if(!$m->createPending($n,$e,$p))$this->go('registro',['error'=>'db']);
   $this->go('login',['registered'=>1]);
 }

 public function logout(): void { Seguridad::destroy(); $this->go('login'); }
 public function sinRol(): void { $this->render('sin_rol/index',['pageTitle'=>'Pendiente de aprobación','routeType'=>'sin-rol','activePage'=>'sin-rol']); }

 private function err(string $e): string { return match($e){
   'invalid'=>'Correo o contraseña incorrectos.',
   'inactive'=>'Usuario inactivo.',
   'db'=>'No se pudo leer la base de datos. Enciende MySQL y revisa config/db.php: host, puerto, usuario, contraseña y nombre de base.',
   'sesion_expirada'=>'Tu sesión expiró por 15 minutos de inactividad.',
   default=>''
 }; }
 private function regerr(string $e): string { return match($e){
   'empty'=>'Todos los campos son obligatorios.',
   'email'=>'Correo institucional inválido.',
   'confirm'=>'Las contraseñas no coinciden.',
   'password'=>'Mínimo 8 caracteres, mayúscula, minúscula y número.',
   'duplicate'=>'Este correo ya existe.',
   'db'=>'No se pudo guardar. Importa la base o revisa config/db.php.',
   default=>''
 }; }
}
