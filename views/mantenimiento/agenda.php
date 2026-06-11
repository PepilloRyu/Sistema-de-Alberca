<?php
$events = is_array($schedule ?? null) ? $schedule : [];
$today = new DateTimeImmutable('today');
$week = [];
for ($i=0; $i<7; $i++) {
  $d = $today->modify("+$i day");
  $week[$d->format('Y-m-d')] = [
    'date'=>$d,
    'label'=>['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'][(int)$d->format('w')],
    'day'=>$d->format('d'),
    'events'=>[]
  ];
}
foreach ($events as $ev) {
  $dateKey = (string)($ev['fecha_programada'] ?? '');
  if (isset($week[$dateKey])) $week[$dateKey]['events'][] = $ev;
}
$total = count($events);
$todayCount = count($week[$today->format('Y-m-d')]['events'] ?? []);
$weekCount = 0;
$preventivos = 0;
$correctivos = 0;
$enProceso = 0;
$poolLoad = [];
$typeLoad = [];
foreach ($events as $ev) {
  $fecha = (string)($ev['fecha_programada'] ?? '');
  if (isset($week[$fecha])) $weekCount++;
  $tipo = mb_strtolower((string)($ev['tipo'] ?? ''));
  if (str_contains($tipo,'prevent')) $preventivos++;
  elseif (str_contains($tipo,'correct') || str_contains($tipo,'emerg')) $correctivos++;
  $estado = mb_strtolower((string)($ev['estado'] ?? 'programado'));
  if (str_contains($estado,'proceso')) $enProceso++;
  $pool = (string)($ev['alberca'] ?? 'Sin alberca');
  $poolLoad[$pool] = ($poolLoad[$pool] ?? 0) + 1;
  $type = (string)($ev['tipo'] ?? 'Mantenimiento');
  $typeLoad[$type] = ($typeLoad[$type] ?? 0) + 1;
}
arsort($poolLoad);
arsort($typeLoad);
$next = $events[0] ?? null;
$agendaRows = array_slice($events,0,8);
$maxPool = max(1, ...array_values($poolLoad ?: [0]));
$labels = array_map(fn($d)=>$d['label'], array_values($week));
$counts = array_map(fn($d)=>count($d['events']), array_values($week));
$typeLabels = array_keys($typeLoad);
$typeCounts = array_values($typeLoad);
$formatTime = fn($t)=>$t ? substr((string)$t,0,5) : '--:--';
$eventTone = function($type,$estado='') {
  $type = mb_strtolower((string)$type); $estado = mb_strtolower((string)$estado);
  if (str_contains($estado,'proceso')) return 'info';
  if (str_contains($type,'emerg') || str_contains($type,'correct')) return 'danger';
  if (str_contains($type,'inspe')) return 'violet';
  return 'success';
};
?>
<div class="mant-agenda-v38">
  <section class="mant-agenda-kpis-v38">
    <article class="mant-agenda-kpi-v38 primary">
      <i class="fa-solid fa-calendar-check"></i>
      <span>Hoy</span><b><?= (int)$todayCount ?></b><small>servicios programados</small>
    </article>
    <article class="mant-agenda-kpi-v38">
      <i class="fa-solid fa-calendar-week"></i>
      <span>Semana</span><b><?= (int)$weekCount ?></b><small>próximos 7 días</small>
    </article>
    <article class="mant-agenda-kpi-v38 success">
      <i class="fa-solid fa-shield-heart"></i>
      <span>Preventivos</span><b><?= (int)$preventivos ?></b><small>protegen operación</small>
    </article>
    <article class="mant-agenda-kpi-v38 danger">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <span>Correctivos</span><b><?= (int)$correctivos ?></b><small>requieren atención</small>
    </article>
    <article class="mant-agenda-kpi-v38 violet">
      <i class="fa-solid fa-person-running"></i>
      <span>En proceso</span><b><?= (int)$enProceso ?></b><small>seguimiento activo</small>
    </article>
  </section>

  <section class="glass-card mant-calendar-card-v38">
    <div class="mant-agenda-head-v38">
      <div><span>Calendario técnico</span><h3>Agenda semanal de mantenimientos</h3></div>
      <b><i class="fa-regular fa-clock"></i> Horario según alberca</b>
    </div>
    <div class="mant-calendar-grid-v38">
      <?php foreach ($week as $dateKey=>$day): ?>
        <article class="mant-day-v38 <?= $dateKey===$today->format('Y-m-d')?'today':'' ?>">
          <header><span><?= e($day['label']) ?></span><b><?= e($day['day']) ?></b><small><?= count($day['events']) ?> serv.</small></header>
          <div class="mant-day-events-v38">
            <?php if (!$day['events']): ?>
              <div class="mant-empty-day-v38">Sin agenda</div>
            <?php else: foreach (array_slice($day['events'],0,3) as $ev): $tone=$eventTone($ev['tipo']??'', $ev['estado']??''); ?>
              <div class="mant-cal-event-v38 <?= e($tone) ?>">
                <time><?= e($formatTime($ev['hora_inicio'] ?? '')) ?></time>
                <b><?= e($ev['tipo'] ?? 'Mantenimiento') ?></b>
                <small><?= e($ev['alberca'] ?? 'Alberca') ?></small>
              </div>
            <?php endforeach; if (count($day['events'])>3): ?>
              <div class="mant-more-v38">+<?= count($day['events'])-3 ?> más</div>
            <?php endif; endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="mant-calendar-bottom-v38">
      <div class="mant-mini-chart-v38"><canvas id="agendaWeekChart"></canvas></div>
      <div class="mant-calendar-notes-v38">
        <div><i class="fa-solid fa-circle-check"></i><span>Regla</span><b>Atender primero correctivos críticos y después preventivos programados.</b></div>
        <div><i class="fa-solid fa-stopwatch"></i><span>Ventana segura</span><b>Evitar intervenciones en picos de aforo salvo emergencia.</b></div>
      </div>
    </div>
  </section>

  <aside class="mant-agenda-side-v38">
    <section class="glass-card mant-next-card-v38">
      <div class="mant-agenda-head-v38 compact"><div><span>Siguiente atención</span><h3>Servicio prioritario</h3></div></div>
      <?php if ($next): ?>
        <div class="mant-next-main-v38 <?= e($eventTone($next['tipo']??'', $next['estado']??'')) ?>">
          <time><?= e($formatTime($next['hora_inicio'] ?? '')) ?> - <?= e($formatTime($next['hora_fin'] ?? '')) ?></time>
          <h4><?= e($next['alberca'] ?? 'Alberca') ?></h4>
          <p><?= e($next['descripcion'] ?? 'Mantenimiento programado') ?></p>
          <div><span><?= e($next['tipo'] ?? 'Mantenimiento') ?></span><span><?= e($next['estado'] ?? 'programado') ?></span></div>
        </div>
      <?php else: ?>
        <div class="mant-empty-state-v38"><i class="fa-solid fa-mug-hot"></i><b>Sin servicios próximos</b><small>No hay mantenimientos agendados para esta semana.</small></div>
      <?php endif; ?>
      <div class="mant-checklist-v38">
        <h4>Antes de iniciar</h4>
        <label><i class="fa-solid fa-check"></i> Confirmar alberca y equipo</label>
        <label><i class="fa-solid fa-check"></i> Validar estado operativo</label>
        <label><i class="fa-solid fa-check"></i> Registrar seguimiento al cerrar</label>
      </div>
    </section>
    <section class="glass-card mant-load-card-v38">
      <div class="mant-agenda-head-v38 compact"><div><span>Carga por alberca</span><h3>Presión técnica</h3></div></div>
      <div class="mant-load-list-v38">
        <?php foreach (array_slice($poolLoad,0,5) as $pool=>$count): $pct=(int)round(($count/$maxPool)*100); ?>
          <div class="mant-load-row-v38"><b><?= e($pool) ?></b><span><i style="width:<?= $pct ?>%"></i></span><strong><?= (int)$count ?></strong></div>
        <?php endforeach; ?>
      </div>
    </section>
  </aside>

  <section class="glass-card mant-agenda-table-card-v38">
    <div class="mant-agenda-head-v38 compact">
      <div><span>Orden de trabajo</span><h3>Servicios programados</h3></div>
      <a href="<?= e(page_url('mantenimiento-tickets')) ?>"><i class="fa-solid fa-list-check"></i> Tickets FIFO</a>
    </div>
    <div class="mant-agenda-table-wrap-v38">
      <table class="mant-agenda-table-v38" id="mantAgendaTable">
        <thead><tr><th>Fecha</th><th>Horario</th><th>Alberca</th><th>Tipo</th><th>Estado</th><th>Descripción</th></tr></thead>
        <tbody>
          <?php foreach ($agendaRows as $ev): $tone=$eventTone($ev['tipo']??'', $ev['estado']??''); ?>
            <tr>
              <td><b><?= e($ev['fecha_programada'] ?? '') ?></b></td>
              <td><span class="mono-v38"><?= e($formatTime($ev['hora_inicio'] ?? '')) ?> - <?= e($formatTime($ev['hora_fin'] ?? '')) ?></span></td>
              <td><?= e($ev['alberca'] ?? '') ?></td>
              <td><span class="mant-pill-v38 <?= e($tone) ?>"><?= e($ev['tipo'] ?? '') ?></span></td>
              <td><span class="mant-state-v38"><?= e($ev['estado'] ?? 'programado') ?></span></td>
              <td><?= e($ev['descripcion'] ?? '') ?></td>
            </tr>
          <?php endforeach; if (!$agendaRows): ?>
            <tr><td colspan="6">No hay mantenimientos programados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="glass-card mant-type-card-v38">
    <div class="mant-agenda-head-v38 compact"><div><span>Distribución</span><h3>Tipo de trabajo</h3></div></div>
    <div class="mant-type-body-v38">
      <canvas id="agendaTypeChart"></canvas>
      <div class="mant-type-legend-v38">
        <?php foreach ($typeLoad as $label=>$count): ?>
          <div><span><?= e($label) ?></span><b><?= (int)$count ?></b></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</div>
<script>
(() => {
  const weekCtx = document.getElementById('agendaWeekChart');
  if (weekCtx && window.Chart) {
    new Chart(weekCtx, {type:'bar', data:{labels:<?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>, datasets:[{label:'Servicios', data:<?= json_encode($counts) ?>, borderRadius:10}]}, options:{responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{x:{grid:{display:false},ticks:{font:{size:9,weight:'700'}}}, y:{display:false, beginAtZero:true}}}});
  }
  const typeCtx = document.getElementById('agendaTypeChart');
  if (typeCtx && window.Chart) {
    new Chart(typeCtx, {type:'doughnut', data:{labels:<?= json_encode($typeLabels, JSON_UNESCAPED_UNICODE) ?>, datasets:[{data:<?= json_encode($typeCounts) ?>, borderWidth:0}]}, options:{responsive:true, maintainAspectRatio:false, cutout:'68%', plugins:{legend:{display:false}}}});
  }
})();
</script>
