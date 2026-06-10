<?php
declare(strict_types=1);

final class AdminController extends Controller {
 public function dashboard(): void {
   $usuarios = new UsuarioModel();
   $tickets = new TicketModel();
   $albercas = new AlbercaModel();
   $operacion = new OperacionModel();
   $limpieza = new LimpiezaModel();
   $mantenimiento = new MantenimientoModel();

   $u = $usuarios->metrics();
   $roles = $usuarios->roleDistribution();
   $t = $tickets->metrics();
   $ticketStatus = $tickets->statusDistribution();
   $ticketPriority = $tickets->priorityDistribution();
   $ticketQueue = $tickets->queue();
   $p = $albercas->dashboard();
   $a = $operacion->alerts();
   $op = $operacion->kpis();
   $flow = $operacion->hourlyFlow();
   $q = $operacion->quality();
   $lm = $limpieza->metrics();
   $turnos = $limpieza->shifts();
   $checklist = $limpieza->checklist();
   $mm = $mantenimiento->metrics();
   $agenda = $mantenimiento->schedule();
   $equipos = $mantenimiento->equipment();

   $this->render('admin/dashboard', compact('u','roles','t','ticketStatus','ticketPriority','ticketQueue','p','a','op','flow','q','lm','turnos','checklist','mm','agenda','equipos') + [
     'pageTitle'=>'Administrador | Centro de control operativo',
     'routeType'=>'admin',
     'activePage'=>'admin-dashboard'
   ]);
 }

 public function usuarios(): void {
   $m = new UsuarioModel();
   if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
     $this->requirePost();
     $ok = $m->assign((int)($_POST['idUsuario'] ?? 0),(int)($_POST['idRol'] ?? 0),(string)($_POST['estado'] ?? 'pendiente'),(int)$_SESSION['usuario_id']);
     flash($ok?'success':'danger',$ok?'Usuario actualizado.':'No se pudo actualizar el usuario.');
     $this->go('admin-usuarios');
   }
   $users = $m->users();
   $roles = $m->roles();
   $this->render('admin/usuarios',compact('users','roles')+['pageTitle'=>'Administrador | Usuarios y roles','routeType'=>'admin','activePage'=>'admin-usuarios']);
 }

 public function albercas(): void {
   $m = new AlbercaModel();
   if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
     $this->requirePost();
     $ok = $m->status((int)($_POST['idAlberca'] ?? 0),(int)($_POST['idEstadoAlberca'] ?? 0),(int)$_SESSION['usuario_id']);
     flash($ok?'success':'danger',$ok?'Estado actualizado.':'No se pudo actualizar el estado.');
     $this->go('admin-albercas');
   }
   $pools = $m->dashboard();
   $estados = $m->estados();
   $op = (new OperacionModel())->kpis();
   $quality = (new OperacionModel())->quality();
   $alerts = (new OperacionModel())->alerts();
   $tickets = (new TicketModel())->queue();
   $agenda = (new MantenimientoModel())->schedule();
   $this->render('admin/albercas',compact('pools','estados','op','quality','alerts','tickets','agenda')+['pageTitle'=>'Administrador | Albercas','routeType'=>'admin','activePage'=>'admin-albercas']);
 }

 public function catalogos(): void {
   $estados = (new AlbercaModel())->estados();
   $cats = (new TicketModel())->cats();
   $roles = (new UsuarioModel())->roles();
   $mantTipos = (new MantenimientoModel())->types();
   $areas = (new LimpiezaModel())->areas();
   $tareas = (new LimpiezaModel())->tasks();
   $this->render('admin/catalogos',compact('estados','cats','roles','mantTipos','areas','tareas')+['pageTitle'=>'Administrador | Catálogos','routeType'=>'admin','activePage'=>'admin-catalogos']);
 }

 public function limpieza(): void {
   $lm = new LimpiezaModel();
   if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
     $this->requirePost();
     $ok = $lm->assignShift($_POST,(int)$_SESSION['usuario_id']);
     flash($ok?'success':'danger',$ok?'Turno de limpieza asignado y checklist generado.':'No se pudo asignar turno.');
     $this->go('admin-limpieza');
   }
   $turnos = $lm->shifts();
   $areas = $lm->areas();
   $tasks = $lm->tasks();
   $metrics = $lm->metrics();
   $checklist = $lm->checklist();
   $pools = (new AlbercaModel())->dashboard();
   $empleados = (new UsuarioModel())->byRole(3);
   $this->render('admin/limpieza',compact('turnos','areas','tasks','metrics','checklist','pools','empleados')+['pageTitle'=>'Administrador | Limpieza operativa','routeType'=>'admin','activePage'=>'admin-limpieza']);
 }

 public function mantenimiento(): void {
   $mm = new MantenimientoModel();
   if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
     $this->requirePost();
     $ok = $mm->program($_POST,(int)$_SESSION['usuario_id']);
     flash($ok?'success':'danger',$ok?'Mantenimiento programado.':'No se pudo programar mantenimiento.');
     $this->go('admin-mantenimiento');
   }
   $schedule = $mm->schedule();
   $types = $mm->types();
   $pools = (new AlbercaModel())->all();
   $tecnicos = (new UsuarioModel())->byRole(4);
   $metrics = $mm->metrics();
   $equipment = $mm->equipment();
   $tickets = (new TicketModel())->queue();
   $this->render('admin/mantenimiento',compact('schedule','types','pools','tecnicos','metrics','equipment','tickets')+['pageTitle'=>'Administrador | Mantenimiento técnico','routeType'=>'admin','activePage'=>'admin-mantenimiento']);
 }

 public function reportes(): void {
   $albercas = new AlbercaModel();
   $operacion = new OperacionModel();
   $ticketsModel = new TicketModel();
   $limpieza = new LimpiezaModel();
   $mantenimiento = new MantenimientoModel();
   $usuarios = new UsuarioModel();

   $p = $albercas->dashboard();
   $q = $operacion->quality();
   $op = $operacion->kpis();
   $flow = $operacion->hourlyFlow();
   $alerts = $operacion->alerts();
   $tickets = $ticketsModel->queue();
   $ticketStatus = $ticketsModel->statusDistribution();
   $ticketPriority = $ticketsModel->priorityDistribution();
   $limpiezaMetrics = $limpieza->metrics();
   $checklist = $limpieza->checklist();
   $turnos = $limpieza->shifts();
   $mantMetrics = $mantenimiento->metrics();
   $schedule = $mantenimiento->schedule(null,false,80);
   $equipment = $mantenimiento->equipment();
   $userMetrics = $usuarios->metrics();
   $roles = $usuarios->roleDistribution();

   $this->render('admin/reportes',compact('p','q','op','flow','alerts','tickets','ticketStatus','ticketPriority','limpiezaMetrics','checklist','turnos','mantMetrics','schedule','equipment','userMetrics','roles')+['pageTitle'=>'Administrador | Reportes ejecutivos','routeType'=>'admin','activePage'=>'admin-reportes']);
 }

 public function seguridad(): void {
   $usuarios = new UsuarioModel();
   $users = $usuarios->users();
   $roles = $usuarios->roles();
   $metrics = $usuarios->securityMetrics();
   $audits = $usuarios->auditTrail();
   $this->render('admin/seguridad',compact('users','roles','metrics','audits')+['pageTitle'=>'Administrador | Seguridad y sesiones','routeType'=>'admin','activePage'=>'admin-seguridad']);
 }
}
