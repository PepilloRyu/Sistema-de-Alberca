<?php
$check = is_array($check ?? null) ? $check : [];
$now = new DateTimeImmutable('now');
$today = $now->format('Y-m-d');
$total = count($check);
$completed = 0;
$overdue = 0;
$pending = 0;
$nextTask = null;
$poolNames = ['Alberca principal','Alberca familiar','Alberca infantil','Alberca vista al mar','Alberca deportiva'];
$poolStats = [];
foreach($poolNames as $p){ $poolStats[$p] = ['total'=>0,'done'=>0,'pending'=>0,'overdue'=>0]; }
$areaStats = [];
$pendingRows = [];
foreach($check as $row){
    $done = (int)($row['completado'] ?? 0) === 1;
    $limitRaw = (string)($row['hora_limite'] ?? '21:00:00');
    $limit = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $today.' '.$limitRaw) ?: $now;
    $isOverdue = !$done && $limit < $now;
    $pool = (string)($row['alberca'] ?? 'Sin alberca');
    $area = (string)($row['area'] ?? 'Área general');
    $poolStats[$pool] = $poolStats[$pool] ?? ['total'=>0,'done'=>0,'pending'=>0,'overdue'=>0];
    $poolStats[$pool]['total']++;
    $poolStats[$pool]['done'] += $done ? 1 : 0;
    $poolStats[$pool]['pending'] += $done ? 0 : 1;
    $poolStats[$pool]['overdue'] += $isOverdue ? 1 : 0;
    $areaStats[$area] = $areaStats[$area] ?? ['total'=>0,'done'=>0];
    $areaStats[$area]['total']++;
    $areaStats[$area]['done'] += $done ? 1 : 0;
    if($done){ $completed++; } else { $pending++; $pendingRows[] = $row + ['_overdue'=>$isOverdue,'_limit_ts'=>$limit->getTimestamp()]; }
    if($isOverdue){ $overdue++; }
}
usort($pendingRows, fn($a,$b)=>(int)$a['_limit_ts'] <=> (int)$b['_limit_ts']);
$nextTask = $pendingRows[0] ?? null;
$progress = $total > 0 ? (int)round(($completed / $total) * 100) : 0;
$remaining = max(0, $total - $completed);
$qualityLabel = $overdue > 0 ? 'Atención inmediata' : ($pending > 0 ? 'Turno en progreso' : 'Checklist cerrado');
$qualityClass = $overdue > 0 ? 'danger' : ($pending > 0 ? 'warning' : 'success');
$areaLabels = array_keys($areaStats);
$areaValues = array_map(fn($x)=>$x['total'] ? (int)round(($x['done']/$x['total'])*100) : 0, array_values($areaStats));
?>
<div class="clean-check-saas-v33">
  <section class="clean-check-kpis">
    <div class="clean-check-kpi">
      <i class="fa-solid fa-clipboard-check"></i>
      <div><span>Avance del día</span><b><?= e((string)$progress) ?>%</b><small><?= e((string)$completed) ?>/<?= e((string)$total) ?> tareas completas</small></div>
    </div>
    <div class="clean-check-kpi success">
      <i class="fa-solid fa-circle-check"></i>
      <div><span>Completadas</span><b><?= e((string)$completed) ?></b><small>Registro del turno</small></div>
    </div>
    <div class="clean-check-kpi warning">
      <i class="fa-solid fa-hourglass-half"></i>
      <div><span>Pendientes</span><b><?= e((string)$pending) ?></b><small>Por cerrar hoy</small></div>
    </div>
    <div class="clean-check-kpi coral">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <div><span>Vencidas</span><b><?= e((string)$overdue) ?></b><small><?= $overdue ? 'Requiere atención' : 'Sin vencimientos' ?></small></div>
    </div>
    <div class="clean-check-kpi violet">
      <i class="fa-solid fa-clock"></i>
      <div><span>Siguiente límite</span><b><?= $nextTask ? e(substr((string)$nextTask['hora_limite'],0,5)) : '—' ?></b><small><?= $nextTask ? e((string)$nextTask['alberca']) : 'Sin pendientes' ?></small></div>
    </div>
  </section>

  <section class="glass-card clean-check-board">
    <div class="clean-check-head">
      <div>
        <h3>Checklist operativo del turno</h3>
        <span>Tareas higiénicas por alberca, área, límite y estado actual.</span>
      </div>
      <div class="clean-check-tools">
        <button type="button" class="active" data-clean-filter="all">Todas</button>
        <button type="button" data-clean-filter="pending">Pendientes</button>
        <button type="button" data-clean-filter="done">Completadas</button>
      </div>
    </div>
    <div class="clean-check-table-wrap">
      <table class="clean-check-table" id="cleanChecklistTable">
        <thead><tr><th>Tarea</th><th>Ubicación</th><th>Límite</th><th>Estado</th><th>Acción rápida</th></tr></thead>
        <tbody>
          <?php foreach($check as $x):
            $done = (int)($x['completado'] ?? 0) === 1;
            $limit = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $today.' '.(string)($x['hora_limite'] ?? '21:00:00')) ?: $now;
            $isOverdue = !$done && $limit < $now;
            $statusClass = $done ? 'success' : ($isOverdue ? 'danger' : 'warning');
            $statusText = $done ? 'Completada' : ($isOverdue ? 'Vencida' : 'Pendiente');
          ?>
          <tr data-status="<?= $done ? 'done' : 'pending' ?>" class="<?= $isOverdue ? 'row-risk' : '' ?>">
            <td>
              <div class="clean-task-title">
                <i class="fa-solid <?= $done ? 'fa-circle-check' : ($isOverdue ? 'fa-circle-exclamation' : 'fa-circle') ?>"></i>
                <div><b><?= e((string)$x['tarea']) ?></b><small><?= e((string)($x['observaciones'] ?? 'Sin observaciones')) ?></small></div>
              </div>
            </td>
            <td><b><?= e((string)$x['alberca']) ?></b><small><?= e((string)$x['area']) ?> · <?= e((string)($x['responsable'] ?? 'Equipo general')) ?></small></td>
            <td><span class="clean-time <?= $isOverdue ? 'danger' : '' ?>"><?= e(substr((string)$x['hora_limite'],0,5)) ?></span></td>
            <td><span class="badge-soft <?= e($statusClass) ?>"><?= e($statusText) ?></span></td>
            <td>
              <?php if(!$done): ?>
              <form class="clean-complete-form" method="POST" action="<?= e(page_url('limpieza-checklist')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="idChecklist" value="<?= e((string)$x['idChecklist']) ?>">
                <input class="form-control form-control-sm" name="observaciones" placeholder="Nota de cierre" value="Completado">
                <button class="btn btn-aqua btn-sm" title="Completar tarea"><i class="fa-solid fa-check"></i></button>
              </form>
              <?php else: ?>
              <span class="clean-locked"><i class="fa-solid fa-lock"></i> Cerrada</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(!$check): ?><tr><td colspan="5"><div class="empty-state">No hay tareas asignadas para hoy.</div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <aside class="glass-card clean-check-progress-card">
    <div class="clean-check-head compact">
      <div><h3>Progreso del turno</h3><span>Lectura rápida del checklist</span></div>
      <em class="clean-status-pill <?= e($qualityClass) ?>"><?= e($qualityLabel) ?></em>
    </div>
    <div class="clean-check-progress-layout">
      <div class="clean-ring" style="--p:<?= e((string)$progress) ?>"><b><?= e((string)$progress) ?>%</b><span>avance</span></div>
      <div class="clean-progress-facts">
        <div><b><?= e((string)$remaining) ?></b><span>restantes</span></div>
        <div><b><?= e((string)$overdue) ?></b><span>vencidas</span></div>
        <div><b><?= e((string)count($areaStats)) ?></b><span>áreas</span></div>
      </div>
    </div>
    <div class="clean-area-chart"><canvas id="cleanAreaChart"></canvas></div>
  </aside>

  <section class="glass-card clean-pool-coverage-card">
    <div class="clean-check-head compact"><div><h3>Cobertura por alberca</h3><span>Avance higiénico de las 5 albercas</span></div><i class="fa-solid fa-water-ladder"></i></div>
    <div class="clean-pool-coverage-list">
      <?php foreach($poolNames as $pool):
        $st=$poolStats[$pool] ?? ['total'=>0,'done'=>0,'pending'=>0,'overdue'=>0];
        $pct=$st['total'] ? (int)round(($st['done']/$st['total'])*100) : 0;
        $cls=$st['overdue']>0?'danger':($pct<100?'warning':'success');
      ?>
      <div class="clean-pool-coverage-line <?= e($cls) ?>">
        <div><b><?= e($pool) ?></b><small><?= e((string)$st['done']) ?>/<?= e((string)$st['total']) ?> tareas · <?= e((string)$st['pending']) ?> pendientes</small></div>
        <div class="clean-mini-bar"><span style="width:<?= e((string)$pct) ?>%"></span></div>
        <strong><?= e((string)$pct) ?>%</strong>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card clean-critical-card">
    <div class="clean-check-head compact"><div><h3>Prioridad inmediata</h3><span>Orden sugerido por hora límite</span></div><i class="fa-solid fa-route"></i></div>
    <div class="clean-critical-list">
      <?php foreach(array_slice($pendingRows,0,4) as $i=>$x):
        $risk = !empty($x['_overdue']);
      ?>
      <div class="clean-critical-line <?= $risk ? 'danger' : '' ?>">
        <span><?= e((string)($i+1)) ?></span>
        <div><b><?= e((string)$x['tarea']) ?></b><small><?= e((string)$x['alberca']) ?> · <?= e((string)$x['area']) ?></small></div>
        <em><?= e(substr((string)$x['hora_limite'],0,5)) ?></em>
      </div>
      <?php endforeach; ?>
      <?php if(!$pendingRows): ?><div class="clean-done-message"><i class="fa-solid fa-circle-check"></i><b>Checklist completo</b><span>No hay pendientes activos.</span></div><?php endif; ?>
    </div>
  </section>

  <aside class="glass-card clean-close-card">
    <div class="clean-check-head compact"><div><h3>Cierre del turno</h3><span>Resumen para bitácora</span></div><i class="fa-solid fa-flag-checkered"></i></div>
    <div class="clean-close-stack">
      <div class="clean-recommendation <?= e($qualityClass) ?>"><i class="fa-solid fa-lightbulb"></i><span><?= $overdue ? 'Atiende primero las tareas vencidas y registra observación de cierre.' : ($pending ? 'Continúa con la siguiente tarea antes del límite del turno.' : 'Turno listo para cierre; revisa historial y reporta incidencias si aplica.') ?></span></div>
      <div class="clean-quick-links">
        <a href="<?= e(page_url('limpieza-incidencias')) ?>"><i class="fa-solid fa-circle-exclamation"></i><span>Reportar incidencia</span></a>
        <a href="<?= e(page_url('limpieza-historial')) ?>"><i class="fa-solid fa-clock-rotate-left"></i><span>Ver historial</span></a>
      </div>
    </div>
  </aside>
</div>
<script>
(function(){
  const filterButtons = document.querySelectorAll('[data-clean-filter]');
  const rows = document.querySelectorAll('#cleanChecklistTable tbody tr[data-status]');
  filterButtons.forEach(btn=>btn.addEventListener('click',()=>{
    filterButtons.forEach(x=>x.classList.remove('active'));
    btn.classList.add('active');
    const f = btn.dataset.cleanFilter;
    rows.forEach(row=>{ row.style.display = (f === 'all' || row.dataset.status === f) ? '' : 'none'; });
  }));
  const areaCanvas = document.getElementById('cleanAreaChart');
  if(areaCanvas && window.Chart){
    new Chart(areaCanvas,{
      type:'bar',
      data:{labels:<?= json_encode($areaLabels, JSON_UNESCAPED_UNICODE) ?>,datasets:[{data:<?= json_encode($areaValues) ?>,borderRadius:10,borderSkipped:false}]},
      options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:(ctx)=>ctx.raw+'% completado'}}},scales:{x:{grid:{display:false},ticks:{font:{size:9,weight:'700'}}},y:{display:false,min:0,max:100}}}
    });
  }
})();
</script>
