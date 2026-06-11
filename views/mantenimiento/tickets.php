<?php
$tickets = $tickets ?? [];
$cats = $cats ?? ['estados'=>[],'prioridades'=>[],'tipos'=>[]];
$activeTickets = array_values($tickets);
$nextTicket = $activeTickets[0] ?? null;
$openCount = count($activeTickets);
$criticalCount = 0;
$unassignedCount = 0;
$assignedMine = 0;
$maxWait = 0;
$statusCounts = [];
foreach (($cats['estados'] ?? []) as $estadoCat) {
  $name = (string)($estadoCat['nombre'] ?? '');
  if ($name !== '' && (int)($estadoCat['es_final'] ?? 0) === 0) $statusCounts[$name] = 0;
}
$priorityCounts = [];
foreach (($cats['prioridades'] ?? []) as $prioridadCat) {
  $name = (string)($prioridadCat['nombre'] ?? '');
  if ($name !== '') $priorityCounts[$name] = 0;
}
$poolCounts = [];
foreach ($activeTickets as $ticket) {
  $nivel = (int)($ticket['prioridad_nivel'] ?? 0);
  $priorityName = (string)($ticket['prioridad'] ?? 'Sin prioridad');
  $statusName = (string)($ticket['estado'] ?? 'Sin estado');
  $poolName = (string)($ticket['alberca'] ?? 'Sin alberca');
  if ($nivel >= 3 || in_array(mb_strtolower($priorityName), ['alta','crítica','critica'], true)) $criticalCount++;
  if (empty($ticket['asignado_a']) || ($ticket['tecnico'] ?? '') === 'Sin asignar') $unassignedCount++;
  else $assignedMine++;
  $wait = !empty($ticket['creado_en']) ? max(1, (int)floor((time() - strtotime((string)$ticket['creado_en'])) / 60)) : 0;
  $maxWait = max($maxWait, $wait);
  $statusCounts[$statusName] = ($statusCounts[$statusName] ?? 0) + 1;
  $priorityCounts[$priorityName] = ($priorityCounts[$priorityName] ?? 0) + 1;
  $poolCounts[$poolName] = ($poolCounts[$poolName] ?? 0) + 1;
}
$oldestLabel = $maxWait >= 60 ? floor($maxWait/60).'h '.($maxWait%60).'m' : $maxWait.'m';
$recommended = $nextTicket
  ? 'Atender '.$nextTicket['folio'].' primero: prioridad '.$nextTicket['prioridad'].' en '.$nextTicket['alberca'].'.'
  : 'Cola limpia. Mantén monitoreo y revisiones preventivas.';
$estadoOptions = $cats['estados'] ?? [];
?>
<div class="mant-tickets-v37">
  <section class="mant-ticket-kpis-v37">
    <article class="mant-ticket-kpi-v37 danger">
      <i class="fa-solid fa-ticket"></i>
      <div><span>Tickets activos</span><b><?= e($openCount) ?></b><small>Cola visible FIFO</small></div>
    </article>
    <article class="mant-ticket-kpi-v37 warning">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <div><span>Alta / crítica</span><b><?= e($criticalCount) ?></b><small>Prioridad operativa</small></div>
    </article>
    <article class="mant-ticket-kpi-v37 aqua">
      <i class="fa-solid fa-user-plus"></i>
      <div><span>Sin asignar</span><b><?= e($unassignedCount) ?></b><small>Listos para tomar</small></div>
    </article>
    <article class="mant-ticket-kpi-v37 violet">
      <i class="fa-solid fa-user-gear"></i>
      <div><span>Asignados</span><b><?= e($assignedMine) ?></b><small>En seguimiento</small></div>
    </article>
    <article class="mant-ticket-kpi-v37 ocean">
      <i class="fa-solid fa-hourglass-half"></i>
      <div><span>Mayor espera</span><b><?= e($oldestLabel) ?></b><small>RN04: 12 horas</small></div>
    </article>
  </section>

  <section class="glass-card mant-ticket-board-v37">
    <div class="mant-ticket-head-v37">
      <div>
        <span>Atención FIFO</span>
        <h3>Tickets de mantenimiento</h3>
      </div>
      <div class="mant-ticket-tools-v37">
        <div class="mant-search-v37"><i class="fa-solid fa-magnifying-glass"></i><input id="mantTicketSearchV37" type="search" placeholder="Buscar folio, alberca, tipo o técnico"></div>
        <select id="mantTicketFilterV37" class="mant-filter-v37">
          <option value="">Todas las prioridades</option>
          <option value="critica">Crítica</option>
          <option value="alta">Alta</option>
          <option value="media">Media</option>
          <option value="baja">Baja</option>
        </select>
      </div>
    </div>

    <div class="mant-ticket-leader-v37">
      <div class="mant-leader-copy-v37">
        <span>Primero en atender</span>
        <?php if($nextTicket): ?>
          <b><?= e($nextTicket['folio'] ?? 'TK') ?></b>
          <small><?= e($recommended) ?></small>
        <?php else: ?>
          <b>Sin pendientes</b>
          <small><?= e($recommended) ?></small>
        <?php endif; ?>
      </div>
      <div class="mant-rule-v37"><i class="fa-solid fa-arrow-down-short-wide"></i><span>Orden: prioridad alta primero, después llegada más antigua.</span></div>
    </div>

    <div class="mant-ticket-table-box-v37">
      <table class="mant-ticket-table-v37 mant-ticket-page-table-v37">
        <thead>
          <tr><th># FIFO</th><th>Ticket</th><th>Origen</th><th>Prioridad</th><th>Estado</th><th>Espera</th><th>Técnico</th><th>Tomar</th></tr>
        </thead>
        <tbody>
        <?php foreach($activeTickets as $i=>$ticket):
          $wait = !empty($ticket['creado_en']) ? max(1, (int)floor((time() - strtotime((string)$ticket['creado_en'])) / 60)) : 0;
          $waitLabel = $wait >= 60 ? floor($wait/60).'h '.($wait%60).'m' : $wait.'m';
          $prioSlug = mb_strtolower((string)($ticket['prioridad'] ?? ''));
          $searchText = trim(($ticket['folio'] ?? '').' '.($ticket['alberca'] ?? '').' '.($ticket['tipo'] ?? '').' '.($ticket['estado'] ?? '').' '.($ticket['tecnico'] ?? '').' '.($ticket['descripcion'] ?? ''));
        ?>
          <tr class="mant-ticket-row-v37" data-priority="<?= e($prioSlug) ?>" data-search="<?= e(mb_strtolower($searchText)) ?>">
            <td><em><?= e($i+1) ?></em></td>
            <td class="mant-ticket-id-v37"><b><?= e($ticket['folio'] ?? 'TK') ?></b><small><?= e(mb_strimwidth((string)($ticket['descripcion'] ?? ''),0,58,'...','UTF-8')) ?></small></td>
            <td><b><?= e($ticket['alberca'] ?? '') ?></b><small><?= e($ticket['tipo'] ?? '') ?></small></td>
            <td><span class="mant-pill-v36 <?= e(sev($ticket['prioridad'] ?? '')) ?>"><?= e($ticket['prioridad'] ?? '') ?></span></td>
            <td><span class="mant-state-v37"><?= e($ticket['estado'] ?? '') ?></span></td>
            <td><strong><?= e($waitLabel) ?></strong><small><?= e(fdt($ticket['creado_en'] ?? null)) ?></small></td>
            <td><b><?= e($ticket['tecnico'] ?? 'Sin asignar') ?></b><small><?= empty($ticket['asignado_a']) ? 'Disponible' : 'Asignado' ?></small></td>
            <td>
              <form method="POST" action="<?= e(page_url('mantenimiento-tickets')) ?>" class="mant-take-form-v37">
                <?= csrf_field() ?>
                <input type="hidden" name="idTicket" value="<?= e($ticket['idTicket'] ?? 0) ?>">
                <button class="mant-take-btn-v37" name="action" value="assign" title="Tomar ticket"><i class="fa-solid fa-hand"></i><span>Tomar</span></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$activeTickets): ?>
          <tr><td colspan="8"><div class="mant-empty-v37"><i class="fa-solid fa-circle-check"></i><b>Sin tickets activos</b><small>La cola FIFO está limpia en este momento.</small></div></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <aside class="mant-ticket-side-v37">
    <section class="glass-card mant-follow-card-v37">
      <div class="mant-ticket-head-v37 mini"><div><span>Seguimiento</span><h3>Actualizar ticket</h3></div></div>
      <form method="POST" action="<?= e(page_url('mantenimiento-tickets')) ?>" class="mant-follow-form-v37">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="follow">
        <label>Ticket</label>
        <select name="idTicket" required>
          <?php foreach($activeTickets as $ticket): ?><option value="<?= e($ticket['idTicket'] ?? 0) ?>"><?= e(($ticket['folio'] ?? 'TK').' · '.($ticket['alberca'] ?? '')) ?></option><?php endforeach; ?>
          <?php if(!$activeTickets): ?><option value="0">Sin tickets activos</option><?php endif; ?>
        </select>
        <label>Nuevo estado</label>
        <select name="idEstadoTicket" required>
          <?php foreach($estadoOptions as $estado): ?>
            <option value="<?= e($estado['idEstadoTicket']) ?>"><?= e($estado['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
        <label>Seguimiento técnico</label>
        <textarea name="comentario" rows="3" placeholder="Describe diagnóstico, acción realizada o siguiente paso." required></textarea>
        <button class="mant-submit-v37"><i class="fa-solid fa-floppy-disk"></i> Guardar seguimiento</button>
      </form>
    </section>

    <section class="glass-card mant-chart-card-v37 tickets-page">
      <div class="mant-ticket-head-v37 mini"><div><span>Salud de cola</span><h3>Estados</h3></div></div>
      <div class="mant-ticket-chart-v37"><canvas id="mantTicketStatusChartV37"></canvas></div>
      <div class="mant-status-grid-v37">
        <?php foreach($statusCounts as $status=>$count): ?><div><span><?= e($status) ?></span><b><?= e($count) ?></b></div><?php endforeach; ?>
      </div>
    </section>

    <section class="glass-card mant-protocol-v37">
      <div class="mant-ticket-head-v37 mini"><div><span>Regla operativa</span><h3>Protocolo FIFO</h3></div></div>
      <ol>
        <li><b>Tomar</b><span>asignar el primer ticket disponible.</span></li>
        <li><b>Diagnosticar</b><span>registrar seguimiento antes de 12 horas.</span></li>
        <li><b>Cerrar</b><span>solo con reparación o causa documentada.</span></li>
      </ol>
    </section>
  </aside>

  <section class="glass-card mant-bottom-v37">
    <div class="mant-ticket-head-v37 mini"><div><span>Presión operativa</span><h3>Tickets por prioridad y alberca</h3></div></div>
    <div class="mant-bottom-grid-v37">
      <div class="mant-priority-stack-v37">
        <?php $maxPrio=max(array_merge([1], array_values($priorityCounts))); foreach($priorityCounts as $priority=>$count): $pct=(int)round(($count/$maxPrio)*100); ?>
          <div><span><?= e($priority) ?></span><i><em style="width:<?= e((string)max(4,$pct)) ?>%"></em></i><b><?= e($count) ?></b></div>
        <?php endforeach; ?>
      </div>
      <div class="mant-pool-stack-v37">
        <?php $maxPool=max(array_merge([1], array_values($poolCounts))); foreach($poolCounts as $pool=>$count): $pct=(int)round(($count/$maxPool)*100); ?>
          <div><span><?= e($pool) ?></span><i><em style="width:<?= e((string)max(4,$pct)) ?>%"></em></i><b><?= e($count) ?></b></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
  const search=document.getElementById('mantTicketSearchV37');
  const filter=document.getElementById('mantTicketFilterV37');
  const rows=[...document.querySelectorAll('.mant-ticket-row-v37')];
  function norm(v){return (v||'').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');}
  function apply(){
    const q=norm(search?.value||'');
    const f=norm(filter?.value||'');
    rows.forEach(row=>{
      const hay=norm(row.dataset.search||'');
      const pr=norm(row.dataset.priority||'');
      const show=(!q||hay.includes(q)) && (!f||pr.includes(f));
      row.style.display=show?'':'none';
    });
  }
  search&&search.addEventListener('input',apply);
  filter&&filter.addEventListener('change',apply);
  const ctx=document.getElementById('mantTicketStatusChartV37');
  if(ctx && window.Chart){
    new Chart(ctx,{type:'doughnut',data:{labels:<?= json_encode(array_keys($statusCounts), JSON_UNESCAPED_UNICODE) ?>,datasets:[{data:<?= json_encode(array_values($statusCounts)) ?>,borderWidth:0,cutout:'70%'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},animation:{duration:650}}});
  }
});
</script>
