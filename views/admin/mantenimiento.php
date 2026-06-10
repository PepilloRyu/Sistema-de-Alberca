<?php
$schedule = $schedule ?? [];
$types = $types ?? [];
$pools = $pools ?? [];
$tecnicos = $tecnicos ?? [];
$metrics = $metrics ?? ['total'=>0,'hoy'=>0,'activos'=>0];
$equipment = $equipment ?? [];
$tickets = $tickets ?? [];

$today = date('Y-m-d');
$nowTs = time();
$todayJobs = array_values(array_filter($schedule, fn($m) => ($m['fecha_programada'] ?? '') === $today));
$activeJobs = array_values(array_filter($schedule, function($m) use ($today, $nowTs) {
  $date = $m['fecha_programada'] ?? '';
  $start = strtotime(($m['fecha_programada'] ?? $today).' '.($m['hora_inicio'] ?? '00:00:00'));
  $end = strtotime(($m['fecha_programada'] ?? $today).' '.($m['hora_fin'] ?? '23:59:59'));
  return $date === $today && $start <= $nowTs && $end >= $nowTs && in_array($m['estado'] ?? 'programado', ['programado','en_proceso'], true);
}));
$criticalTickets = count(array_filter($tickets, fn($t) => (int)($t['prioridad_nivel'] ?? (($t['prioridad'] ?? '') === 'Alta' ? 3 : 0)) >= 3));
$openTickets = count($tickets);
$equipmentTotal = count($equipment);
$equipmentAttention = count(array_filter($equipment, fn($e) => in_array($e['estado'] ?? '', ['revision','critico','fuera_servicio'], true)));
$equipmentCritical = count(array_filter($equipment, fn($e) => in_array($e['estado'] ?? '', ['critico','fuera_servicio'], true)));
$preventivos = count(array_filter($schedule, fn($m) => stripos((string)($m['tipo'] ?? ''), 'prevent') !== false));
$correctivos = count(array_filter($schedule, fn($m) => stripos((string)($m['tipo'] ?? ''), 'correct') !== false || stripos((string)($m['tipo'] ?? ''), 'emerg') !== false));
$techCount = count($tecnicos);
$healthPct = $equipmentTotal > 0 ? max(0, min(100, (int)round((($equipmentTotal - $equipmentAttention) / $equipmentTotal) * 100))) : 100;

$techLoad = [];
foreach ($tecnicos as $t) $techLoad[(string)$t['nombre']] = 0;
foreach ($schedule as $job) {
  $tech = (string)($job['tecnico'] ?? 'Sin técnico');
  if (!isset($techLoad[$tech])) $techLoad[$tech] = 0;
  $techLoad[$tech]++;
}
arsort($techLoad);

if (!function_exists('maint21_pool_label')) {
  function maint21_pool_label(string $name): string {
    $name = trim(str_ireplace('Alberca ', '', $name));
    return $name !== '' ? $name : 'alberca';
  }
  function maint21_time(?string $time): string {
    return $time ? substr($time, 0, 5) : '--:--';
  }
  function maint21_date(?string $date): string {
    if (!$date) return '--/--';
    $ts = strtotime($date);
    return $ts ? date('d/m', $ts) : '--/--';
  }
  function maint21_day(string $date): string {
    $days = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    $ts = strtotime($date);
    return $ts ? $days[(int)date('w',$ts)] : 'Día';
  }
  function maint21_wait(?string $datetime): string {
    if (!$datetime) return 'Sin fecha';
    $diff = max(0, time() - strtotime($datetime));
    if ($diff < 3600) return max(1, (int)floor($diff/60)).' min';
    if ($diff < 86400) return (int)floor($diff/3600).' h';
    return (int)floor($diff/86400).' d';
  }
  function maint21_tone(string $value): string {
    $s = mb_strtolower($value);
    if (str_contains($s, 'critico') || str_contains($s, 'fuera') || str_contains($s, 'emerg') || str_contains($s, 'alta')) return 'danger';
    if (str_contains($s, 'revision') || str_contains($s, 'proceso') || str_contains($s, 'media')) return 'warning';
    if (str_contains($s, 'program') || str_contains($s, 'asign')) return 'info';
    return 'success';
  }
  function maint21_priority_rank(array $ticket): int {
    if (isset($ticket['prioridad_nivel'])) return (int)$ticket['prioridad_nivel'];
    $p = mb_strtolower((string)($ticket['prioridad'] ?? 'media'));
    return str_contains($p,'alta') ? 3 : (str_contains($p,'baja') ? 1 : 2);
  }
}

$calendar = [];
for ($i=0; $i<7; $i++) {
  $date = date('Y-m-d', strtotime($today." +{$i} day"));
  $calendar[$date] = [];
}
foreach ($schedule as $job) {
  $date = (string)($job['fecha_programada'] ?? '');
  if (isset($calendar[$date])) $calendar[$date][] = $job;
}

$fifoTickets = $tickets;
usort($fifoTickets, function($a,$b){
  $priority = maint21_priority_rank($b) <=> maint21_priority_rank($a);
  if ($priority !== 0) return $priority;
  return strtotime($a['creado_en'] ?? 'now') <=> strtotime($b['creado_en'] ?? 'now');
});

$nextJob = $schedule[0] ?? null;
$nextWindow = $nextJob ? maint21_date($nextJob['fecha_programada'] ?? null).' · '.maint21_time($nextJob['hora_inicio'] ?? null) : 'Sin agenda';
$oldestTicket = $fifoTickets[0] ?? null;
$oldestWait = $oldestTicket ? maint21_wait($oldestTicket['creado_en'] ?? null) : '0 min';

$kpis = [
  ['label'=>'Mantto. hoy','value'=>count($todayJobs),'sub'=>count($activeJobs).' activos ahora','icon'=>'fa-calendar-day','tone'=>'aqua'],
  ['label'=>'Tickets FIFO','value'=>$openTickets,'sub'=>$criticalTickets.' alta prioridad','icon'=>'fa-list-check','tone'=>$criticalTickets>0?'coral':'violet'],
  ['label'=>'Equipos','value'=>$equipmentTotal,'sub'=>$equipmentAttention.' en atención','icon'=>'fa-gears','tone'=>$equipmentCritical>0?'coral':'blue'],
  ['label'=>'Preventivos','value'=>$preventivos,'sub'=>'próximos trabajos','icon'=>'fa-shield-heart','tone'=>'mint'],
  ['label'=>'Técnicos','value'=>$techCount,'sub'=>'disponibles','icon'=>'fa-user-gear','tone'=>'amber'],
];
?>

<div class="maint-saas-page maint-saas-v21">
  <section class="maint-kpi-row maint-kpi-row-v21">
    <?php foreach ($kpis as $k): ?>
      <article class="maint-kpi maint-kpi-<?= e($k['tone']) ?>">
        <i class="fa-solid <?= e($k['icon']) ?>"></i>
        <div>
          <span><?= e($k['label']) ?></span>
          <b><?= e((string)$k['value']) ?></b>
          <small><?= e($k['sub']) ?></small>
        </div>
      </article>
    <?php endforeach; ?>
  </section>

  <section class="maint-main-grid maint-main-grid-v21">
    <article class="glass-card maint-agenda-card maint-agenda-v21">
      <div class="section-head tight maint-headline">
        <div>
          <h3>Agenda técnica</h3>
          <span>Calendario semanal con ventanas preventivas y correctivas.</span>
        </div>
        <span class="mini-counter"><?= e((string)count($schedule)) ?></span>
      </div>

      <div class="maint-calendar-board">
        <?php foreach ($calendar as $date => $events): ?>
          <div class="maint-day-card <?= $date === $today ? 'today' : '' ?> <?= $events ? 'has-event' : '' ?>">
            <header>
              <strong><?= e(maint21_day($date)) ?></strong>
              <span><?= e(maint21_date($date)) ?></span>
            </header>
            <div class="day-events">
              <?php if (!$events): ?>
                <em>Libre</em>
              <?php endif; ?>
              <?php foreach (array_slice($events, 0, 2) as $event):
                $tone = maint21_tone((string)($event['tipo'] ?? $event['estado'] ?? 'programado'));
              ?>
                <div class="day-chip <?= e($tone) ?>">
                  <b><?= e(maint21_time($event['hora_inicio'] ?? null)) ?></b>
                  <span><?= e($event['tipo'] ?? 'Mantto.') ?></span>
                  <small><?= e(maint21_pool_label((string)($event['alberca'] ?? 'Alberca'))) ?></small>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="agenda-insights">
        <div><i class="fa-solid fa-clock"></i><span>Siguiente ventana</span><b><?= e($nextWindow) ?></b></div>
        <div><i class="fa-solid fa-shield-halved"></i><span>Preventivos</span><b><?= e((string)$preventivos) ?> activos</b></div>
        <div><i class="fa-solid fa-triangle-exclamation"></i><span>Correctivos</span><b><?= e((string)$correctivos) ?> en cola</b></div>
      </div>
    </article>

    <article class="glass-card maint-fifo-card maint-fifo-v21">
      <div class="section-head tight maint-headline">
        <div>
          <h3>Cola FIFO de tickets</h3>
          <span>Prioridad primero, después orden real de llegada.</span>
        </div>
        <a class="mini-action" href="<?= e(page_url('mantenimiento-tickets')) ?>"><i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir</a>
      </div>

      <div class="fifo-table-v21-wrap">
        <table class="fifo-table fifo-table-v21">
          <thead>
            <tr>
              <th>Orden</th>
              <th>Ticket</th>
              <th>Alberca</th>
              <th>Prioridad</th>
              <th>Estado</th>
              <th>Espera</th>
              <th>Técnico</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$fifoTickets): ?>
              <tr><td colspan="7" class="fifo-empty">Sin tickets abiertos</td></tr>
            <?php endif; ?>
            <?php foreach (array_slice($fifoTickets, 0, 5) as $idx => $t):
              $prio = (string)($t['prioridad'] ?? 'Media');
              $tone = maint21_tone($prio);
            ?>
              <tr>
                <td><span class="fifo-index"><?= e((string)($idx+1)) ?></span></td>
                <td><b><?= e($t['folio'] ?? 'TK') ?></b><small><?= e($t['tipo'] ?? 'Incidencia') ?></small></td>
                <td><?= e(maint21_pool_label((string)($t['alberca'] ?? 'Alberca'))) ?></td>
                <td><span class="fifo-pill <?= e($tone) ?>"><?= e($prio) ?></span></td>
                <td><?= e($t['estado'] ?? 'Nuevo') ?></td>
                <td><strong><?= e(maint21_wait($t['creado_en'] ?? null)) ?></strong></td>
                <td><?= e($t['tecnico'] ?? 'Sin asignar') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="fifo-summary-strip">
        <div><span>Primero en atender</span><b><?= e($oldestTicket['folio'] ?? 'Sin tickets') ?></b></div>
        <div><span>Espera mayor</span><b><?= e($oldestWait) ?></b></div>
        <div><span>Regla activa</span><b>FIFO + prioridad</b></div>
      </div>
    </article>

    <article class="glass-card maint-equipment-card maint-equipment-v21">
      <div class="section-head tight maint-headline">
        <div>
          <h3>Equipos críticos por alberca</h3>
          <span>Bombas, filtros, dosificadores, revisión y estado operativo.</span>
        </div>
        <a class="mini-action" href="<?= e(page_url('mantenimiento-equipos')) ?>"><i class="fa-solid fa-screwdriver-wrench"></i> Equipos</a>
      </div>
      <div class="equipment-board-v21">
        <?php foreach (array_slice($equipment, 0, 6) as $eq):
          $status = (string)($eq['estado'] ?? 'operativo');
          $tone = maint21_tone($status);
        ?>
          <div class="equipment-tile <?= e($tone) ?>">
            <div class="eq-icon"><i class="fa-solid fa-gear"></i></div>
            <div class="eq-main">
              <b><?= e($eq['nombre'] ?? 'Equipo') ?></b>
              <small><?= e(($eq['tipo'] ?? 'Equipo').' · '.maint21_pool_label((string)($eq['alberca'] ?? 'Alberca'))) ?></small>
            </div>
            <span><?= e(str_replace('_',' ', $status)) ?></span>
            <em>Próxima <?= e(maint21_date($eq['proxima_revision'] ?? null)) ?></em>
          </div>
        <?php endforeach; ?>
      </div>
    </article>
  </section>

  <aside class="maint-side-grid maint-side-grid-v21">
    <article class="glass-card maint-form-card maint-form-v21-card">
      <div class="section-head tight maint-headline">
        <div>
          <h3>Programar mantenimiento</h3>
          <span>RF08 · técnico, tipo, fecha y ventana.</span>
        </div>
        <i class="side-badge fa-solid fa-calendar-plus"></i>
      </div>
      <form method="post" class="maint-form-v21">
        <?= csrf_field() ?>
        <label class="form-line full"><span>Alberca</span><select name="idAlberca" class="form-select" required><?php foreach($pools as $p): ?><option value="<?= e($p['idAlberca']) ?>"><?= e($p['nombre']) ?></option><?php endforeach; ?></select></label>
        <label class="form-line"><span>Tipo</span><select name="idTipoMantenimiento" class="form-select" required><?php foreach($types as $t): ?><option value="<?= e($t['idTipoMantenimiento']) ?>"><?= e($t['nombre']) ?></option><?php endforeach; ?></select></label>
        <label class="form-line"><span>Técnico</span><select name="asignado_a" class="form-select" required><?php foreach($tecnicos as $u): ?><option value="<?= e($u['idUsuario']) ?>"><?= e($u['nombre']) ?></option><?php endforeach; ?></select></label>
        <label class="form-line full"><span>Fecha</span><input type="date" name="fecha_programada" class="form-control" value="<?= e(date('Y-m-d')) ?>" required></label>
        <label class="form-line"><span>Inicio</span><input type="time" name="hora_inicio" class="form-control" value="08:00" required></label>
        <label class="form-line"><span>Fin</span><input type="time" name="hora_fin" class="form-control" value="09:30" required></label>
        <label class="form-line full"><span>Descripción</span><textarea name="descripcion" class="form-control" rows="2" placeholder="Equipo, falla o actividad preventiva..."></textarea></label>
        <button class="btn btn-aqua w-100"><i class="fa-solid fa-floppy-disk me-1"></i> Programar mantenimiento</button>
      </form>
    </article>

    <article class="glass-card maint-health-card maint-health-v21">
      <div class="section-head tight maint-headline">
        <div>
          <h3>Salud técnica</h3>
          <span>Equipos y carga del técnico.</span>
        </div>
        <span class="mini-counter"><?= e((string)$healthPct) ?>%</span>
      </div>
      <div class="maint-health-layout-v21">
        <div class="maint-donut-wrap">
          <canvas id="maintenanceHealthChart" aria-label="Salud técnica"></canvas>
          <div class="donut-center"><b><?= e((string)$healthPct) ?>%</b><span>ok</span></div>
        </div>
        <div class="maint-tech-list-v21">
          <?php foreach (array_slice($techLoad, 0, 4, true) as $tech => $load):
            $pct = max(8, min(100, (int)round(($load / max(1, count($schedule))) * 100)));
          ?>
            <div class="tech-load-line-v21">
              <div><b><?= e($tech) ?></b><small><?= e((string)$load) ?> trabajo<?= $load==1?'':'s' ?></small></div>
              <div class="progress micro"><span style="width:<?= e((string)$pct) ?>%"></span></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </article>
  </aside>
</div>

<script>
(() => {
  const el = document.getElementById('maintenanceHealthChart');
  if (!el || typeof Chart === 'undefined') return;
  new Chart(el, {
    type: 'doughnut',
    data: {
      labels: ['Operativos','Atención','Críticos'],
      datasets: [{
        data: [<?= (int)max(0,$equipmentTotal-$equipmentAttention) ?>, <?= (int)max(0,$equipmentAttention-$equipmentCritical) ?>, <?= (int)$equipmentCritical ?>],
        backgroundColor: ['#00B8A9','#FFD166','#FF6B6B'],
        borderWidth: 0,
        cutout: '70%'
      }]
    },
    options: {responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}, tooltip:{enabled:true}}}
  });
})();
</script>
