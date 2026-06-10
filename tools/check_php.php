<?php
$root=dirname(__DIR__); $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)); $ok=true;
foreach($it as $file){ if($file->isFile() && strtolower($file->getExtension())==='php'){ exec('php -l '.escapeshellarg($file->getPathname()),$out,$code); if($code!==0){echo implode("\n",$out)."\n"; $ok=false;} } }
echo $ok?"OK\n":"ERROR\n"; exit($ok?0:1);
