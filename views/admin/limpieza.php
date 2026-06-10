<?php
$turnos = $turnos ?? [];
$areas = $areas ?? [];
$tasks = $tasks ?? [];
$metrics = $metrics ?? ['total'=>0,'completas'=>0,'vencidas'=>0];
$checklist = $checklist ?? [];
$pools = $pools ?? [];
$empleados = $empleados ?? [];

$totalChecklist = max(0, (int)($metrics['total'] ?? count($checklist)));
$doneChecklist = max(0, (int)($metrics['completas'] ?? 0));
$lateChecklist = max(0, (int)($metrics['vencidas'] ?? 0));
if ($totalChecklist === 0 && $checklist) {
  $totalChecklist = count($checklist);
  $doneChecklist = count(array_filter($checklist, fn($x) => (int)($x['completado'] ?? 0) === 1));
}
$pendingChecklist = max(0, $totalChecklist - $doneChecklist);
$completionPct = $totalChecklist > 0 ? (int)round(($doneChecklist / $totalChecklist) * 100) : 0;
$today = date('Y-m-d');
$nowTime = date('H:i:s');
$todayTurns = array_values(array_filter($turnos, fn($t) => ($t['fecha'] ?? '') === $today));
$activeTurns = array_values(array_filter($todayTurns, fn($t) => ($t['hora_inicio'] ?? '00:00:00') <= $nowTime && ($t['hora_fin'] ?? '23:59:59') >= $nowTime));
$nextTurns = array_slice($turnos, 0, 5);

$poolStats = [];
foreach ($pools as $pool) {
  $name = (string)($pool['nombre'] ?? 'Alberca');
  $poolStats[$name] = ['total'=>0,'done'=>0,'areas'=>[], 'estado'=>$pool['estado_nombre'] ?? 'Disponible', 'clase'=>$pool['clase_ui'] ?? 'success'];
}
foreach ($checklist as $item) {
  $name = (string)($item['alberca'] ?? 'Alberca');
  if (!isset($poolStats[$name])) $poolStats[$name] = ['total'=>0,'done'=>0,'areas'=>[], 'estado'=>'Disponible', 'clase'=>'success'];
  $poolStats[$name]['total']++;
  $poolStats[$name]['done'] += (int)($item['completado'] ?? 0) === 1 ? 1 : 0;
  $area = (string)($item['area'] ?? 'Área');
  $poolStats[$name]['areas'][$area] = true;
}
foreach ($turnos as $turno) {
  $name = (string)($turno['alberca'] ?? 'Alberca');
  if (!isset($poolStats[$name])) $poolStats[$name] = ['total'=>0,'done'=>0,'areas'=>[], 'estado'=>'Disponible', 'clase'=>'success'];
  if (!empty($turno['area'])) $poolStats[$name]['areas'][(string)$turno['area']] = true;
}

$areaCoverage = [];
foreach ($areas as $a) $areaCoverage[(string)($a['nombre'] ?? 'Área')] = 0;
foreach ($turnos as $turno) {
  $area = (string)($turno['area'] ?? 'Área');
  if (!isset($areaCoverage[$area])) $areaCoverage[$area] = 0;
  $areaCoverage[$area]++;
}
arsort($areaCoverage);
$coverageGlobal = count($areas) > 0 ? min(100, (int)round((count(array_filter($areaCoverage)) / count($areas)) * 100)) : 0;

$kpis = [
  ['label'=>'Turnos hoy','value'=>count($todayTurns),'sub'=>count($activeTurns).' activos ahora','icon'=>'fa-calendar-check','tone'=>'aqua'],
  ['label'=>'Checklist','value'=>$completionPct.'%','sub'=>$doneChecklist.'/'.$totalChecklist.' completadas','icon'=>'fa-clipboard-check','tone'=>'violet'],
  ['label'=>'Pendientes','value'=>$pendingChecklist,'sub'=>$lateChecklist.' vencidas','icon'=>'fa-hourglass-half','tone'=>$lateChecklist>0?'coral':'amber'],
  ['label'=>'Áreas cubiertas','value'=>$coverageGlobal.'%','sub'=>count(array_filter($areaCoverage)).'/'.count($areas).' áreas','icon'=>'fa-map-location-dot','tone'=>'blue'],
  ['label'=>'Equipo','value'=>count($empleados),'sub'=>'personal activo','icon'=>'fa-people-group','tone'=>'mint'],
];

function clean_pool_label(string $name): string {
  return trim(str_ireplace('Alberca ', '', $name));
}
function clean_short_time(?string $time): string {
  return $time ? substr($time, 0, 5) : '--:--';
}
?>

<div class="clean-saas-page">
  <section class="clean-kpi-row">
    <?php foreach ($kpis as $k): ?>
      <article class="clean-kpi clean-kpi-<?= e($k['tone']) ?>">
        <i class="fa-solid <?= e($k['icon']) ?>"></i>
        <div>
          <span><?= e($k['label']) ?></span>
          <b><?= e((string)$k['value']) ?></b>
          <small><?= e($k['sub']) ?></small>
        </div>
      </article>
    <?php endforeach; ?>
  </section>

  <section class="clean-left-grid">
    <article class="glass-card clean-map-card">
      <div class="section-head tight clean-headline">
        <div>
          <h3>Operación de limpieza por alberca</h3>
          <span>Turnos, áreas cubiertas y avance del checklist diario.</span>
        </div>
        <a class="mini-action" href="<?= e(page_url('admin-albercas')) ?>"><i class="fa-solid fa-water"></i> Albercas</a>
      </div>
      <div class="clean-pool-list">
        <?php foreach ($poolStats as $poolName => $st):
          $total = (int)$st['total'];
          $done = (int)$st['done'];
          $pct = $total > 0 ? (int)round(($done / $total) * 100) : 0;
          $areasTxt = $st['areas'] ? implode(', ', array_slice(array_keys($st['areas']), 0, 2)) : 'Sin turno asignado';
          $statusClass = $pct >= 80 ? 'ok' : ($pct >= 40 ? 'warn' : 'risk');
        ?>
          <div class="clean-pool-row <?= e($statusClass) ?>">
            <div class="pool-icon"><i class="fa-solid fa-water-ladder"></i></div>
            <div class="clean-pool-main">
              <div class="clean-pool-title">
                <b><?= e(clean_pool_label($poolName)) ?></b>
                <span><?= e($done.'/'.$total) ?> tareas</span>
              </div>
              <div class="progress clean-progress"><span style="width:<?= e((string)$pct) ?>%"></span></div>
              <small><?= e($areasTxt) ?></small>
            </div>
            <div class="clean-pool-status">
              <strong><?= e((string)$pct) ?>%</strong>
              <em><?= e((string)$st['estado']) ?></em>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </article>

    <article class="glass-card clean-turns-card">
      <div class="section-head tight clean-headline">
        <div>
          <h3>Turnos próximos</h3>
          <span>Asignación operativa de los siguientes 7 días.</span>
        </div>
        <span class="mini-counter"><?= e((string)count($turnos)) ?></span>
      </div>
      <div class="clean-timeline">
        <?php if (!$nextTurns): ?>
          <div class="empty-state compact"><i class="fa-solid fa-calendar-xmark"></i><b>Sin turnos</b><small>Asigna un turno desde el panel derecho.</small></div>
        <?php endif; ?>
        <?php foreach ($nextTurns as $idx => $t): ?>
          <div class="clean-turn-line <?= $idx===0?'current':'' ?>">
            <div class="turn-hour"><?= e(clean_short_time($t['hora_inicio'] ?? null)) ?><small><?= e(clean_short_time($t['hora_fin'] ?? null)) ?></small></div>
            <div class="turn-copy">
              <b><?= e($t['empleado'] ?? 'Personal') ?></b>
              <small><?= e(($t['area'] ?? 'Área').' · '.clean_pool_label((string)($t['alberca'] ?? 'Alberca'))) ?></small>
            </div>
            <span><?= e(date('d/m', strtotime($t['fecha'] ?? $today))) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </article>

    <article class="glass-card clean-check-card">
      <div class="section-head tight clean-headline">
        <div>
          <h3>Checklist crítico del día</h3>
          <span>RF10 · tareas completadas, pendientes y vencidas.</span>
        </div>
        <a class="mini-action" href="<?= e(page_url('admin-reportes')) ?>"><i class="fa-solid fa-chart-line"></i> Reporte</a>
      </div>
      <div class="clean-check-grid">
        <div class="clean-donut-wrap">
          <canvas id="cleanChecklistChart" aria-label="Avance de checklist"></canvas>
          <div class="donut-center"><b><?= e((string)$completionPct) ?>%</b><span>avance</span></div>
        </div>
        <div class="clean-check-list">
          <?php foreach (array_slice($checklist, 0, 4) as $item):
            $completed = (int)($item['completado'] ?? 0) === 1;
            $isLate = !$completed && (($item['hora_limite'] ?? '23:59:59') < $nowTime);
          ?>
            <div class="check-mini <?= $completed?'done':($isLate?'late':'todo') ?>">
              <i class="fa-solid <?= $completed?'fa-check':'fa-clock' ?>"></i>
              <div>
                <b><?= e($item['tarea'] ?? 'Tarea') ?></b>
                <small><?= e(clean_pool_label((string)($item['alberca'] ?? 'Alberca')).' · '.($item['area'] ?? 'Área').' · '.clean_short_time($item['hora_limite'] ?? null)) ?></small>
              </div>
              <span><?= $completed?'OK':($isLate?'Vencida':'Pendiente') ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </article>
  </section>

  <aside class="clean-side-grid">
    <article class="glass-card clean-assign-card">
      <div class="section-head tight clean-headline">
        <div>
          <h3>Asignar turno</h3>
          <span>RF09 · empleado, alberca y área específica.</span>
        </div>
        <i class="side-badge fa-solid fa-broom"></i>
      </div>
      <form method="post" class="clean-assign-form">
        <?= csrf_field() ?>
        <label>Empleado</label>
        <select name="idUsuario" class="form-select" required>
          <?php foreach($empleados as $u): ?><option value="<?= e($u['idUsuario']) ?>"><?= e($u['nombre']) ?></option><?php endforeach; ?>
        </select>
        <div class="two-field">
          <div><label>Alberca</label><select name="idAlberca" class="form-select" required><?php foreach($pools as $p): ?><option value="<?= e($p['idAlberca']) ?>"><?= e($p['nombre']) ?></option><?php endforeach; ?></select></div>
          <div><label>Área</label><select name="idAreaLimpieza" class="form-select" required><?php foreach($areas as $a): ?><option value="<?= e($a['idAreaLimpieza']) ?>"><?= e($a['nombre']) ?></option><?php endforeach; ?></select></div>
        </div>
        <label>Fecha</label>
        <input type="date" name="fecha" class="form-control" value="<?= e(date('Y-m-d')) ?>" required>
        <div class="two-field">
          <div><label>Inicio</label><input type="time" name="hora_inicio" class="form-control" value="07:00" required></div>
          <div><label>Fin</label><input type="time" name="hora_fin" class="form-control" value="13:00" required></div>
        </div>
        <button class="btn btn-aqua w-100"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar turno</button>
      </form>
    </article>

    <article class="glass-card clean-coverage-card">
      <div class="section-head tight clean-headline">
        <div>
          <h3>Cobertura por área</h3>
          <span>Lectura rápida de carga operativa.</span>
        </div>
        <span class="mini-counter"><?= e((string)count(array_filter($areaCoverage))) ?>/<?= e((string)count($areas)) ?></span>
      </div>
      <div class="area-coverage-list">
        <?php foreach (array_slice($areaCoverage, 0, 6, true) as $area => $count):
          $pct = count($turnos) > 0 ? min(100, (int)round(($count / max(1,count($turnos))) * 100)) : 0;
        ?>
          <div class="area-line">
            <div><b><?= e($area) ?></b><small><?= e((string)$count) ?> turno<?= $count==1?'':'s' ?></small></div>
            <div class="progress micro"><span style="width:<?= e((string)$pct) ?>%"></span></div>
          </div>
        <?php endforeach; ?>
      </div>
    </article>
  </aside>
</div>

<script>
(() => {
  const el = document.getElementById('cleanChecklistChart');
  if (!el || typeof Chart === 'undefined') return;
  new Chart(el, {
    type: 'doughnut',
    data: {
      labels: ['Completadas','Pendientes','Vencidas'],
      datasets: [{
        data: [<?= (int)$doneChecklist ?>, <?= (int)max(0,$pendingChecklist-$lateChecklist) ?>, <?= (int)$lateChecklist ?>],
        backgroundColor: ['#00B8A9','#38BDF8','#FF6B6B'],
        borderWidth: 0,
        cutout: '68%'
      }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}, tooltip:{enabled:true}} }
  });
})();
</script>
