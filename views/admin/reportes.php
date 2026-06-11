<?php
$p = $p ?? [];
$q = $q ?? [];
$op = $op ?? ['entradas'=>0,'salidas'=>0,'ocupacion'=>0];
$flow = $flow ?? ['labels'=>[],'entradas'=>[],'salidas'=>[]];
$alerts = $alerts ?? [];
$tickets = $tickets ?? [];
$ticketStatus = $ticketStatus ?? [];
$ticketPriority = $ticketPriority ?? [];
$limpiezaMetrics = $limpiezaMetrics ?? ['total'=>0,'completas'=>0,'pendientes'=>0];
$checklist = $checklist ?? [];
$turnos = $turnos ?? [];
$mantMetrics = $mantMetrics ?? ['total'=>0,'hoy'=>0,'activos'=>0];
$schedule = $schedule ?? [];
$equipment = $equipment ?? [];
$userMetrics = $userMetrics ?? ['total'=>0,'activos'=>0,'pendientes'=>0];
$roles = $roles ?? [];

if (!function_exists('rep22_pool_label')) {
  function rep22_pool_label(string $name): string {
    $name = trim(str_ireplace('Alberca ', '', $name));
    return $name !== '' ? $name : 'alberca';
  }
  function rep22_pct(int|float $value, int|float $total): int {
    return $total > 0 ? max(0, min(100, (int)round(($value / $total) * 100))) : 0;
  }
  function rep22_moneyless_date(?string $date): string {
    if (!$date) return 'Sin fecha';
    $ts = strtotime($date);
    return $ts ? date('d/m/Y H:i', $ts) : 'Sin fecha';
  }
  function rep22_time(?string $time): string {
    return $time ? substr($time, 0, 5) : '--:--';
  }
  function rep22_tone(string $text): string {
    $s = strtolower($text);
    if (str_contains($s, 'crit') || str_contains($s, 'alta') || str_contains($s, 'mantenimiento') || str_contains($s, 'cerrada')) return 'danger';
    if (str_contains($s, 'media') || str_contains($s, 'limpieza') || str_contains($s, 'revision') || str_contains($s, 'proceso')) return 'warning';
    if (str_contains($s, 'nuevo') || str_contains($s, 'program')) return 'info';
    return 'success';
  }
  function rep22_wait(?string $datetime): string {
    if (!$datetime) return 'Sin fecha';
    $diff = max(0, time() - strtotime($datetime));
    if ($diff < 3600) return max(1, (int)floor($diff/60)).' min';
    if ($diff < 86400) return (int)floor($diff/3600).' h';
    return (int)floor($diff/86400).' d';
  }
}

$totalCapacity = array_sum(array_map(fn($x) => (int)($x['capacidad_maxima'] ?? 0), $p));
$totalOccupancy = array_sum(array_map(fn($x) => max(0, (int)($x['ocupacion_actual'] ?? 0)), $p));
$occupancyPct = rep22_pct($totalOccupancy, $totalCapacity);
$poolAttention = count(array_filter($p, fn($x) => !in_array((string)($x['estado_nombre'] ?? 'Disponible'), ['Disponible','En uso (profesional solamente)'], true)));
$openTickets = count($tickets);
$criticalTickets = count(array_filter($tickets, fn($x) => (int)($x['prioridad_nivel'] ?? ((($x['prioridad'] ?? '') === 'Alta') ? 3 : 0)) >= 3));
$cleanTotal = (int)($limpiezaMetrics['total'] ?? count($checklist));
$cleanDone = (int)($limpiezaMetrics['completas'] ?? 0);
$cleanPct = rep22_pct($cleanDone, $cleanTotal);
$cleanPending = max(0, (int)($limpiezaMetrics['total'] ?? 0) - (int)($limpiezaMetrics['completas'] ?? 0));
$equipTotal = count($equipment);
$equipRisk = count(array_filter($equipment, fn($x) => in_array((string)($x['estado'] ?? ''), ['revision','critico','fuera_servicio'], true)));
$equipOkPct = $equipTotal ? rep22_pct($equipTotal - $equipRisk, $equipTotal) : 100;
$entries = (int)($op['entradas'] ?? array_sum($flow['entradas'] ?? []));
$exits = (int)($op['salidas'] ?? array_sum($flow['salidas'] ?? []));
$netFlow = max(0, $entries - $exits);

$qualityByPool = [];
foreach ($q as $row) {
  $key = isset($row['idAlberca']) ? (string)$row['idAlberca'] : strtolower((string)($row['alberca'] ?? ''));
  $qualityByPool[$key] = $row;
}
$waterRows = [];
foreach ($p as $pool) {
  $key = (string)($pool['idAlberca'] ?? strtolower((string)($pool['nombre'] ?? '')));
  $row = $qualityByPool[$key] ?? $qualityByPool[strtolower((string)($pool['nombre'] ?? ''))] ?? null;
  $cl = $row ? (float)($row['cloro_ppm'] ?? 0) : 0;
  $ph = $row ? (float)($row['ph'] ?? 0) : 0;
  $temp = $row ? (float)($row['temperatura_c'] ?? 0) : 0;
  $ok = true;
  $waterRows[] = ['pool'=>$pool,'quality'=>$row,'cl'=>$cl,'ph'=>$ph,'temp'=>$temp,'ok'=>$ok];
}
$waterOk = count(array_filter($waterRows, fn($x) => (bool)$x['quality']));
$nextJob = $schedule[0] ?? null;
$nextJobText = $nextJob ? rep22_pool_label((string)($nextJob['alberca'] ?? 'Alberca')).' · '.rep22_time($nextJob['hora_inicio'] ?? null) : 'Sin agenda';

$poolChart = array_map(fn($x) => ['n'=>rep22_pool_label((string)($x['nombre'] ?? 'Alberca')), 'o'=>max(0,(int)($x['ocupacion_actual'] ?? 0)), 'c'=>(int)($x['capacidad_maxima'] ?? 0)], $p);
$priorityChart = array_map(fn($x) => ['n'=>(string)($x['prioridad'] ?? 'Prioridad'), 'v'=>(int)($x['total'] ?? 0)], $ticketPriority);
$roleChart = array_map(fn($x) => ['n'=>(string)($x['rol'] ?? 'Rol'), 'v'=>(int)($x['total'] ?? 0)], $roles);

$kpis = [
  ['label'=>'Ocupación','value'=>$occupancyPct.'%','sub'=>$totalOccupancy.'/'.$totalCapacity.' personas','icon'=>'fa-chart-pie','tone'=>'aqua'],
  ['label'=>'Lecturas agua','value'=>$waterOk.'/'.count($waterRows),'sub'=>'con registro válido','icon'=>'fa-flask-vial','tone'=>$waterOk===count($waterRows)?'mint':'coral'],
  ['label'=>'Tickets','value'=>$openTickets,'sub'=>$criticalTickets.' críticos/alta','icon'=>'fa-ticket','tone'=>$criticalTickets>0?'coral':'blue'],
  ['label'=>'Limpieza','value'=>$cleanPct.'%','sub'=>$cleanPending.' pendientes','icon'=>'fa-broom','tone'=>$cleanPending>0?'amber':'mint'],
  ['label'=>'Mantto.','value'=>(int)($mantMetrics['hoy'] ?? 0),'sub'=>$equipOkPct.'% salud técnica','icon'=>'fa-screwdriver-wrench','tone'=>$equipRisk>0?'amber':'violet'],
];
?>

<div class="reports-saas-page reports-v22">
  <section class="reports-kpi-row">
    <?php foreach ($kpis as $k): ?>
      <article class="reports-kpi reports-kpi-<?= e($k['tone']) ?>">
        <i class="fa-solid <?= e($k['icon']) ?>"></i>
        <div>
          <span><?= e($k['label']) ?></span>
          <b><?= e((string)$k['value']) ?></b>
          <small><?= e((string)$k['sub']) ?></small>
        </div>
      </article>
    <?php endforeach; ?>
  </section>

  <section class="reports-main-grid">
    <article class="glass-card report-card report-occupancy-card">
      <div class="section-head tight report-headline">
        <div>
          <h3>Reporte de ocupación</h3>
          <span>Capacidad, ocupación y presión por alberca.</span>
        </div>
        <span class="report-pill"><i class="fa-solid fa-clock"></i> 07:00 - 21:00</span>
      </div>
      <div class="report-occupancy-layout">
        <div class="report-pool-bars">
          <?php foreach ($p as $pool):
            $occ = max(0,(int)($pool['ocupacion_actual'] ?? 0));
            $cap = (int)($pool['capacidad_maxima'] ?? 0);
            $pct = rep22_pct($occ, $cap);
            $tone = $pct >= 85 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
          ?>
            <div class="report-pool-row <?= e($tone) ?>">
              <div><b><?= e(rep22_pool_label((string)($pool['nombre'] ?? 'Alberca'))) ?></b><small><?= e($occ.'/'.$cap) ?> personas</small></div>
              <div class="progress report-progress"><span style="width:<?= e((string)$pct) ?>%"></span></div>
              <strong><?= e((string)$pct) ?>%</strong>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="report-chart-box"><canvas id="reportPoolChart" data-pools='<?= e(json_encode($poolChart, JSON_UNESCAPED_UNICODE)) ?>'></canvas></div>
      </div>
    </article>

    <article class="glass-card report-card report-flow-card">
      <div class="section-head tight report-headline">
        <div>
          <h3>Demanda por hora</h3>
          <span>Entradas vs salidas y lectura de flujo neto.</span>
        </div>
        <span class="report-pill strong"><?= e((string)$netFlow) ?> neto</span>
      </div>
      <div class="report-flow-chart"><canvas id="reportFlowChart" data-flow='<?= e(json_encode($flow, JSON_UNESCAPED_UNICODE)) ?>'></canvas></div>
      <div class="report-flow-strip">
        <div><span>Entradas</span><b><?= e((string)$entries) ?></b></div>
        <div><span>Salidas</span><b><?= e((string)$exits) ?></b></div>
        <div><span>Disponible</span><b><?= e((string)max(0,$totalCapacity-$totalOccupancy)) ?></b></div>
      </div>
    </article>

    <article class="glass-card report-card report-water-card">
      <div class="section-head tight report-headline">
        <div>
          <h3>Calidad del agua</h3>
          <span>Cloro, pH y temperatura de las 5 albercas.</span>
        </div>
        <span class="report-pill">RF05</span>
      </div>
      <div class="report-water-grid">
        <?php foreach ($waterRows as $w):
          $pool = $w['pool'];
          $ok = $w['ok'];
          $has = (bool)$w['quality'];
        ?>
          <div class="report-water-tile <?= $has?'ok':'empty' ?>">
            <header><b><?= e(rep22_pool_label((string)($pool['nombre'] ?? 'Alberca'))) ?></b><span><?= $has?'Registrada':'Sin registro' ?></span></header>
            <div><small>CL</small><strong><?= $has ? e((string)$w['cl']) : '--' ?></strong><em style="width:<?= e((string)rep22_pct($w['cl'],3)) ?>%"></em></div>
            <div><small>PH</small><strong><?= $has ? e((string)$w['ph']) : '--' ?></strong><em style="width:<?= e((string)rep22_pct($w['ph'],8)) ?>%"></em></div>
            <div><small>°C</small><strong><?= $has ? e((string)$w['temp']) : '--' ?></strong><em style="width:<?= e((string)rep22_pct($w['temp'],35)) ?>%"></em></div>
          </div>
        <?php endforeach; ?>
      </div>
    </article>

    <article class="glass-card report-card report-ticket-card">
      <div class="section-head tight report-headline">
        <div>
          <h3>Tickets y cumplimiento</h3>
          <span>Estado FIFO, prioridad y tiempos de atención.</span>
        </div>
        <a class="mini-action" href="<?= e(page_url('admin-mantenimiento')) ?>"><i class="fa-solid fa-arrow-up-right-from-square"></i> Ver</a>
      </div>
      <div class="report-ticket-layout">
        <div class="report-priority-bars">
          <?php foreach ($ticketPriority as $r):
            $total = max(0,(int)($r['total'] ?? 0));
            $level = (int)($r['nivel'] ?? 1);
            $tone = $level >= 3 ? 'danger' : ($level === 2 ? 'warning' : 'success');
          ?>
            <div class="priority-line <?= e($tone) ?>">
              <span><?= e($r['prioridad'] ?? 'Prioridad') ?></span>
              <div class="progress micro"><span style="width:<?= e((string)min(100,$total*22)) ?>%"></span></div>
              <b><?= e((string)$total) ?></b>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="report-ticket-table-wrap">
          <table class="report-ticket-table">
            <thead><tr><th>Ticket</th><th>Prioridad</th><th>Espera</th></tr></thead>
            <tbody>
              <?php if (!$tickets): ?><tr><td colspan="3">Sin tickets abiertos</td></tr><?php endif; ?>
              <?php foreach (array_slice($tickets, 0, 4) as $t): ?>
                <tr>
                  <td><b><?= e($t['folio'] ?? 'TK') ?></b><small><?= e(rep22_pool_label((string)($t['alberca'] ?? 'Alberca'))) ?></small></td>
                  <td><span class="report-mini-badge <?= e(rep22_tone((string)($t['prioridad'] ?? 'Media'))) ?>"><?= e($t['prioridad'] ?? 'Media') ?></span></td>
                  <td><?= e(rep22_wait($t['creado_en'] ?? null)) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </article>
  </section>

  <aside class="reports-side-grid">
    <article class="glass-card report-card report-export-card">
      <div class="section-head tight report-headline">
        <div>
          <h3>Centro de reportes</h3>
          <span>Consulta en pantalla de pH/cloro, incidencias, limpieza y mantenimiento.</span>
        </div>
        <i class="side-badge fa-solid fa-file-lines"></i>
      </div>
      <div class="report-readiness">
        <div><b><?= e((string)$occupancyPct) ?>%</b><span>aforo</span></div>
        <div><b><?= e((string)$cleanPct) ?>%</b><span>limpieza</span></div>
        <div><b><?= e((string)$equipOkPct) ?>%</b><span>técnico</span></div>
      </div>
    </article>

    <article class="glass-card report-card report-insight-card">
      <div class="section-head tight report-headline">
        <div>
          <h3>Indicadores operativos</h3>
          <span>Lectura rápida para toma de decisión.</span>
        </div>
        <span class="mini-counter"><?= e((string)count($alerts)) ?></span>
      </div>
      <div class="report-insight-list">
        <div class="insight-line"><i class="fa-solid fa-user-clock"></i><div><span>Usuarios activos</span><b><?= e((string)($userMetrics['activos'] ?? 0)) ?>/<?= e((string)($userMetrics['total'] ?? 0)) ?></b></div></div>
        <div class="insight-line"><i class="fa-solid fa-calendar-check"></i><div><span>Siguiente mantto.</span><b><?= e($nextJobText) ?></b></div></div>
        <div class="insight-line"><i class="fa-solid fa-bell"></i><div><span>Alertas abiertas</span><b><?= e((string)count($alerts)) ?> activas</b></div></div>
        <div class="insight-line"><i class="fa-solid fa-shield-halved"></i><div><span>RN04 / FIFO</span><b>12h cierre automático</b></div></div>
      </div>
      <div class="report-role-chart"><canvas id="reportRoleChart" data-roles='<?= e(json_encode($roleChart, JSON_UNESCAPED_UNICODE)) ?>'></canvas></div>
    </article>

    <article class="glass-card report-card report-alert-card">
      <div class="section-head tight report-headline">
        <div>
          <h3>Alertas recientes</h3>
          <span>Riesgos visibles para cierre de turno.</span>
        </div>
      </div>
      <div class="report-alert-list">
        <?php if (!$alerts): ?><div class="empty-state compact"><i class="fa-solid fa-circle-check"></i><b>Sin alertas</b></div><?php endif; ?>
        <?php foreach (array_slice($alerts, 0, 3) as $a): ?>
          <div class="report-alert-line <?= e(rep22_tone((string)($a['nivel'] ?? 'media'))) ?>">
            <i class="fa-solid fa-bell"></i>
            <div><b><?= e($a['titulo'] ?? 'Alerta') ?></b><small><?= e(rep22_pool_label((string)($a['alberca'] ?? 'Alberca')).' · '.($a['nivel'] ?? 'media')) ?></small></div>
          </div>
        <?php endforeach; ?>
      </div>
    </article>
  </aside>
</div>

<script>
(function(){
  if(!window.Chart) return;
  const css = getComputedStyle(document.documentElement);
  const lagoon = css.getPropertyValue('--lagoon').trim() || '#00B8A9';
  const sky = css.getPropertyValue('--sky').trim() || '#38BDF8';
  const coral = css.getPropertyValue('--coral').trim() || '#FF6B6B';
  const violet = css.getPropertyValue('--violet').trim() || '#7C3AED';
  const mutedGrid = 'rgba(18,56,95,.10)';
  const textColor = '#6B7A90';

  const pool = document.getElementById('reportPoolChart');
  if(pool){
    const d = JSON.parse(pool.dataset.pools || '[]');
    new Chart(pool,{type:'bar',data:{labels:d.map(x=>x.n),datasets:[{label:'Ocupación',data:d.map(x=>x.o),borderRadius:10,backgroundColor:lagoon},{label:'Capacidad',data:d.map(x=>x.c),borderRadius:10,backgroundColor:'rgba(56,189,248,.18)'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{color:textColor,font:{size:10,weight:'700'}}},y:{beginAtZero:true,grid:{color:mutedGrid},ticks:{color:textColor,font:{size:10,weight:'700'}}}}});
  }

  const flow = document.getElementById('reportFlowChart');
  if(flow){
    const d = JSON.parse(flow.dataset.flow || '{}');
    new Chart(flow,{type:'line',data:{labels:d.labels||[],datasets:[{label:'Entradas',data:d.entradas||[],borderColor:lagoon,backgroundColor:'rgba(0,184,169,.14)',fill:true,tension:.38,borderWidth:3,pointRadius:0},{label:'Salidas',data:d.salidas||[],borderColor:coral,backgroundColor:'rgba(255,107,107,.08)',fill:false,tension:.38,borderWidth:3,pointRadius:0}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{color:textColor,font:{size:10,weight:'700'}}},y:{beginAtZero:true,grid:{color:mutedGrid},ticks:{color:textColor,font:{size:10,weight:'700'}}}}});
  }

  const roles = document.getElementById('reportRoleChart');
  if(roles){
    const d = JSON.parse(roles.dataset.roles || '[]');
    new Chart(roles,{type:'doughnut',data:{labels:d.map(x=>x.n),datasets:[{data:d.map(x=>x.v),backgroundColor:[lagoon,sky,violet,coral,'#FFD166'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,cutout:'72%',plugins:{legend:{display:false}}}});
  }
})();
</script>
