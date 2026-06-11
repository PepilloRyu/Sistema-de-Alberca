<?php
$openTimes = array_filter(array_map(fn($pool)=>substr((string)($pool['horario_apertura'] ?? ''),0,5), $p ?? []));
$closeTimes = array_filter(array_map(fn($pool)=>substr((string)($pool['horario_cierre'] ?? ''),0,5), $p ?? []));
$inicio = $openTimes ? min($openTimes) : 'Sin dato';
$cierre = $closeTimes ? max($closeTimes) : 'Sin dato';
?>
<div class="module-page two-grid"><section class="glass-card gradient-ocean"><span class="eyebrow-light">RN01</span><h2>Horario operativo general</h2><p>El backend valida el horario configurado por cada alberca registrada en base de datos antes de aceptar aforo.</p><div class="big-time"><?= e($inicio) ?> → <?= e($cierre) ?></div></section><section class="glass-card"><h3>Albercas oficiales</h3><?php foreach($p as $pool): ?><div class="catalog-line"><span><?= e($pool['nombre']) ?></span><b><?= e(substr((string)$pool['horario_apertura'],0,5)) ?> - <?= e(substr((string)$pool['horario_cierre'],0,5)) ?> · <?= e($pool['capacidad_maxima']) ?> pax</b></div><?php endforeach; ?><?php if(empty($p)): ?><p class="text-muted fw-bold">No hay albercas registradas.</p><?php endif; ?></section></div>
