<?php
$totalPools = count($pools);
$totalCapacity = array_sum(array_map(fn($p)=>(int)($p['capacidad_maxima']??0), $pools));
$totalOccupancy = array_sum(array_map(fn($p)=>(int)($p['ocupacion_actual']??0), $pools));
$globalPct = pct($totalOccupancy,$totalCapacity);
$available = count(array_filter($pools, fn($p)=>mb_strtolower((string)($p['estado_nombre']??''))==='disponible'));
$attention = count(array_filter($pools, fn($p)=>in_array(sev((string)($p['estado_nombre']??'')), ['warning','danger','dark'], true)));
$criticalAlerts = count(array_filter($alerts ?? [], fn($a)=>in_array(sev((string)($a['nivel']??'')), ['danger','warning'], true)));
$qualityById = [];
foreach (($quality ?? []) as $row) {
  if (isset($row['idAlberca'])) $qualityById[(int)$row['idAlberca']] = $row;
}
$qualityOk = 0;
foreach ($pools as $pool) {
  $q = $qualityById[(int)$pool['idAlberca']] ?? null;
  $cl = $q ? (float)$q['cloro_ppm'] : null;
  $ph = $q ? (float)$q['ph'] : null;
  if ($q) $qualityOk++;
}
$ticketCount = count($tickets ?? []);
$agendaCount = count($agenda ?? []);
$poolClass = function(string $estado): string { $s=mb_strtolower($estado); if(str_contains($s,'cerr')) return 'dark'; if(str_contains($s,'mantenimiento')) return 'danger'; if(str_contains($s,'limpieza')) return 'warning'; if(str_contains($s,'uso')) return 'info'; return 'success'; };
$states = ['success'=>'Disponible','info'=>'En uso','warning'=>'Limpieza','danger'=>'Mantenimiento','dark'=>'Cerrada'];
?>
<section class="pool-saas-page">
  <div class="pool-kpi-strip">
    <article class="pool-kpi-item aqua"><i class="fa-solid fa-water-ladder"></i><span>Albercas</span><b><?= e($totalPools) ?></b><small>catálogo oficial</small></article>
    <article class="pool-kpi-item violet"><i class="fa-solid fa-users-viewfinder"></i><span>Ocupación</span><b><?= e($globalPct) ?>%</b><small><?= e($totalOccupancy) ?>/<?= e($totalCapacity) ?> personas</small></article>
    <article class="pool-kpi-item mint"><i class="fa-solid fa-circle-check"></i><span>Disponibles</span><b><?= e($available) ?></b><small>listas para operar</small></article>
    <article class="pool-kpi-item amber"><i class="fa-solid fa-triangle-exclamation"></i><span>Atención</span><b><?= e($attention) ?></b><small>limpieza, mantto o cierre</small></article>
    <article class="pool-kpi-item coral"><i class="fa-solid fa-flask-vial"></i><span>Lecturas agua</span><b><?= e($qualityOk) ?>/<?= e($totalPools) ?></b><small>con registro</small></article>
  </div>

  <div class="pool-main-grid">
    <section class="glass-card pool-map-card">
      <div class="section-head tight">
        <div><h3>Mapa operativo de albercas</h3><span>Estado, capacidad, presión y acción administrativa.</span></div>
        <span class="pill-aqua"><i class="fa-solid fa-clock"></i> 07:00 - 21:00</span>
      </div>
      <div class="pool-admin-list">
        <?php foreach($pools as $pool):
          $id=(int)$pool['idAlberca']; $cap=(int)$pool['capacidad_maxima']; $occ=max(0,(int)$pool['ocupacion_actual']); $pc=pct($occ,$cap); $free=max(0,$cap-$occ);
          $stateClass=$poolClass((string)$pool['estado_nombre']);
          $q=$qualityById[$id] ?? null;
          $cl=$q ? (float)$q['cloro_ppm'] : null; $ph=$q ? (float)$q['ph'] : null; $temp=$q ? (float)$q['temperatura_c'] : null;
          $waterOk=(bool)$q;
        ?>
        <article class="pool-admin-row state-<?= e($stateClass) ?>">
          <div class="pool-admin-name">
            <i class="fa-solid fa-water-ladder"></i>
            <div><b><?= e(str_replace('Alberca ','',$pool['nombre'])) ?></b><small><?= e($pool['estado_nombre']) ?></small></div>
          </div>
          <div class="pool-admin-pressure">
            <div class="pressure-top"><span><?= e($occ) ?> ocupantes</span><strong><?= e($pc) ?>%</strong></div>
            <div class="pool-big-progress"><span style="width:<?= e($pc) ?>%"></span></div>
            <div class="pressure-bottom"><span><?= e($free) ?> libres</span><span><?= e($cap) ?> capacidad</span></div>
          </div>
          <div class="pool-admin-chem <?= $waterOk?'ok':'warn' ?>">
            <span>Agua</span>
            <b><?= $q ? e(number_format($cl,1).' CL · pH '.number_format($ph,1)) : 'Sin registro' ?></b>
            <small><?= $q ? e(number_format($temp,1).'°C') : 'pendiente' ?></small>
          </div>
          <form class="pool-status-form" method="POST" action="<?= e(page_url('admin-albercas')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="idAlberca" value="<?= e($id) ?>">
            <select class="form-select" name="idEstadoAlberca" aria-label="Estado de <?= e($pool['nombre']) ?>">
              <?php foreach($estados as $estado): ?>
                <option value="<?= e($estado['idEstadoAlberca']) ?>" <?= $pool['estado_nombre']===$estado['nombre']?'selected':'' ?>><?= e($estado['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-aqua" type="submit" title="Guardar estado"><i class="fa-solid fa-floppy-disk"></i></button>
          </form>
        </article>
        <?php endforeach; ?>
      </div>
    </section>

    <aside class="pool-control-stack">
      <section class="glass-card pool-health-card">
        <div class="section-head tight"><div><h3>Salud del complejo</h3><span>Lectura global del turno.</span></div></div>
        <div class="pool-health-body">
          <div class="score-ring mini" style="--score:<?= e($globalPct) ?>%"><strong><?= e($globalPct) ?></strong><small>aforo</small></div>
          <div class="health-grid">
            <div><b><?= e($totalOccupancy) ?>/<?= e($totalCapacity) ?></b><span>ocupación total</span></div>
            <div><b><?= e($criticalAlerts) ?></b><span>alertas activas</span></div>
            <div><b><?= e($ticketCount) ?></b><span>tickets FIFO</span></div>
            <div><b><?= e($agendaCount) ?></b><span>agenda mantto.</span></div>
          </div>
        </div>
      </section>

      <section class="glass-card pool-state-card">
        <div class="section-head tight"><div><h3>Estados oficiales</h3><span>RN03 · solo 5 estados permitidos.</span></div></div>
        <div class="state-legend-grid">
          <?php foreach($states as $class=>$label): $count=count(array_filter($pools, fn($p)=>$poolClass((string)$p['estado_nombre'])===$class)); ?>
            <div class="state-legend-item <?= e($class) ?>"><i></i><b><?= e($count) ?></b><span><?= e($label) ?></span></div>
          <?php endforeach; ?>
        </div>
      </section>
    </aside>
  </div>

  <div class="pool-bottom-grid">
    <section class="glass-card water-board-card">
      <div class="section-head tight"><div><h3>Calidad del agua por alberca</h3><span>Cloro, pH y temperatura con lectura rápida.</span></div><a class="btn btn-soft" href="<?= e(page_url('admin-reportes')) ?>">Reporte</a></div>
      <div class="water-admin-grid">
        <?php foreach($pools as $pool):
          $q=$qualityById[(int)$pool['idAlberca']] ?? null;
          $cl=$q ? (float)$q['cloro_ppm'] : 0; $ph=$q ? (float)$q['ph'] : 0; $temp=$q ? (float)$q['temperatura_c'] : 0;
          $ok=(bool)$q;
        ?>
        <article class="water-mini-card <?= $ok?'ok':'warn' ?>">
          <div class="water-mini-head"><b><?= e(str_replace('Alberca ','',$pool['nombre'])) ?></b><span><?= $ok?'Con registro':'Sin registro' ?></span></div>
          <div class="water-mini-metrics">
            <div><small>CL</small><strong><?= $q?e(number_format($cl,1)):'--' ?></strong><i><em style="width:<?= e($q?min(100,max(0,$cl/3*100)):0) ?>%"></em></i></div>
            <div><small>pH</small><strong><?= $q?e(number_format($ph,1)):'--' ?></strong><i><em style="width:<?= e($q?min(100,max(0,($ph-6.8)/1.4*100)):0) ?>%"></em></i></div>
            <div><small>°C</small><strong><?= $q?e(number_format($temp,1)):'--' ?></strong><i><em style="width:<?= e($q?min(100,max(0,($temp-22)/10*100)):0) ?>%"></em></i></div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="glass-card pool-risk-card">
      <div class="section-head tight"><div><h3>Riesgos y próxima acción</h3><span>Alertas, FIFO y mantenimiento preventivo.</span></div></div>
      <div class="risk-mini-list">
        <?php foreach(array_slice($alerts ?? [],0,2) as $al): ?>
          <div class="risk-mini-row sev-<?= e(sev((string)($al['nivel']??''))) ?>"><i class="fa-solid fa-bell"></i><div><b><?= e($al['titulo']??'Alerta') ?></b><small><?= e($al['alberca']??'Alberca') ?> · <?= e($al['nivel']??'media') ?></small></div></div>
        <?php endforeach; ?>
        <?php foreach(array_slice($tickets ?? [],0,1) as $tk): ?>
          <div class="risk-mini-row"><i class="fa-solid fa-screwdriver-wrench"></i><div><b><?= e($tk['folio']??'Ticket pendiente') ?></b><small><?= e($tk['alberca']??'Alberca') ?> · FIFO</small></div></div>
        <?php endforeach; ?>
        <?php foreach(array_slice($agenda ?? [],0,1) as $ag): ?>
          <div class="risk-mini-row"><i class="fa-solid fa-calendar-check"></i><div><b><?= e($ag['tipo']??'Mantenimiento') ?></b><small><?= e($ag['alberca']??'Alberca') ?> · <?= e(!empty($ag['fecha_programada']) ? date('d/m/Y',strtotime((string)$ag['fecha_programada'])).' '.substr((string)($ag['hora_inicio']??''),0,5) : 'programado') ?></small></div></div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</section>
