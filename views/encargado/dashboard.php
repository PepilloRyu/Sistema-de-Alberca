<?php
$capacidadTotal = array_sum(array_map(fn($x)=>(int)$x['capacidad_maxima'], $p));
$ocupacionTotal = array_sum(array_map(fn($x)=>(int)$x['ocupacion_actual'], $p));
$ocupacionPct = pct($ocupacionTotal, $capacidadTotal);
$entradas = (int)($k['entradas'] ?? 0);
$salidas = (int)($k['salidas'] ?? 0);
$neto = max(0, $entradas - $salidas);
$alertasCount = count($a ?? []);
$ticketsOpen = count($tickets ?? []);
$qualityByPool = [];
foreach (($q ?? []) as $row) {
  $key = (int)($row['idAlberca'] ?? 0);
  if ($key > 0) $qualityByPool[$key] = $row;
}
$qualityOk = 0;
foreach ($p as $pool) {
  $qq = $qualityByPool[(int)$pool['idAlberca']] ?? null;
  $cl = (float)($qq['cloro_ppm'] ?? 0);
  $ph = (float)($qq['ph'] ?? 0);
  if ($cl >= 1.0 && $cl <= 3.0 && $ph >= 7.2 && $ph <= 7.8) $qualityOk++;
}
$available = count(array_filter($p, fn($pool)=>sev((string)$pool['estado_nombre']) === 'success'));
$attention = count($p) - $available;
$pressure = $ocupacionPct >= 85 ? 'Alta' : ($ocupacionPct >= 60 ? 'Media' : 'Controlada');
$nextAction = $alertasCount > 0 ? 'Revisar alertas activas' : ($ocupacionPct >= 70 ? 'Preparar control de aforo' : 'Monitoreo normal del turno');
$flowLabels = $flow['labels'] ?? ['07','08','09','10','11','12','13','14','15','16','17','18','19','20'];
$flowEntries = $flow['entradas'] ?? [18,34,46,72,86,96,74,61,58,43,31,21,12,6];
$flowExits = $flow['salidas'] ?? [0,5,11,20,28,36,48,54,63,66,58,41,29,17];
$maxEntry = max($flowEntries ?: [0]);
$peakIndex = array_search($maxEntry, $flowEntries, true);
$peakHour = $flowLabels[$peakIndex === false ? 0 : $peakIndex] ?? '12';
?>
<div class="encargado-saas-page encargado-dashboard-v25">
  <section class="enc-kpi-strip">
    <article class="enc-kpi-card"><i class="fa-solid fa-users-viewfinder"></i><div><span>Aforo actual</span><b><?= $ocupacionPct ?>%</b><small><?= e($ocupacionTotal) ?>/<?= e($capacidadTotal) ?> personas</small></div></article>
    <article class="enc-kpi-card"><i class="fa-solid fa-right-to-bracket"></i><div><span>Entradas hoy</span><b><?= e($entradas) ?></b><small>Salidas <?= e($salidas) ?> · neto <?= e($neto) ?></small></div></article>
    <article class="enc-kpi-card warning"><i class="fa-solid fa-triangle-exclamation"></i><div><span>Alertas</span><b><?= e($alertasCount) ?></b><small><?= $alertasCount ? 'requieren revisión' : 'sin pendientes' ?></small></div></article>
    <article class="enc-kpi-card violet"><i class="fa-solid fa-flask-vial"></i><div><span>Agua OK</span><b><?= e($qualityOk) ?>/5</b><small>cloro y pH en rango</small></div></article>
    <article class="enc-kpi-card blue"><i class="fa-solid fa-ticket"></i><div><span>Tickets FIFO</span><b><?= e($ticketsOpen) ?></b><small>mantenimiento abierto</small></div></article>
  </section>

  <section class="glass-card enc-live-card">
    <div class="enc-section-head">
      <div><h3>Estado operativo de albercas</h3><span>RF04 · aforo, estado y presión por alberca</span></div>
      <a href="<?= e(page_url('encargado-aforo')) ?>" class="mini-action">Registrar aforo</a>
    </div>
    <div class="enc-pool-list">
      <?php foreach($p as $pool):
        $pc = pct((int)$pool['ocupacion_actual'], (int)$pool['capacidad_maxima']);
        $stateTone = sev((string)$pool['estado_nombre']);
        $free = max(0, (int)$pool['capacidad_maxima'] - (int)$pool['ocupacion_actual']);
      ?>
      <article class="enc-pool-row tone-<?= e($stateTone) ?>">
        <div class="enc-pool-name"><i class="fa-solid fa-water-ladder"></i><div><b><?= e(str_replace('Alberca ', '', $pool['nombre'])) ?></b><small><?= e($pool['estado_nombre']) ?></small></div></div>
        <div class="enc-pool-pressure">
          <div class="pressure-line"><strong><?= e($pool['ocupacion_actual']) ?></strong><span>/ <?= e($pool['capacidad_maxima']) ?> ocupantes</span><em><?= e($free) ?> libres</em></div>
          <div class="enc-progress"><span style="width:<?= $pc ?>%"></span></div>
        </div>
        <strong class="enc-pool-percent"><?= $pc ?>%</strong>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card enc-flow-card">
    <div class="enc-section-head">
      <div><h3>Flujo del turno</h3><span>Entradas, salidas, hora pico y acción recomendada</span></div>
      <span class="mini-badge"><?= e($peakHour) ?>:00 pico</span>
    </div>
    <div class="enc-flow-chart-wrap"><canvas id="encFlowChart"></canvas></div>
    <div class="enc-flow-metrics">
      <div><span>Entradas</span><b><?= e($entradas) ?></b></div>
      <div><span>Salidas</span><b><?= e($salidas) ?></b></div>
      <div><span>Presión</span><b><?= e($pressure) ?></b></div>
    </div>
    <div class="enc-next-action"><i class="fa-solid fa-bolt"></i><div><b>Siguiente acción</b><span><?= e($nextAction) ?></span></div></div>
  </section>

  <section class="glass-card enc-right-card">
    <div class="enc-section-head"><div><h3>Alertas y tickets</h3><span>Prioridad operativa inmediata</span></div><a href="<?= e(page_url('encargado-alertas')) ?>" class="mini-action">Ver</a></div>
    <div class="enc-alert-list">
      <?php foreach(array_slice($a,0,3) as $x): ?>
      <article class="enc-alert-item sev-<?= e(sev($x['nivel'] ?? 'media')) ?>"><i class="fa-solid fa-bell"></i><div><b><?= e($x['titulo']) ?></b><small><?= e($x['alberca']) ?> · <?= e($x['nivel'] ?? 'media') ?></small></div></article>
      <?php endforeach; ?>
      <?php if(empty($a)): ?><article class="enc-empty-mini"><i class="fa-solid fa-check"></i><b>Sin alertas activas</b><small>Operación estable.</small></article><?php endif; ?>
    </div>
    <div class="enc-ticket-table">
      <div class="enc-ticket-head"><span>#</span><span>Ticket FIFO</span><span>Prioridad</span></div>
      <?php foreach(array_slice($tickets,0,3) as $i=>$t): ?>
      <div class="enc-ticket-row"><b><?= $i+1 ?></b><div><strong><?= e($t['folio'] ?? 'TK') ?></strong><small><?= e($t['alberca'] ?? '') ?> · <?= e($t['estado'] ?? '') ?></small></div><em><?= e($t['prioridad'] ?? 'Media') ?></em></div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card enc-water-card">
    <div class="enc-section-head">
      <div><h3>Calidad del agua</h3><span>RF05 · última lectura de las 5 albercas</span></div>
      <a href="<?= e(page_url('encargado-calidad-agua')) ?>" class="mini-action">Registrar</a>
    </div>
    <div class="enc-water-grid">
      <?php foreach($p as $pool):
        $qq = $qualityByPool[(int)$pool['idAlberca']] ?? null;
        $cl = (float)($qq['cloro_ppm'] ?? 0);
        $ph = (float)($qq['ph'] ?? 0);
        $temp = (float)($qq['temperatura_c'] ?? 0);
        $ok = $qq && $cl >= 1.0 && $cl <= 3.0 && $ph >= 7.2 && $ph <= 7.8;
        $label = $qq ? ($ok ? 'En rango' : 'Revisar') : 'Sin registro';
        $tone = $qq ? ($ok ? 'success' : 'warning') : 'warning';
      ?>
      <article class="enc-water-mini tone-<?= e($tone) ?>">
        <div class="enc-water-head"><b><?= e(str_replace('Alberca ', '', $pool['nombre'])) ?></b><span><?= e($label) ?></span></div>
        <div class="enc-water-line"><small>CL</small><strong><?= $qq ? e($cl) : '--' ?></strong><div><span style="width:<?= $qq ? pct($cl,3) : 0 ?>%"></span></div></div>
        <div class="enc-water-line"><small>PH</small><strong><?= $qq ? e($ph) : '--' ?></strong><div><span style="width:<?= $qq ? pct($ph,14) : 0 ?>%"></span></div></div>
        <div class="enc-water-line"><small>°C</small><strong><?= $qq ? e($temp) : '--' ?></strong><div><span style="width:<?= $qq ? pct($temp,36) : 0 ?>%"></span></div></div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card enc-actions-card">
    <div class="enc-section-head"><div><h3>Control rápido</h3><span>Accesos principales del encargado</span></div><span class="mini-badge">07:00 - 21:00</span></div>
    <div class="enc-action-grid">
      <a href="<?= e(page_url('encargado-aforo')) ?>"><i class="fa-solid fa-people-arrows"></i><b>Aforo</b><small>Entradas y salidas</small></a>
      <a href="<?= e(page_url('encargado-calidad-agua')) ?>"><i class="fa-solid fa-flask-vial"></i><b>Agua</b><small>Cloro, pH y temp.</small></a>
      <a href="<?= e(page_url('encargado-incidencias')) ?>"><i class="fa-solid fa-ticket"></i><b>Incidencia</b><small>Enviar a FIFO</small></a>
      <a href="<?= e(page_url('encargado-horarios')) ?>"><i class="fa-solid fa-calendar-days"></i><b>Horarios</b><small>Operación diaria</small></a>
    </div>
    <div class="enc-ops-summary">
      <div><b><?= e($available) ?>/5</b><span>disponibles</span></div>
      <div><b><?= e($attention) ?></b><span>con atención</span></div>
      <div><b>15 min</b><span>sesión RNF02</span></div>
    </div>
  </section>
</div>

<script>
(() => {
  const canvas = document.getElementById('encFlowChart');
  if (!canvas || !window.Chart) return;
  new Chart(canvas, {
    type: 'line',
    data: {
      labels: <?= json_encode(array_values($flowLabels), JSON_UNESCAPED_UNICODE) ?>,
      datasets: [
        {label:'Entradas', data: <?= json_encode(array_values($flowEntries)) ?>, borderColor:'#00B8A9', backgroundColor:'rgba(0,184,169,.13)', fill:true, tension:.42, borderWidth:3, pointRadius:0},
        {label:'Salidas', data: <?= json_encode(array_values($flowExits)) ?>, borderColor:'#FF6B6B', backgroundColor:'rgba(255,107,107,.06)', fill:false, tension:.42, borderWidth:3, pointRadius:0}
      ]
    },
    options: {
      responsive:true,
      maintainAspectRatio:false,
      plugins:{legend:{display:false}, tooltip:{mode:'index', intersect:false}},
      scales:{x:{grid:{display:false}, ticks:{color:'#6B7C93', font:{weight:800}}}, y:{beginAtZero:true, grid:{color:'rgba(11,31,58,.08)'}, ticks:{color:'#6B7C93', font:{weight:800}}}}
    }
  });
})();
</script>
