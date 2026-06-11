<?php
$p = is_array($p ?? null) ? $p : [];
$cats = is_array($cats ?? null) ? $cats : ['tipos'=>[], 'prioridades'=>[]];
$tickets = is_array($tickets ?? null) ? $tickets : [];
$queue = is_array($queue ?? null) ? $queue : [];
$check = is_array($check ?? null) ? $check : [];

$poolNames = array_map(fn($pool)=>(string)($pool['nombre'] ?? ''), $pools ?? $p ?? []);
$poolNames = array_values(array_filter($poolNames));
$poolStats = [];
foreach($poolNames as $name){ $poolStats[$name] = ['total'=>0,'critical'=>0,'open'=>0]; }
$open = 0; $critical = 0; $inProcess = 0; $closed = 0; $lastTicket = null;
$priorityCounts = ['Crítica'=>0,'Alta'=>0,'Media'=>0,'Baja'=>0];
foreach($tickets as $t){
    $estado = mb_strtolower((string)($t['estado'] ?? 'Nuevo'));
    $priority = (string)($t['prioridad'] ?? 'Media');
    $pool = (string)($t['alberca'] ?? 'Sin alberca');
    $isFinal = str_contains($estado,'concluido') || str_contains($estado,'cerrado') || str_contains($estado,'cancelado');
    if(!$isFinal){ $open++; } else { $closed++; }
    if(str_contains($estado,'proceso') || str_contains($estado,'asignado')){ $inProcess++; }
    if(in_array(mb_strtolower($priority), ['alta','crítica','critica'], true)){ $critical++; }
    $poolStats[$pool] = $poolStats[$pool] ?? ['total'=>0,'critical'=>0,'open'=>0];
    $poolStats[$pool]['total']++;
    $poolStats[$pool]['critical'] += in_array(mb_strtolower($priority), ['alta','crítica','critica'], true) ? 1 : 0;
    $poolStats[$pool]['open'] += !$isFinal ? 1 : 0;
    $priorityCounts[$priority] = ($priorityCounts[$priority] ?? 0) + 1;
    $lastTicket ??= $t;
}
$totalTickets = count($tickets);
$pendingChecklist = 0;
foreach($check as $task){ if((int)($task['completado'] ?? 0) === 0){ $pendingChecklist++; } }
$firstFifo = $queue[0] ?? null;
$lastLabel = $lastTicket ? fdt((string)($lastTicket['creado_en'] ?? '')) : 'Sin reportes';
$riskLabel = $critical > 0 ? 'Atención prioritaria' : ($open > 0 ? 'Seguimiento activo' : 'Sin bloqueo');
$riskClass = $critical > 0 ? 'danger' : ($open > 0 ? 'warning' : 'success');
$poolLabels = array_keys($poolStats);
$poolTotals = array_map(fn($x)=>(int)$x['total'], array_values($poolStats));
$priorityLabels = array_keys($priorityCounts);
$priorityValues = array_values($priorityCounts);
?>
<div class="clean-inc-saas-v34">
  <section class="clean-inc-kpis">
    <div class="clean-inc-kpi <?= e($riskClass) ?>">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <div><span>Estado de incidencias</span><b><?= e($riskLabel) ?></b><small><?= e((string)$open) ?> abiertas · <?= e((string)$critical) ?> alta/crítica</small></div>
    </div>
    <div class="clean-inc-kpi">
      <i class="fa-solid fa-ticket"></i>
      <div><span>Reportes enviados</span><b><?= e((string)$totalTickets) ?></b><small>Último: <?= e($lastLabel) ?></small></div>
    </div>
    <div class="clean-inc-kpi violet">
      <i class="fa-solid fa-screwdriver-wrench"></i>
      <div><span>En atención técnica</span><b><?= e((string)$inProcess) ?></b><small>Asignado / en proceso</small></div>
    </div>
    <div class="clean-inc-kpi warning">
      <i class="fa-solid fa-broom"></i>
      <div><span>Checklist pendiente</span><b><?= e((string)$pendingChecklist) ?></b><small>Puede generar incidencias</small></div>
    </div>
  </section>

  <section class="glass-card clean-inc-form-card">
    <div class="clean-inc-head">
      <div>
        <h3>Reportar incidencia desde limpieza</h3>
        <span>Registra riesgos higiénicos, obstrucciones, residuos, daño visible o apoyo requerido para mantenimiento.</span>
      </div>
      <span class="clean-inc-pill"><i class="fa-solid fa-shield-halved"></i> FIFO activo</span>
    </div>
    <form class="clean-inc-form" method="POST" action="<?= e(page_url('limpieza-incidencias')) ?>">
      <?= csrf_field() ?>
      <label>
        <span>Alberca / zona</span>
        <select name="idAlberca" class="form-select" required>
          <?php foreach($p as $pool): ?>
            <option value="<?= e($pool['idAlberca'] ?? '') ?>"><?= e($pool['nombre'] ?? 'Alberca') ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        <span>Tipo</span>
        <select name="idTipoIncidencia" class="form-select" required>
          <?php foreach(($cats['tipos'] ?? []) as $x): ?>
            <option value="<?= e($x['idTipoIncidencia'] ?? '') ?>"><?= e($x['nombre'] ?? 'Incidencia') ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        <span>Prioridad</span>
        <select name="idPrioridad" class="form-select" required>
          <?php foreach(($cats['prioridades'] ?? []) as $x): ?>
            <option value="<?= e($x['idPrioridad'] ?? '') ?>"><?= e($x['nombre'] ?? 'Media') ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        <span>Área exacta</span>
        <select name="area_afectada" class="form-select">
          <option value="Perímetro">Perímetro</option>
          <option value="Sanitarios">Sanitarios</option>
          <option value="Regaderas">Regaderas</option>
          <option value="Acceso / pasillo">Acceso / pasillo</option>
          <option value="Cuarto de máquinas">Cuarto de máquinas</option>
          <option value="Área común">Área común</option>
        </select>
      </label>
      <label class="span-2">
        <span>Descripción operativa</span>
        <textarea class="form-control" name="descripcion" required maxlength="500" placeholder="Ejemplo: residuos acumulados cerca de rejilla, piso resbaloso, fuga visible, cristales, mal olor, bomba con ruido o área que requiere cierre preventivo."></textarea>
      </label>
      <label>
        <span>Evidencia / referencia</span>
        <input class="form-control" name="evidencia" maxlength="160" placeholder="Foto, folio interno o referencia">
      </label>
      <button class="btn btn-aqua clean-inc-submit"><i class="fa-solid fa-paper-plane"></i> Enviar a mantenimiento</button>
    </form>
  </section>

  <section class="glass-card clean-inc-status-card">
    <div class="clean-inc-head compact">
      <div><h3>Impacto por alberca</h3><span>Reportes detectados desde limpieza.</span></div>
    </div>
    <div class="clean-pool-impact">
      <?php foreach($poolStats as $pool=>$st): $total=(int)$st['total']; $openPool=(int)$st['open']; $pct=$total?min(100,$total*22):5; $cls=$st['critical']>0?'danger':($openPool>0?'warning':'success'); ?>
        <div class="clean-pool-impact-row <?= e($cls) ?>">
          <div><b><?= e($pool) ?></b><small><?= e((string)$openPool) ?> abiertas · <?= e((string)$st['critical']) ?> críticas/altas</small></div>
          <i><em style="width:<?= e((string)$pct) ?>%"></em></i>
          <strong><?= e((string)$total) ?></strong>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card clean-inc-table-card">
    <div class="clean-inc-head compact">
      <div><h3>Mis incidencias reportadas</h3><span>Seguimiento de reportes creados por el personal de limpieza.</span></div>
      <input class="form-control clean-inc-search" id="cleanIncSearch" placeholder="Buscar folio, alberca, tipo...">
    </div>
    <div class="clean-inc-table-wrap">
      <table class="clean-inc-table" id="cleanIncTable">
        <thead><tr><th>Folio</th><th>Ubicación</th><th>Prioridad</th><th>Estado</th><th>Creado</th><th>Atención</th></tr></thead>
        <tbody>
          <?php foreach($tickets as $t): $prio=(string)($t['prioridad'] ?? 'Media'); $state=(string)($t['estado'] ?? 'Nuevo'); ?>
          <tr>
            <td><b><?= e($t['folio'] ?? 'TK-SIN-FOLIO') ?></b><small><?= e($t['tipo'] ?? 'Incidencia') ?></small></td>
            <td><b><?= e($t['alberca'] ?? 'Sin alberca') ?></b><small><?= e(mb_substr((string)($t['descripcion'] ?? ''),0,72)) ?></small></td>
            <td><span class="badge-soft <?= e(sev($prio)) ?>"><?= e($prio) ?></span></td>
            <td><span class="clean-state <?= e(sev($state)) ?>"><?= e($state) ?></span></td>
            <td><?= e(fdt((string)($t['creado_en'] ?? ''))) ?></td>
            <td><b><?= e($t['tecnico'] ?? 'Sin asignar') ?></b><small><?= !empty($t['ultimo_seguimiento_en']) ? 'Seguimiento: '.e(fdt((string)$t['ultimo_seguimiento_en'])) : 'Sin seguimiento' ?></small></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="glass-card clean-inc-side-card">
    <div class="clean-inc-head compact"><div><h3>Prioridad y protocolo</h3><span>Cuándo reportar y cómo dar seguimiento.</span></div></div>
    <div class="clean-inc-chart"><canvas id="cleanIncPriorityChart"></canvas></div>
    <div class="clean-inc-protocol">
      <div class="danger"><i class="fa-solid fa-ban"></i><div><b>Cerrar paso</b><small>Si existe vidrio, químico, cableado, fuga o piso resbaloso.</small></div></div>
      <div class="warning"><i class="fa-solid fa-camera"></i><div><b>Documentar</b><small>Agregar referencia de evidencia y área exacta.</small></div></div>
      <div><i class="fa-solid fa-arrows-spin"></i><div><b>FIFO técnico</b><small>La cola respeta prioridad y orden de llegada.</small></div></div>
    </div>
  </section>

  <section class="glass-card clean-inc-fifo-card">
    <div class="clean-inc-head compact"><div><h3>FIFO técnico relacionado</h3><span>Primeros tickets abiertos que pueden impactar limpieza.</span></div></div>
    <div class="clean-inc-fifo-list">
      <?php foreach(array_slice($queue,0,4) as $i=>$t): ?>
        <div class="clean-inc-fifo-row">
          <strong>#<?= e((string)($i+1)) ?></strong>
          <div><b><?= e($t['folio'] ?? 'TK') ?></b><small><?= e($t['alberca'] ?? 'Alberca') ?> · <?= e($t['tipo'] ?? 'Incidencia') ?></small></div>
          <span class="badge-soft <?= e(sev((string)($t['prioridad'] ?? 'Media'))) ?>"><?= e($t['prioridad'] ?? 'Media') ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if($firstFifo): ?>
      <div class="clean-inc-next">
        <span>Siguiente en atender</span>
        <b><?= e($firstFifo['folio'] ?? 'TK') ?></b>
        <small><?= e($firstFifo['alberca'] ?? '') ?> · <?= e($firstFifo['tecnico'] ?? 'Sin asignar') ?></small>
      </div>
    <?php endif; ?>
  </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const input = document.getElementById('cleanIncSearch');
  const rows = Array.from(document.querySelectorAll('#cleanIncTable tbody tr'));
  if(input){
    input.addEventListener('input', function(){
      const q = this.value.toLowerCase().trim();
      rows.forEach(row => row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none');
    });
  }
  const priorityCanvas = document.getElementById('cleanIncPriorityChart');
  if(priorityCanvas && window.Chart){
    new Chart(priorityCanvas, {
      type:'doughnut',
      data:{
        labels: <?= json_encode($priorityLabels, JSON_UNESCAPED_UNICODE) ?>,
        datasets:[{data: <?= json_encode($priorityValues, JSON_UNESCAPED_UNICODE) ?>, backgroundColor:['#FF6B6B','#FFD166','#38BDF8','#00B8A9'], borderWidth:0}]
      },
      options:{responsive:true, maintainAspectRatio:false, cutout:'68%', plugins:{legend:{display:false}}}
    });
  }
});
</script>
