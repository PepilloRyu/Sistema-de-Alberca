<?php
declare(strict_types=1);

final class EncargadoController extends Controller {
 public function dashboard(): void {
  $op=new OperacionModel();
  $p=(new AlbercaModel())->dashboard();
  $k=$op->kpis();
  $q=$op->quality();
  $a=$op->alerts();
  $flow=$op->hourlyFlow();
  $tickets=(new TicketModel())->queue();
  $this->render('encargado/dashboard',compact('p','k','q','a','flow','tickets')+['pageTitle'=>'Encargado | Centro operativo','routeType'=>'encargado','activePage'=>'encargado-dashboard']);
 }

 public function aforo(): void {
  $op=new OperacionModel();
  if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
   $this->requirePost();
   $r=$op->aforo((int)($_POST['idAlberca'] ?? 0),(string)($_POST['tipo_movimiento'] ?? ''),(int)($_POST['cantidad'] ?? 0),(int)$_SESSION['usuario_id']);
   flash($r['ok']?'success':'danger',$r['msg']);
   $this->go('encargado-aforo');
  }
  $p=(new AlbercaModel())->dashboard();
  $k=$op->kpis();
  $flow=$op->hourlyFlow();
  $recent=$op->recentAforo(8);
  $this->render('encargado/aforo',compact('p','k','flow','recent')+['pageTitle'=>'Encargado | Control de aforo','routeType'=>'encargado','activePage'=>'encargado-aforo']);
 }

 public function calidad(): void {
  $op=new OperacionModel();
  if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
   $this->requirePost();
   $ok=$op->saveQuality($_POST,(int)$_SESSION['usuario_id']);
   flash($ok?'success':'danger',$ok?'Calidad registrada.':'No se pudo guardar.');
   $this->go('encargado-calidad-agua');
  }
  $p=(new AlbercaModel())->all();
  $q=$op->quality();
  $this->render('encargado/calidad',compact('p','q')+['pageTitle'=>'Encargado | Calidad del agua','routeType'=>'encargado','activePage'=>'encargado-calidad-agua']);
 }

 public function alertas(): void {
  $a=(new OperacionModel())->alerts();
  $p=(new AlbercaModel())->dashboard();
  $this->render('encargado/alertas',compact('a','p')+['pageTitle'=>'Encargado | Alertas','routeType'=>'encargado','activePage'=>'encargado-alertas']);
 }

 public function incidencias(): void {
  $t=new TicketModel();
  if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
   $this->requirePost();
   $ok=$t->create($_POST,(int)$_SESSION['usuario_id']);
   flash($ok?'success':'danger',$ok?'Ticket enviado a FIFO.':'No se pudo crear ticket.');
   $this->go('encargado-incidencias');
  }
  $p=(new AlbercaModel())->all();
  $cats=$t->cats();
  $tickets=$t->queue();
  $this->render('encargado/incidencias',compact('p','cats','tickets')+['pageTitle'=>'Encargado | Incidencias','routeType'=>'encargado','activePage'=>'encargado-incidencias']);
 }

 public function horarios(): void {
  $p=(new AlbercaModel())->all();
  $this->render('encargado/horarios',compact('p')+['pageTitle'=>'Encargado | Horarios','routeType'=>'encargado','activePage'=>'encargado-horarios']);
 }
}
