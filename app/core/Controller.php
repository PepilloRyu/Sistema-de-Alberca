<?php
declare(strict_types=1);
abstract class Controller {
 protected function render(string $view,array $data=[]): void {
   $path=ALBERCAS_ROOT.'/views/'.$view.'.php'; if(!is_file($path)){http_response_code(500);exit('Vista no encontrada: '.e($view));}
   extract($data,EXTR_SKIP); ob_start(); require $path; $content=ob_get_clean(); require ALBERCAS_ROOT.'/views/layouts/app.php';
 }
 protected function plain(string $view,array $data=[]): void { extract($data,EXTR_SKIP); require ALBERCAS_ROOT.'/views/'.$view.'.php'; }
 protected function requirePost(): void { if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST') exit('Método no permitido'); Seguridad::verifyCsrfOrFail($_POST['csrf_token']??''); }
 protected function go(string $page,array $params=[]): never { redirect(page_url($page,$params)); }
}
