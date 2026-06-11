<?php
$totalCapacity = 0;
$currentOccupancy = 0;
$nonAvailable = 0;
$poolLabels = [];
$poolCapacity = [];
$poolOccupancy = [];
$poolPercent = [];
$mostLoadedPool = null;
$mostLoadedPct = -1;

foreach ($p as $pool) {
  $cap = (int)($pool['capacidad_maxima'] ?? 0);
  $occ = max(0, (int)($pool['ocupacion_actual'] ?? 0));
  $pc = pct($occ, $cap);
  $totalCapacity += $cap;
  $currentOccupancy += $occ;
  if (($pool['estado_nombre'] ?? '') !== 'Disponible') $nonAvailable++;
  if ($pc > $mostLoadedPct) { $mostLoadedPct = $pc; $mostLoadedPool = $pool; }
  $poolLabels[] = (string)($pool['nombre'] ?? 'Alberca');
  $poolCapacity[] = $cap;
  $poolOccupancy[] = $occ;
  $poolPercent[] = $pc;
}

$occupancyPct = pct($currentOccupancy, $totalCapacity);
$availableCapacity = max(0, $totalCapacity - $currentOccupancy);
$usersPending = (int)($u['pendientes'] ?? 0);
$ticketsOpen = (int)($t['abiertos'] ?? 0);
$ticketsCritical = (int)($t['criticos'] ?? 0);
$cleanTotal = max(0, (int)($lm['total'] ?? 0));
$cleanDone = max(0, (int)($lm['completas'] ?? 0));
$cleanPending = max(0, $cleanTotal - $cleanDone);
$cleanPct = $cleanTotal > 0 ? pct($cleanDone, $cleanTotal) : 100;
$maintenanceToday = (int)($mm['hoy'] ?? 0);
$operationalScore = max(0, min(100, 100 - ($ticketsCritical * 12) - ($usersPending * 5) - ($cleanPending * 4) - ($nonAvailable * 4)));

$qualityById = [];
$qualityByName = [];
foreach ($q as $row) {
  if (isset($row['idAlberca'])) $qualityById[(int)$row['idAlberca']] = $row;
  if (isset($row['alberca'])) $qualityByName[mb_strtolower(trim((string)$row['alberca']))] = $row;
}

$waterCards = [];
$waterOk = 0;
foreach ($p as $pool) {
  $id = (int)($pool['idAlberca'] ?? 0);
  $name = (string)($pool['nombre'] ?? 'Alberca');
  $row = $qualityById[$id] ?? $qualityByName[mb_strtolower(trim($name))] ?? null;
  $cl = $row !== null ? (float)($row['cloro_ppm'] ?? 0) : null;
  $ph = $row !== null ? (float)($row['ph'] ?? 0) : null;
  $temp = $row !== null ? (float)($row['temperatura_c'] ?? 0) : null;

  if ($row === null && !empty($pool['ultimo_quimico']) && stripos((string)$pool['ultimo_quimico'], 'pendiente') === false) {
    if (preg_match_all('/([0-9]+(?:\.[0-9]+)?)/', (string)$pool['ultimo_quimico'], $m) && count($m[1]) >= 3) {
      $cl = (float)$m[1][0];
      $ph = (float)$m[1][1];
      $temp = (float)$m[1][2];
    }
  }

  $hasQuality = $cl !== null && $ph !== null && $temp !== null;
  $ok = $hasQuality;
  if ($ok) $waterOk++;
  $waterCards[] = [
    'nombre' => $name,
    'cloro' => $cl,
    'ph' => $ph,
    'temp' => $temp,
    'ok' => $ok,
    'hasQuality' => $hasQuality,
    'estado' => $ok ? 'Con registro' : 'Sin registro'
  ];
}

$flowLabels = $flow['labels'] ?? [];
$flowEntries = array_map('intval', $flow['entradas'] ?? []);
$flowExits = array_map('intval', $flow['salidas'] ?? []);
$peakIndex = $flowEntries ? array_keys($flowEntries, max($flowEntries))[0] : 0;
$peakHour = $flowLabels[$peakIndex] ?? '—';
$peakCount = $flowEntries[$peakIndex] ?? 0;
$netFlow = (int)($op['entradas'] ?? 0) - (int)($op['salidas'] ?? 0);
$pressureLabel = $occupancyPct >= 80 ? 'Alta' : ($occupancyPct >= 60 ? 'Media' : 'Controlada');
$nextAction = 'Monitoreo normal';
if ($ticketsCritical > 0) $nextAction = 'Atender ticket crítico';
elseif ($cleanPending > 0) $nextAction = 'Revisar checklist pendiente';
elseif ($usersPending > 0) $nextAction = 'Aprobar usuarios';
elseif ($nonAvailable > 0) $nextAction = 'Validar albercas no disponibles';

$chartData = [
  'flow' => $flow,
  'priorities' => ['labels'=>array_map(fn($x)=>(string)($x['prioridad'] ?? 'Prioridad'), $ticketPriority),'values'=>array_map(fn($x)=>(int)($x['total'] ?? 0), $ticketPriority)],
  'roles' => ['labels'=>array_map(fn($x)=>(string)($x['rol'] ?? 'Rol'), $roles),'values'=>array_map(fn($x)=>(int)($x['total'] ?? 0), $roles)]
];
?>
<div class="admin-command-grid admin-command-grid-v8">
  <section class="glass-card command-score-card">
    <div class="score-ring" style="--score:<?= e($operationalScore) ?>%"><strong><?= e($operationalScore) ?></strong><small>salud operativa</small></div>
    <div class="score-stack">
      <div><b><?= e($currentOccupancy) ?>/<?= e($totalCapacity) ?></b><span>ocupación total</span></div>
      <div><b><?= e($nonAvailable) ?></b><span>albercas con atención</span></div>
      <div><b><?= e($waterOk) ?>/<?= e(count($p)) ?></b><span>lecturas de agua</span></div>
    </div>
  </section>

  <section class="command-kpis">
    <a class="mini-kpi" href="<?= e(page_url('admin-albercas')) ?>"><i class="fa-solid fa-people-arrows"></i><span>Aforo actual</span><b><?= e($occupancyPct) ?>%</b><small><?= e($currentOccupancy) ?> personas</small></a>
    <a class="mini-kpi coral" href="<?= e(page_url('admin-mantenimiento')) ?>"><i class="fa-solid fa-screwdriver-wrench"></i><span>Tickets críticos</span><b><?= e($ticketsCritical) ?></b><small><?= e($ticketsOpen) ?> abiertos</small></a>
    <a class="mini-kpi amber" href="<?= e(page_url('admin-limpieza')) ?>"><i class="fa-solid fa-broom"></i><span>Limpieza diaria</span><b><?= e($cleanPct) ?>%</b><small><?= e($cleanPending) ?> pendientes</small></a>
    <a class="mini-kpi violet" href="<?= e(page_url('admin-usuarios')) ?>"><i class="fa-solid fa-user-check"></i><span>Usuarios pendientes</span><b><?= e($usersPending) ?></b><small><?= e($u['activos'] ?? 0) ?> activos</small></a>
    <a class="mini-kpi sky" href="<?= e(page_url('admin-mantenimiento')) ?>"><i class="fa-solid fa-calendar-day"></i><span>Mantto. hoy</span><b><?= e($maintenanceToday) ?></b><small><?= e($mm['activos'] ?? 0) ?> activos</small></a>
  </section>

  <section class="glass-card admin-occupancy-card compact-card">
    <div class="section-head tight"><div><h3>Estado vivo de albercas</h3><span>Ocupación, capacidad, estado y presión por alberca</span></div><a href="<?= e(page_url('admin-albercas')) ?>">Gestionar</a></div>
    <div class="pool-live-list">
      <?php foreach($p as $pool):
        $cap=(int)($pool['capacidad_maxima'] ?? 0);
        $occ=max(0,(int)($pool['ocupacion_actual'] ?? 0));
        $pc=pct($occ,$cap);
        $level=$pc>=85?'danger':($pc>=70?'warning':'ok');
        $estado=(string)($pool['estado_nombre'] ?? 'Disponible');
      ?>
      <div class="pool-live-row level-<?= e($level) ?>">
        <div class="pool-live-main">
          <div class="pool-live-title"><b><?= e(str_replace('Alberca ', '', $pool['nombre'])) ?></b><span class="pool-state state-<?= e($pool['clase_ui'] ?? 'success') ?>"><?= e($estado) ?></span></div>
          <div class="pool-capacity-line"><span><?= e($occ) ?> ocupantes</span><span><?= e($cap) ?> capacidad</span></div>
          <div class="pool-big-progress"><span style="width:<?= e($pc) ?>%"></span></div>
        </div>
        <div class="pool-live-score"><strong><?= e($pc) ?>%</strong><small><?= e($availableCapacity) ?> libres global</small></div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card admin-flow-card compact-card">
    <div class="section-head tight"><div><h3>Flujo de operación</h3><span>Entradas, salidas, hora pico y próxima acción</span></div><b><?= e($op['entradas'] ?? 0) ?> / <?= e($op['salidas'] ?? 0) ?></b></div>
    <div class="chart-box flow"><canvas id="hourlyFlowChart"></canvas></div>
    <div class="flow-metrics">
      <div><span>Entradas</span><b><?= e($op['entradas'] ?? 0) ?></b></div>
      <div><span>Salidas</span><b><?= e($op['salidas'] ?? 0) ?></b></div>
      <div><span>Disponibles</span><b><?= e($availableCapacity) ?></b></div>
    </div>
    <div class="flow-intel-grid">
      <div class="flow-intel-card"><i class="fa-solid fa-arrow-trend-up"></i><span>Hora pico</span><b><?= e($peakHour) ?>:00</b><small><?= e($peakCount) ?> entradas</small></div>
      <div class="flow-intel-card"><i class="fa-solid fa-scale-balanced"></i><span>Neto actual</span><b><?= e($netFlow >= 0 ? '+' : '') ?><?= e($netFlow) ?></b><small>personas en sitio</small></div>
      <div class="flow-intel-card"><i class="fa-solid fa-gauge-high"></i><span>Presión</span><b><?= e($pressureLabel) ?></b><small><?= e($occupancyPct) ?>% de uso</small></div>
      <div class="flow-intel-card action"><i class="fa-solid fa-bolt"></i><span>Siguiente</span><b><?= e($nextAction) ?></b><small>prioridad operativa</small></div>
    </div>
  </section>

  <section class="glass-card admin-risk-card compact-card">
    <div class="section-head tight"><div><h3>Riesgos y atención FIFO</h3><span>Alertas, tickets y mantenimiento</span></div><a href="<?= e(page_url('admin-reportes')) ?>">Ver todo</a></div>
    <div class="risk-split">
      <div class="risk-block">
        <h4>Alertas activas</h4>
        <?php foreach(array_slice($a,0,2) as $al): ?>
        <div class="risk-line sev-<?= e(sev($al['nivel'])) ?>"><i class="fa-solid fa-bell"></i><div><b><?= e($al['titulo']) ?></b><small><?= e($al['alberca']) ?> · <?= e($al['nivel']) ?></small></div></div>
        <?php endforeach; ?>
      </div>
      <div class="risk-block">
        <h4>Cola técnica</h4>
        <?php foreach(array_slice($ticketQueue,0,2) as $tk): ?>
        <div class="fifo-line"><span><?= e($tk['folio'] ?? 'TK') ?></span><b><?= e($tk['alberca'] ?? '') ?></b><small><?= e($tk['prioridad'] ?? '') ?> · <?= e($tk['estado'] ?? '') ?></small></div>
        <?php endforeach; ?>
      </div>
      <div class="risk-block last">
        <h4>Agenda próxima</h4>
        <?php foreach(array_slice($agenda,0,2) as $ag): ?>
        <div class="agenda-mini"><b><?= e($ag['tipo'] ?? 'Mantenimiento') ?></b><small><?= e($ag['alberca'] ?? '') ?> · <?= e(substr((string)($ag['hora_inicio'] ?? ''),0,5)) ?> · <?= e($ag['tecnico'] ?? '') ?></small></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="glass-card admin-water-card compact-card">
    <div class="section-head tight"><div><h3>Calidad del agua</h3><span>Las 5 albercas con cloro, pH y temperatura</span></div><a href="<?= e(page_url('admin-reportes')) ?>">Reporte</a></div>
    <div class="water-pool-grid">
      <?php foreach($waterCards as $row):
        $clPct = $row['hasQuality'] ? max(0,min(100,pct((float)$row['cloro'],3.0))) : 0;
        $phPct = $row['hasQuality'] ? max(0,min(100,pct(((float)$row['ph'])-6.8,1.4))) : 0;
        $tmpPct = $row['hasQuality'] ? max(0,min(100,pct(((float)$row['temp'])-22,10))) : 0;
      ?>
      <div class="water-pool-card <?= $row['ok']?'ok':'warn' ?>">
        <div class="water-title"><b><?= e(str_replace('Alberca ', '', $row['nombre'])) ?></b><span><?= e($row['estado']) ?></span></div>
        <div class="water-metric-row"><small>Cl</small><strong><?= $row['hasQuality'] ? e(number_format((float)$row['cloro'],1)) : '—' ?></strong><div class="water-meter"><span style="width:<?= e($clPct) ?>%"></span></div></div>
        <div class="water-metric-row"><small>pH</small><strong><?= $row['hasQuality'] ? e(number_format((float)$row['ph'],1)) : '—' ?></strong><div class="water-meter"><span style="width:<?= e($phPct) ?>%"></span></div></div>
        <div class="water-metric-row"><small>°C</small><strong><?= $row['hasQuality'] ? e(number_format((float)$row['temp'],1)) : '—' ?></strong><div class="water-meter"><span style="width:<?= e($tmpPct) ?>%"></span></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card admin-people-card compact-card">
    <div class="section-head tight"><div><h3>Personal y limpieza</h3><span>Cobertura del día y checklist</span></div><a href="<?= e(page_url('admin-limpieza')) ?>">Asignar</a></div>
    <div class="people-grid">
      <div class="donut-mini"><canvas id="rolesChart"></canvas></div>
      <div class="coverage-list">
        <div class="coverage-pill"><span>Checklist</span><b><?= e($cleanDone) ?>/<?= e($cleanTotal) ?></b></div>
        <?php foreach(array_slice($turnos,0,2) as $tu): ?>
        <div class="coverage-line"><b><?= e($tu['empleado'] ?? 'Personal') ?></b><small><?= e($tu['alberca'] ?? '') ?> · <?= e(substr((string)($tu['hora_inicio'] ?? ''),0,5)) ?>-<?= e(substr((string)($tu['hora_fin'] ?? ''),0,5)) ?></small></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="glass-card admin-ticket-card compact-card">
    <div class="section-head tight"><div><h3>Tickets por prioridad</h3><span>Abiertos, ordenados por urgencia</span></div><a href="<?= e(page_url('admin-mantenimiento')) ?>">Programar</a></div>
    <div class="ticket-priority-grid">
      <div class="donut-mini"><canvas id="ticketPriorityChart"></canvas></div>
      <div class="equipment-mini">
        <?php foreach(array_slice($equipos,0,3) as $eq): ?>
        <div><b><?= e($eq['nombre']) ?></b><small><?= e($eq['alberca']) ?> · <?= e($eq['estado']) ?></small></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</div>

<script>
(() => {
  const data = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  if (!window.Chart) return;
  Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
  Chart.defaults.color = '#6B7890';
  const ocean = '#0B1F3A', lagoon = '#00B8A9', sky = '#2FB7F6', coral = '#FF6B6B', amber = '#FFD166', violet = '#8B5CF6';
  const grid = 'rgba(11,31,58,.08)';

  const flow = document.getElementById('hourlyFlowChart');
  if (flow) new Chart(flow, {
    type: 'line',
    data: { labels: data.flow.labels, datasets: [
      { label: 'Entradas', data: data.flow.entradas, tension:.38, fill:true, borderColor: lagoon, backgroundColor:'rgba(0,184,169,.12)', pointRadius:0, borderWidth:3 },
      { label: 'Salidas', data: data.flow.salidas, tension:.38, fill:true, borderColor: coral, backgroundColor:'rgba(255,107,107,.10)', pointRadius:0, borderWidth:3 }
    ]},
    options: { maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{x:{grid:{display:false}, ticks:{font:{size:10}}}, y:{grid:{color:grid}, ticks:{font:{size:10}}, beginAtZero:true}} }
  });

  const ticket = document.getElementById('ticketPriorityChart');
  if (ticket) new Chart(ticket, {
    type: 'doughnut',
    data: { labels: data.priorities.labels, datasets: [{ data: data.priorities.values, backgroundColor:[coral, amber, sky, lagoon], borderWidth:0 }]},
    options: { maintainAspectRatio:false, cutout:'70%', plugins:{legend:{display:false}} }
  });

  const roles = document.getElementById('rolesChart');
  if (roles) new Chart(roles, {
    type: 'doughnut',
    data: { labels: data.roles.labels, datasets: [{ data: data.roles.values, backgroundColor:[ocean, lagoon, sky, violet, amber], borderWidth:0 }]},
    options: { maintainAspectRatio:false, cutout:'68%', plugins:{legend:{display:false}} }
  });
})();
</script>
