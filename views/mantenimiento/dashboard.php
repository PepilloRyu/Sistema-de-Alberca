<?php
$metrics = $metrics ?? ['total'=>0,'abiertos'=>0,'criticos'=>0];
$tickets = $tickets ?? [];
$schedule = $schedule ?? [];
$equipment = $equipment ?? [];
$openTickets = (int)($metrics['abiertos'] ?? 0);
$totalTickets = (int)($metrics['total'] ?? count($tickets));
$criticalTickets = (int)($metrics['criticos'] ?? 0);
$today = date('Y-m-d');
$todaySchedule = array_values(array_filter($schedule, fn($x)=>($x['fecha_programada'] ?? '') === $today));
$activeSchedule = array_values(array_filter($schedule, fn($x)=>in_array(strtolower((string)($x['estado'] ?? 'programado')), ['programado','en_proceso','activo'], true)));
$equipmentRisk = array_values(array_filter($equipment, fn($x)=>in_array(strtolower((string)($x['estado'] ?? '')), ['critico','revision','fuera_servicio'], true)));
$nextTicket = $tickets[0] ?? null;
$firstWait = $nextTicket && !empty($nextTicket['creado_en']) ? max(1, (int)floor((time()-strtotime((string)$nextTicket['creado_en']))/60)) : 0;
$priorityCounts = [];
$statusCounts = [];
$poolCounts = [];
foreach ($tickets as $ticket) {
  $priority = (string)($ticket['prioridad'] ?? 'Sin prioridad');
  $status = (string)($ticket['estado'] ?? 'Sin estado');
  $pool = (string)($ticket['alberca'] ?? 'Sin alberca');
  $priorityCounts[$priority] = ($priorityCounts[$priority] ?? 0) + 1;
  $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
  $poolCounts[$pool] = ($poolCounts[$pool] ?? 0) + 1;
}
if (!$priorityCounts) $priorityCounts = ['Alta'=>1,'Media'=>1,'Baja'=>0];
if (!$statusCounts) $statusCounts = ['Nuevo'=>1,'Asignado'=>1,'En proceso'=>0];
$weekly = [];
for ($i=0; $i<7; $i++) {
  $date = date('Y-m-d', strtotime('+'.$i.' day'));
  $weekly[$date] = ['label'=>mb_substr(['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'][(int)date('w', strtotime($date))],0,3),'count'=>0,'items'=>[]];
}
foreach ($schedule as $item) {
  $date = (string)($item['fecha_programada'] ?? '');
  if (isset($weekly[$date])) { $weekly[$date]['count']++; $weekly[$date]['items'][] = $item; }
}
$recommendation = $criticalTickets > 0 ? 'Atender primero los tickets críticos sin romper FIFO por prioridad.' : ($openTickets > 0 ? 'Tomar el primer ticket disponible y registrar seguimiento antes de 12 horas.' : 'Mantener monitoreo preventivo y preparar siguiente revisión programada.');
?>
<div class="mant-dash-v36">
  <section class="mant-kpis-v36">
    <article class="mant-kpi-v36 danger">
      <i class="fa-solid fa-ticket"></i>
      <div><span>Tickets abiertos</span><b><?= e($openTickets) ?></b><small>Total histórico <?= e($totalTickets) ?></small></div>
    </article>
    <article class="mant-kpi-v36 warning">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <div><span>Críticos / altos</span><b><?= e($criticalTickets) ?></b><small>FIFO con prioridad</small></div>
    </article>
    <article class="mant-kpi-v36 aqua">
      <i class="fa-solid fa-calendar-day"></i>
      <div><span>Agenda hoy</span><b><?= e(count($todaySchedule)) ?></b><small><?= e(count($activeSchedule)) ?> activos próximos</small></div>
    </article>
    <article class="mant-kpi-v36 violet">
      <i class="fa-solid fa-gears"></i>
      <div><span>Equipos en revisión</span><b><?= e(count($equipmentRisk)) ?></b><small><?= e(count($equipment)) ?> registrados</small></div>
    </article>
    <article class="mant-kpi-v36 ocean">
      <i class="fa-solid fa-hourglass-half"></i>
      <div><span>Mayor espera</span><b><?= e($firstWait) ?>m</b><small>Primer ticket visible</small></div>
    </article>
  </section>

  <section class="glass-card mant-fifo-card-v36">
    <div class="mant-section-head-v36">
      <div>
        <span>Atención técnica</span>
        <h3>Cola FIFO de tickets</h3>
      </div>
      <a class="mant-mini-btn-v36" href="<?= e(page_url('mantenimiento-tickets')) ?>"><i class="fa-solid fa-list-check"></i> Ver todos</a>
    </div>
    <div class="mant-fifo-summary-v36">
      <div class="mant-next-ticket-v36">
        <span>Primero en atender</span>
        <?php if($nextTicket): ?>
          <b><?= e($nextTicket['folio'] ?? 'TK') ?></b>
          <small><?= e($nextTicket['alberca'] ?? '') ?> · <?= e($nextTicket['prioridad'] ?? '') ?> · espera <?= e($firstWait) ?> min</small>
        <?php else: ?>
          <b>Sin cola activa</b>
          <small>No hay tickets pendientes para este técnico.</small>
        <?php endif; ?>
      </div>
      <div class="mant-reco-v36"><i class="fa-solid fa-route"></i><span><?= e($recommendation) ?></span></div>
    </div>
    <div class="mant-ticket-table-wrap-v36">
      <table class="mant-ticket-table-v36">
        <thead><tr><th>#</th><th>Ticket</th><th>Alberca</th><th>Prioridad</th><th>Estado</th><th>Espera</th></tr></thead>
        <tbody>
        <?php foreach(array_slice($tickets,0,7) as $i=>$ticket):
          $wait = !empty($ticket['creado_en']) ? max(1, (int)floor((time()-strtotime((string)$ticket['creado_en']))/60)) : 0;
        ?>
          <tr>
            <td><em><?= e($i+1) ?></em></td>
            <td><b><?= e($ticket['folio'] ?? 'TK') ?></b><small><?= e($ticket['tipo'] ?? '') ?></small></td>
            <td><?= e($ticket['alberca'] ?? '') ?></td>
            <td><span class="mant-pill-v36 <?= e(sev($ticket['prioridad'] ?? '')) ?>"><?= e($ticket['prioridad'] ?? '') ?></span></td>
            <td><span class="mant-state-v36"><?= e($ticket['estado'] ?? '') ?></span></td>
            <td><strong><?= e($wait) ?>m</strong></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$tickets): ?><tr><td colspan="6"><b>Sin tickets pendientes</b><small>La cola técnica está limpia.</small></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <aside class="mant-side-v36">
    <section class="glass-card mant-chart-card-v36">
      <div class="mant-section-head-v36 compact"><div><span>Salud técnica</span><h3>Estados de tickets</h3></div></div>
      <div class="mant-chart-box-v36"><canvas id="mantStatusChartV36"></canvas></div>
      <div class="mant-status-list-v36">
        <?php foreach($statusCounts as $status=>$total): ?>
          <div><span><?= e($status) ?></span><b><?= e($total) ?></b></div>
        <?php endforeach; ?>
      </div>
    </section>
    <section class="glass-card mant-critical-card-v36">
      <div class="mant-section-head-v36 compact"><div><span>Equipos críticos</span><h3>Riesgo operativo</h3></div></div>
      <div class="mant-equipment-list-v36">
        <?php foreach(array_slice($equipmentRisk ?: $equipment,0,4) as $eq): ?>
          <div class="mant-equipment-line-v36 <?= e(sev($eq['estado'] ?? '')) ?>">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            <div><b><?= e($eq['nombre'] ?? 'Equipo') ?></b><small><?= e($eq['alberca'] ?? '') ?> · <?= e($eq['tipo'] ?? '') ?></small></div>
            <span><?= e($eq['estado'] ?? '') ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </aside>

  <section class="glass-card mant-agenda-card-v36">
    <div class="mant-section-head-v36">
      <div><span>Próximos 7 días</span><h3>Agenda técnica</h3></div>
      <a class="mant-mini-btn-v36" href="<?= e(page_url('mantenimiento-agenda')) ?>"><i class="fa-regular fa-calendar"></i> Agenda</a>
    </div>
    <div class="mant-week-v36">
      <?php foreach($weekly as $date=>$day):
        $level = min(100, ((int)$day['count']) * 34);
      ?>
        <div class="<?= $date===$today?'active':'' ?>">
          <span><?= e($day['label']) ?></span>
          <b><?= e(date('d', strtotime($date))) ?></b>
          <i><em style="height:<?= e((string)max(8,$level)) ?>%"></em></i>
          <small><?= e($day['count']) ?> mant.</small>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mant-agenda-list-v36">
      <?php foreach(array_slice($schedule,0,4) as $item): ?>
        <article>
          <time><?= e(substr((string)($item['hora_inicio'] ?? '00:00'),0,5)) ?></time>
          <div><b><?= e($item['alberca'] ?? '') ?></b><small><?= e($item['tipo'] ?? '') ?> · <?= e($item['descripcion'] ?? '') ?></small></div>
          <span><?= e($item['estado'] ?? 'programado') ?></span>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card mant-pool-pressure-v36">
    <div class="mant-section-head-v36 compact"><div><span>Origen de tickets</span><h3>Presión por alberca</h3></div></div>
    <div class="mant-pool-list-v36">
      <?php $maxPool=max(1, ...array_values($poolCounts ?: ['Sin datos'=>1])); foreach(($poolCounts ?: ['Sin datos'=>0]) as $pool=>$count): $pct = (int)round(($count/$maxPool)*100); ?>
        <div>
          <b><?= e($pool) ?></b>
          <i><em style="width:<?= e((string)$pct) ?>%"></em></i>
          <strong><?= e($count) ?></strong>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card mant-actions-v36">
    <div class="mant-section-head-v36 compact"><div><span>Acciones rápidas</span><h3>Operación del técnico</h3></div></div>
    <div class="mant-action-grid-v36">
      <a href="<?= e(page_url('mantenimiento-tickets')) ?>"><i class="fa-solid fa-hand"></i><b>Tomar ticket</b><small>FIFO</small></a>
      <a href="<?= e(page_url('mantenimiento-agenda')) ?>"><i class="fa-solid fa-calendar-check"></i><b>Ver agenda</b><small>Preventivos</small></a>
      <a href="<?= e(page_url('mantenimiento-equipos')) ?>"><i class="fa-solid fa-gears"></i><b>Equipos</b><small>Revisiones</small></a>
      <a href="<?= e(page_url('mantenimiento-historial')) ?>"><i class="fa-solid fa-clock-rotate-left"></i><b>Historial</b><small>Bitácora</small></a>
    </div>
  </section>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
  const statusCtx=document.getElementById('mantStatusChartV36');
  if(statusCtx && window.Chart){
    new Chart(statusCtx,{type:'doughnut',data:{labels:<?= json_encode(array_keys($statusCounts), JSON_UNESCAPED_UNICODE) ?>,datasets:[{data:<?= json_encode(array_values($statusCounts)) ?>,borderWidth:0,cutout:'68%'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},animation:{duration:700}}});
  }
});
</script>
