<?php
declare(strict_types=1);

final class MantenimientoController extends Controller {
 public function dashboard(): void {
   $uid=(int)$_SESSION['usuario_id'];
   $t=new TicketModel();
   $m=new MantenimientoModel();
   $metrics=$t->metrics();
   $tickets=$t->queue($uid);
   $schedule=$m->schedule($uid,true,20);
   $equipment=$m->equipment();
   $this->render('mantenimiento/dashboard',compact('metrics','tickets','schedule','equipment')+['pageTitle'=>'Mantenimiento | Dashboard','routeType'=>'mantenimiento','activePage'=>'mantenimiento-dashboard']);
 }

 public function tickets(): void {
   $t=new TicketModel();
   $uid=(int)$_SESSION['usuario_id'];
   if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
     $this->requirePost();
     if(($_POST['action']??'')==='assign') $ok=$t->assign((int)($_POST['idTicket'] ?? 0),$uid);
     else $ok=$t->follow((int)($_POST['idTicket'] ?? 0),$uid,trim((string)($_POST['comentario']??'')),(int)($_POST['idEstadoTicket'] ?? 0));
     flash($ok?'success':'danger',$ok?'Ticket actualizado.':'No se pudo actualizar el ticket.');
     $this->go('mantenimiento-tickets');
   }
   $tickets=$t->queue($uid);
   $cats=$t->cats();
   $this->render('mantenimiento/tickets',compact('tickets','cats')+['pageTitle'=>'Mantenimiento | Tickets FIFO','routeType'=>'mantenimiento','activePage'=>'mantenimiento-tickets']);
 }

 public function agenda(): void {
   $schedule=(new MantenimientoModel())->schedule((int)$_SESSION['usuario_id'],true,50);
   $this->render('mantenimiento/agenda',compact('schedule')+['pageTitle'=>'Mantenimiento | Agenda','routeType'=>'mantenimiento','activePage'=>'mantenimiento-agenda']);
 }

 public function equipos(): void {
   $m=new MantenimientoModel();
   $uid=(int)$_SESSION['usuario_id'];
   if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
     $this->requirePost();
     $ok=false;
     if(($_POST['action']??'')==='update_equipo'){
       $ok=$m->updateEquipment(
         (int)($_POST['idEquipo']??0),
         (string)($_POST['estado']??'revision'),
         (string)($_POST['ultima_revision']??date('Y-m-d')),
         (string)($_POST['proxima_revision']??date('Y-m-d',strtotime('+30 days'))),
         (string)($_POST['comentario']??''),
         $uid
       );
     }
     flash($ok?'success':'danger',$ok?'Revision de equipo guardada.':'No se pudo actualizar el equipo.');
     $this->go('mantenimiento-equipos');
   }
   $equipment=$m->equipment();
   $this->render('mantenimiento/equipos',compact('equipment')+['pageTitle'=>'Mantenimiento | Equipos','routeType'=>'mantenimiento','activePage'=>'mantenimiento-equipos']);
 }

 public function historial(): void {
   $t=new TicketModel();
   $m=new MantenimientoModel();
   $tech=(int)($_SESSION['usuario_id']??0);
   $tickets=$t->history($tech);
   $schedule=$m->history($tech);
   $equipment=$m->equipment();
   $this->render('mantenimiento/historial',compact('tickets','schedule','equipment')+['pageTitle'=>'Mantenimiento | Historial','routeType'=>'mantenimiento','activePage'=>'mantenimiento-historial']);
 }
}
