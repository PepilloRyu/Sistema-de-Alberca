<?php
$user=current_user();
$routeType=$routeType??role_slug($user['idRol']??null);
$activePage=$activePage??'';
$pageTitle=$pageTitle??config('name');
$sidebar=ALBERCAS_ROOT.'/views/partials/sidebar-'.$routeType.'.php';
$flash=flashes();
$cssFiles=['css/base.css','css/sidebar.css','css/topbar.css'];
$pageCssName=$activePage;
$prefix=$routeType.'-';
if($routeType && str_starts_with($activePage,$prefix)) $pageCssName=substr($activePage,strlen($prefix));
$pageCss='css/'.$routeType.'/'.$pageCssName.'.css';
if($routeType==='sin-rol') $pageCss='css/sin-rol.css';
if(is_file(ALBERCAS_ROOT.'/'.$pageCss)) $cssFiles[]=$pageCss;
$cssFiles[]='css/fixes/no-overlap.css';
?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><base href="<?= e(url('')) ?>"><title><?= e($pageTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?php foreach($cssFiles as $css): ?><link rel="stylesheet" href="<?= asset($css) ?>?v=<?= e(asset_version($css)) ?>">
<?php endforeach; ?><script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>window.ALBERCAS_SESSION_TIMEOUT=<?= (int)system_setting('session_timeout_seconds', config('session_timeout',900)) ?>;window.ALBERCAS_LOGOUT_URL="<?= e(page_url('logout')) ?>";</script>
</head><body class="role-<?= e($routeType) ?> page-<?= e($activePage) ?>"><div class="app-shell"><?php if(is_file($sidebar)) require $sidebar; ?><section class="workspace"><?php require ALBERCAS_ROOT.'/views/partials/topbar.php'; ?><?php if($flash): ?><div class="flash-stack"><?php foreach($flash as $f): ?><div class="alert alert-<?= e($f['type']) ?> py-2 px-3 mb-2"><?= e($f['message']) ?></div><?php endforeach; ?></div><?php endif; ?><main class="content-frame"><?= $content ?></main></section></div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="<?= asset('js/app.js') ?>?v=<?= e(asset_version('js/app.js')) ?>"></script></body></html>
