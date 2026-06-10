<?php
declare(strict_types=1);

final class LimpiezaController extends Controller {
 public function dashboard(): void {
  $uid=(int)$_SESSION['usuario_id'];
  $m=new LimpiezaModel();
  $metrics=$m->metrics();
  $check=$m->checklist($uid);
  $turnos=$m->shifts($uid);
  $alerts=(new OperacionModel())->alerts();
  $this->render('limpieza/dashboard',compact('metrics','check','turnos','alerts')+['pageTitle'=>'Limpieza | Dashboard','routeType'=>'limpieza','activePage'=>'limpieza-dashboard']);
 }

 public function turnos(): void {
  $turnos=(new LimpiezaModel())->shifts((int)$_SESSION['usuario_id']);
  $this->render('limpieza/turnos',compact('turnos')+['pageTitle'=>'Limpieza | Turnos','routeType'=>'limpieza','activePage'=>'limpieza-turnos']);
 }

 public function checklist(): void {
  $m=new LimpiezaModel();
  $uid=(int)$_SESSION['usuario_id'];
  if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
   $this->requirePost();
   $ok=$m->complete((int)($_POST['idChecklist'] ?? 0),trim((string)($_POST['observaciones']??'Completado')),$uid);
   flash($ok?'success':'danger',$ok?'Tarea completada.':'No se pudo completar.');
   $this->go('limpieza-checklist');
  }
  $check=$m->checklist($uid);
  $this->render('limpieza/checklist',compact('check')+['pageTitle'=>'Limpieza | Checklist diario','routeType'=>'limpieza','activePage'=>'limpieza-checklist']);
 }

 public function incidencias(): void {
  $t=new TicketModel();
  $uid=(int)$_SESSION['usuario_id'];
  if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
   $this->requirePost();
   $ok=$t->create($_POST,$uid);
   flash($ok?'success':'danger',$ok?'Incidencia reportada y enviada a la cola FIFO.':'No se pudo reportar la incidencia.');
   $this->go('limpieza-incidencias');
  }
  $p=(new AlbercaModel())->all();
  $cats=$t->cats();
  $tickets=$t->recentByReporter($uid);
  $queue=$t->queue();
  $check=(new LimpiezaModel())->checklist($uid);
  $this->render('limpieza/incidencias',compact('p','cats','tickets','queue','check')+['pageTitle'=>'Limpieza | Incidencias','routeType'=>'limpieza','activePage'=>'limpieza-incidencias']);
 }

 public function historial(): void {
  $uid=(int)$_SESSION['usuario_id'];
  $m=new LimpiezaModel();
  $history=$m->history($uid,30);
  $metrics=$m->historyMetrics($uid,30);
  $byPool=$m->historyByPool($uid,30);
  $byArea=$m->historyByArea($uid,30);
  $turnos=$m->shifts($uid);
  $tickets=(new TicketModel())->recentByReporter($uid);
  $this->render('limpieza/historial',compact('history','metrics','byPool','byArea','turnos','tickets')+['pageTitle'=>'Limpieza | Historial','routeType'=>'limpieza','activePage'=>'limpieza-historial']);
 }
}
