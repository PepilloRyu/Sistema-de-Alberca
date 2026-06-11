<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');
require dirname(__DIR__).'/app/helpers/functions.php';
if(!is_local_request()){ http_response_code(403); exit('Herramienta disponible solo en CLI o localhost.'); }

$root = dirname(__DIR__);
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$ok = true;
foreach($it as $file){
  $path = $file->getPathname();
  if(str_contains($path, DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR)) continue;
  if($file->isFile() && strtolower($file->getExtension()) === 'php'){
    $out = [];
    exec('php -l '.escapeshellarg($path), $out, $code);
    if($code !== 0){
      echo implode("\n", $out)."\n";
      $ok = false;
    }
  }
}
echo $ok ? "OK\n" : "ERROR\n";
exit($ok ? 0 : 1);
