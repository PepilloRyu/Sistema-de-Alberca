<?php
$tickets = $tickets ?? [];
$schedule = $schedule ?? [];
$equipment = $equipment ?? [];
$short = function(string $text, int $max=70): string {
  $text = trim($text);
  if (function_exists('mb_strimwidth')) return mb_strimwidth($text, 0, $max, '...', 'UTF-8');
  return strlen($text) > $max ? substr($text,0,$max-3).'...' : $text;
};
$fmtDate = function(?string $date): string {
  if (!$date) return 'Sin fecha';
  $ts = strtotime($date);
  return $ts ? date('d/m/Y', $ts) : $date;
};
$fmtTime = function(?string $time): string {
  if (!$time) return '--:--';
  $ts = strtotime($time);
  return $ts ? date('H:i', $ts) : substr($time,0,5);
};
$waitLabel = function(int $min): string {
  if ($min <= 0) return '0m';
  if ($min < 60) return $min.'m';
  $h = intdiv($min,60); $m = $min % 60;
  return $h.'h'.($m ? ' '.$m.'m' : '');
};
$stateTone = function(string $state): string {
  $s = mb_strtolower($state);
  if (str_contains($s,'concluido') || str_contains($s,'cerrado')) return 'success';
  if (str_contains($s,'cancel')) return 'muted';
  if (str_contains($s,'proceso') || str_contains($s,'asignado')) return 'warning';
  return 'info';
};
$totalTickets = count($tickets);
$closedTickets = 0;
$activeTickets = 0;
$totalMinutes = 0;
$closedMinutes = 0;
$criticalClosed = 0;
$followUps = 0;
$byPool = [];
$byState = [];
$byDay = [];
$recentActivity = [];
foreach ($tickets as $ticket) {
  $state = (string)($ticket['estado'] ?? 'Nuevo');
  $isFinal = !empty($ticket['es_final']) || in_array($stateTone($state), ['success','muted'], true);
  if ($isFinal) $closedTickets++; else $activeTickets++;
  $min = max(0, (int)($ticket['minutos_atencion'] ?? 0));
  $totalMinutes += $min;
  if ($isFinal) $closedMinutes += $min;
  if ((int)($ticket['prioridad_nivel'] ?? 0) >= 3 && $isFinal) $criticalClosed++;
  $followUps += (int)($ticket['seguimientos'] ?? 0);
  $pool = (string)($ticket['alberca'] ?? 'Sin alberca');
  $byPool[$pool] = ($byPool[$pool] ?? 0) + 1;
  $byState[$state] = ($byState[$state] ?? 0) + 1;
  $day = date('d/m', strtotime((string)($ticket['creado_en'] ?? 'now')) ?: time());
  $byDay[$day] = ($byDay[$day] ?? 0) + 1;
  $recentActivity[] = [
    'type'=>'ticket',
    'date'=>$ticket['cerrado_en'] ?? $ticket['ultimo_evento'] ?? $ticket['actualizado_en'] ?? $ticket['creado_en'] ?? null,
    'title'=>($ticket['folio'] ?? 'Ticket').' · '.($ticket['estado'] ?? 'Estado'),
    'subtitle'=>($ticket['alberca'] ?? 'Alberca').' · '.($ticket['tipo'] ?? 'Incidencia'),
    'tone'=>$stateTone($state)
  ];
}
$completedServices = 0;
$preventiveServices = 0;
$correctiveServices = 0;
foreach ($schedule as $svc) {
  $st = mb_strtolower((string)($svc['estado'] ?? 'programado'));
  if (str_contains($st,'concluido')) $completedServices++;
  $type = mb_strtolower((string)($svc['tipo'] ?? ''));
  if (str_contains($type,'prevent')) $preventiveServices++;
  if (str_contains($type,'correct') || str_contains($type,'emerg')) $correctiveServices++;
  $recentActivity[] = [
    'type'=>'service',
    'date'=>($svc['fecha_programada'] ?? date('Y-m-d')).' '.($svc['hora_inicio'] ?? '00:00:00'),
    'title'=>($svc['tipo'] ?? 'Mantenimiento').' · '.($svc['estado'] ?? 'programado'),
    'subtitle'=>($svc['alberca'] ?? 'Alberca').' · '.($svc['descripcion'] ?? ''),
    'tone'=>str_contains($st,'concluido') ? 'success' : (str_contains($st,'cancel') ? 'muted' : 'info')
  ];
}
usort($recentActivity, fn($a,$b)=> (strtotime((string)($b['date'] ?? '')) ?: 0) <=> (strtotime((string)($a['date'] ?? '')) ?: 0));
$recentActivity = array_slice($recentActivity,0,7);
$avgMinutes = $closedTickets ? (int)round($closedMinutes / max(1,$closedTickets)) : ($totalTickets ? (int)round($totalMinutes/max(1,$totalTickets)) : 0);
$avgLabel = $waitLabel($avgMinutes);
$equipmentReviewed = count(array_filter($equipment, fn($e)=>!empty($e['ultima_revision']) && (strtotime((string)$e['ultima_revision']) ?: 0) >= strtotime('-30 days')));
if (!$byState) $byState = ['Concluido'=>0,'En proceso'=>0,'Nuevo'=>0];
if (!$byPool) $byPool = ['Alberca principal'=>0,'Alberca familiar'=>0,'Alberca infantil'=>0,'Alberca vista al mar'=>0,'Alberca deportiva'=>0];
$poolMax = max(array_merge([1], array_values($byPool)));
$dayLabels = array_keys($byDay ?: ['Lun'=>0,'Mar'=>0,'Mié'=>0,'Jue'=>0,'Vie'=>0]);
$dayCounts = array_values($byDay ?: ['Lun'=>0,'Mar'=>0,'Mié'=>0,'Jue'=>0,'Vie'=>0]);
$stateLabels = array_keys($byState);
$stateCounts = array_values($byState);
?>
<div class="mant-history-v41">
  <section class="mant-history-kpis-v41">
    <article class="mh-kpi-v41 ocean"><i class="fa-solid fa-clock-rotate-left"></i><div><span>Historial técnico</span><b><?= e($totalTickets + count($schedule)) ?></b><small>registros trazables</small></div></article>
    <article class="mh-kpi-v41 success"><i class="fa-solid fa-circle-check"></i><div><span>Cierres</span><b><?= e($closedTickets + $completedServices) ?></b><small>tickets + servicios</small></div></article>
    <article class="mh-kpi-v41 warning"><i class="fa-solid fa-stopwatch"></i><div><span>Prom. atención</span><b><?= e($avgLabel) ?></b><small>tiempo técnico</small></div></article>
    <article class="mh-kpi-v41 violet"><i class="fa-solid fa-shield-halved"></i><div><span>Críticos cerrados</span><b><?= e($criticalClosed) ?></b><small>prioridad alta/crítica</small></div></article>
    <article class="mh-kpi-v41 aqua"><i class="fa-solid fa-gears"></i><div><span>Equipos revisados</span><b><?= e($equipmentReviewed) ?></b><small>últimos 30 días</small></div></article>
  </section>

  <section class="glass-card mh-analytics-v41">
    <div class="mh-head-v41">
      <div><span>Desempeño</span><h3>Producción técnica y trazabilidad</h3></div>
      <div class="mh-export-v41"><i class="fa-solid fa-file-lines"></i> Historial operativo</div>
    </div>
    <div class="mh-analytics-grid-v41">
      <div class="mh-chart-v41"><canvas id="mantHistoryTrendChart"></canvas></div>
      <div class="mh-score-stack-v41">
        <div><span>Seguimientos</span><b><?= e($followUps) ?></b><small>bitácoras registradas</small></div>
        <div><span>Preventivos</span><b><?= e($preventiveServices) ?></b><small>servicios planeados</small></div>
        <div><span>Correctivos</span><b><?= e($correctiveServices) ?></b><small>servicios por falla</small></div>
      </div>
    </div>
  </section>

  <section class="glass-card mh-table-card-v41">
    <div class="mh-head-v41">
      <div><span>Bitácora FIFO</span><h3>Historial de tickets de mantenimiento</h3></div>
      <div class="mh-tools-v41">
        <label><i class="fa-solid fa-magnifying-glass"></i><input id="mantHistorySearch" type="search" placeholder="Buscar folio, alberca, técnico o descripción"></label>
        <select id="mantHistoryState"><option value="">Todos</option><option value="concluido">Concluidos</option><option value="activo">Activos</option><option value="alta">Alta / crítica</option></select>
      </div>
    </div>
    <div class="mh-table-wrap-v41">
      <table class="mh-table-v41" id="mantHistoryTable">
        <thead><tr><th>Folio</th><th>Alberca / tipo</th><th>Prioridad</th><th>Estado</th><th>Atención</th><th>Seguimientos</th><th>Cierre</th></tr></thead>
        <tbody>
          <?php foreach($tickets as $ticket):
            $state = (string)($ticket['estado'] ?? 'Nuevo');
            $tone = $stateTone($state);
            $isFinal = !empty($ticket['es_final']) || in_array($tone,['success','muted'],true);
            $prio = (string)($ticket['prioridad'] ?? 'Media');
            $prioKey = mb_strtolower($prio);
            $min = max(0,(int)($ticket['minutos_atencion'] ?? 0));
            $search = mb_strtolower(trim(($ticket['folio'] ?? '').' '.($ticket['alberca'] ?? '').' '.($ticket['tipo'] ?? '').' '.($ticket['tecnico'] ?? '').' '.($ticket['descripcion'] ?? '').' '.$state.' '.$prio));
          ?>
          <tr data-history-row data-state="<?= e($isFinal?'concluido':'activo') ?>" data-priority="<?= e($prioKey) ?>" data-search="<?= e($search) ?>">
            <td class="mh-folio-v41"><b><?= e($ticket['folio'] ?? 'TK') ?></b><small><?= e(fdt($ticket['creado_en'] ?? null)) ?></small></td>
            <td><b><?= e($ticket['alberca'] ?? 'Alberca') ?></b><small><?= e($ticket['tipo'] ?? 'Incidencia') ?> · <?= e($short((string)($ticket['descripcion'] ?? ''),52)) ?></small></td>
            <td><span class="mh-pill-v41 <?= e(sev($prio)) ?>"><?= e($prio) ?></span></td>
            <td><span class="mh-state-v41 <?= e($tone) ?>"><?= e($state) ?></span><small><?= e($ticket['tecnico'] ?? 'Sin asignar') ?></small></td>
            <td><strong><?= e($waitLabel($min)) ?></strong><small><?= $min>=720?'RN04 revisar':'Dentro de control' ?></small></td>
            <td><b><?= e((int)($ticket['seguimientos'] ?? 0)) ?></b><small><?= e(fdt($ticket['ultimo_evento'] ?? $ticket['ultimo_seguimiento_en'] ?? null)) ?></small></td>
            <td><b><?= e($fmtDate($ticket['cerrado_en'] ?? $ticket['actualizado_en'] ?? null)) ?></b><small><?= e($short((string)($ticket['cierre_motivo'] ?? ($isFinal ? 'Cierre técnico' : 'Pendiente')),38)) ?></small></td>
          </tr>
          <?php endforeach; ?>
          <?php if(!$tickets): ?><tr><td colspan="7"><div class="mh-empty-v41"><i class="fa-solid fa-circle-info"></i><b>Sin historial técnico</b><small>Cuando cierres tickets o registres seguimientos aparecerán aquí.</small></div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <aside class="mh-side-v41">
    <section class="glass-card mh-timeline-card-v41">
      <div class="mh-head-v41 mini"><div><span>Actividad reciente</span><h3>Bitácora</h3></div></div>
      <div class="mh-timeline-v41">
        <?php foreach($recentActivity as $item): ?>
        <div class="mh-timeline-item-v41 <?= e($item['tone']) ?>"><i class="fa-solid <?= $item['type']==='service'?'fa-calendar-check':'fa-ticket' ?>"></i><div><b><?= e($short((string)$item['title'],44)) ?></b><small><?= e($short((string)$item['subtitle'],58)) ?></small><em><?= e(fdt($item['date'] ?? null)) ?></em></div></div>
        <?php endforeach; ?>
      </div>
    </section>
    <section class="glass-card mh-pools-card-v41">
      <div class="mh-head-v41 mini"><div><span>Origen de trabajo</span><h3>Por alberca</h3></div></div>
      <div class="mh-pool-list-v41">
        <?php foreach($byPool as $pool=>$count): $w=(int)round(((int)$count/$poolMax)*100); ?>
          <div class="mh-pool-v41"><b><?= e($pool) ?></b><span><i style="width:<?= e($w) ?>%"></i></span><strong><?= e($count) ?></strong></div>
        <?php endforeach; ?>
      </div>
    </section>
    <section class="glass-card mh-status-card-v41">
      <div class="mh-head-v41 mini"><div><span>Estados</span><h3>Cierre vs activo</h3></div></div>
      <div class="mh-donut-v41"><canvas id="mantHistoryStateChart"></canvas></div>
    </section>
  </aside>

  <section class="glass-card mh-service-card-v41">
    <div class="mh-head-v41"><div><span>Servicios programados</span><h3>Historial de mantenimiento preventivo/correctivo</h3></div><b><?= e(count($schedule)) ?> registros</b></div>
    <div class="mh-service-grid-v41">
      <?php foreach(array_slice($schedule,0,6) as $svc): $st=mb_strtolower((string)($svc['estado'] ?? 'programado')); ?>
        <article class="mh-service-v41 <?= e(str_contains($st,'concluido')?'done':(str_contains($st,'cancel')?'cancel':'open')) ?>">
          <i class="fa-solid <?= str_contains(mb_strtolower((string)($svc['tipo'] ?? '')),'prevent')?'fa-shield-heart':'fa-screwdriver-wrench' ?>"></i>
          <div><span><?= e($svc['tipo'] ?? 'Mantenimiento') ?></span><b><?= e($svc['alberca'] ?? 'Alberca') ?></b><small><?= e($fmtDate($svc['fecha_programada'] ?? null)) ?> · <?= e($fmtTime($svc['hora_inicio'] ?? null)) ?>-<?= e($fmtTime($svc['hora_fin'] ?? null)) ?> · <?= e($svc['estado'] ?? 'programado') ?></small></div>
          <strong><?= e($waitLabel((int)($svc['duracion_min'] ?? 0))) ?></strong>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</div>
<script>
(() => {
  const q = document.getElementById('mantHistorySearch');
  const filter = document.getElementById('mantHistoryState');
  const rows = [...document.querySelectorAll('[data-history-row]')];
  const apply = () => {
    const term = (q?.value || '').trim().toLowerCase();
    const mode = filter?.value || '';
    rows.forEach(row => {
      const matchesText = !term || (row.dataset.search || '').includes(term);
      let matchesMode = true;
      if (mode === 'concluido') matchesMode = row.dataset.state === 'concluido';
      if (mode === 'activo') matchesMode = row.dataset.state === 'activo';
      if (mode === 'alta') matchesMode = ['alta','crítica','critica'].includes(row.dataset.priority || '');
      row.style.display = matchesText && matchesMode ? '' : 'none';
    });
  };
  q?.addEventListener('input', apply);
  filter?.addEventListener('change', apply);
  if (window.Chart) {
    const trend = document.getElementById('mantHistoryTrendChart');
    if (trend) new Chart(trend,{type:'bar',data:{labels:<?= json_encode($dayLabels, JSON_UNESCAPED_UNICODE) ?>,datasets:[{label:'Tickets',data:<?= json_encode($dayCounts) ?>,borderWidth:0,borderRadius:8}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{precision:0},grid:{color:'rgba(11,31,58,.08)'}}}});
    const states = document.getElementById('mantHistoryStateChart');
    if (states) new Chart(states,{type:'doughnut',data:{labels:<?= json_encode($stateLabels, JSON_UNESCAPED_UNICODE) ?>,datasets:[{data:<?= json_encode($stateCounts) ?>,borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,cutout:'66%',plugins:{legend:{display:false}}}});
  }
})();
</script>
