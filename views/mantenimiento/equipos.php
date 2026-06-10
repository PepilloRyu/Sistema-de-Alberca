<?php
$equipos = is_array($equipment ?? null) ? $equipment : [];
$today = new DateTimeImmutable('today');
$stateTone = function($estado) {
  $e = mb_strtolower((string)$estado);
  if (str_contains($e,'crit') || str_contains($e,'fuera')) return 'danger';
  if (str_contains($e,'revision') || str_contains($e,'revisión')) return 'warning';
  return 'success';
};
$stateLabel = function($estado) {
  $e = str_replace('_',' ',(string)$estado);
  return ucwords($e);
};
$daysTo = function($date) use ($today) {
  if (!$date) return null;
  try { $d = new DateTimeImmutable((string)$date); return (int)$today->diff($d)->format('%r%a'); }
  catch (Throwable $e) { return null; }
};
$fmtDate = function($date) {
  if (!$date) return 'Sin fecha';
  $t = strtotime((string)$date);
  return $t ? date('d/m/Y',$t) : (string)$date;
};
$total = count($equipos);
$operativos = $revision = $criticos = $fuera = $vencidos = $proximos = 0;
$porTipo = [];
$porAlberca = [];
foreach ($equipos as $eq) {
  $estado = mb_strtolower((string)($eq['estado'] ?? 'operativo'));
  if (str_contains($estado,'operativo')) $operativos++;
  elseif (str_contains($estado,'revision') || str_contains($estado,'revisión')) $revision++;
  elseif (str_contains($estado,'crit')) $criticos++;
  elseif (str_contains($estado,'fuera')) $fuera++;
  $dias = $daysTo($eq['proxima_revision'] ?? null);
  if ($dias !== null && $dias < 0) $vencidos++;
  if ($dias !== null && $dias >= 0 && $dias <= 7) $proximos++;
  $tipo = (string)($eq['tipo'] ?? 'Equipo');
  $porTipo[$tipo] = ($porTipo[$tipo] ?? 0) + 1;
  $pool = (string)($eq['alberca'] ?? 'Sin alberca');
  if (!isset($porAlberca[$pool])) $porAlberca[$pool] = ['total'=>0,'alerta'=>0,'revision'=>0,'ok'=>0];
  $porAlberca[$pool]['total']++;
  if (str_contains($estado,'crit') || str_contains($estado,'fuera')) $porAlberca[$pool]['alerta']++;
  elseif (str_contains($estado,'revision') || str_contains($estado,'revisión')) $porAlberca[$pool]['revision']++;
  else $porAlberca[$pool]['ok']++;
}
$salud = $total ? (int)round(($operativos / $total) * 100) : 0;
$alertas = $criticos + $fuera + $vencidos;
$poolValues = array_values(array_map(fn($x)=>(int)($x['total'] ?? 0), $porAlberca ?: [['total'=>1]]));
$poolMax = max(array_merge([1], $poolValues));
$tipoLabels = array_keys($porTipo ?: ['Bomba'=>1,'Filtro'=>1,'Dosificador'=>1]);
$tipoCounts = array_values($porTipo ?: ['Bomba'=>1,'Filtro'=>1,'Dosificador'=>1]);
$stateLabels = ['Operativos','Revisión','Críticos','Fuera'];
$stateCounts = [$operativos,$revision,$criticos,$fuera];
$nextReview = $equipos;
usort($nextReview, function($a,$b) use ($daysTo) { return ($daysTo($a['proxima_revision'] ?? null) ?? 9999) <=> ($daysTo($b['proxima_revision'] ?? null) ?? 9999); });
$next = $nextReview[0] ?? null;
?>
<div class="mant-equipos-v39">
  <section class="mant-eq-kpis-v39">
    <article class="mant-eq-kpi-v39 primary"><i class="fa-solid fa-gears"></i><span>Inventario</span><b><?= (int)$total ?></b><small>equipos registrados</small></article>
    <article class="mant-eq-kpi-v39 success"><i class="fa-solid fa-circle-check"></i><span>Operativos</span><b><?= (int)$operativos ?></b><small><?= (int)$salud ?>% salud técnica</small></article>
    <article class="mant-eq-kpi-v39 warning"><i class="fa-solid fa-screwdriver-wrench"></i><span>En revisión</span><b><?= (int)$revision ?></b><small>requieren seguimiento</small></article>
    <article class="mant-eq-kpi-v39 danger"><i class="fa-solid fa-triangle-exclamation"></i><span>Alertas</span><b><?= (int)$alertas ?></b><small>críticos/vencidos</small></article>
    <article class="mant-eq-kpi-v39 violet"><i class="fa-solid fa-calendar-day"></i><span>Próx. 7 días</span><b><?= (int)$proximos ?></b><small>revisiones próximas</small></article>
  </section>

  <section class="glass-card mant-eq-inventory-v39">
    <div class="mant-eq-head-v39">
      <div><span>Inventario técnico</span><h3>Equipos por alberca y estado operativo</h3></div>
      <label class="mant-eq-search-v39"><i class="fa-solid fa-magnifying-glass"></i><input id="equipmentSearch" type="search" placeholder="Buscar equipo, alberca o tipo"></label>
    </div>
    <div class="mant-eq-table-wrap-v39">
      <table class="mant-eq-table-v39" id="equipmentTable">
        <thead><tr><th>Equipo</th><th>Alberca</th><th>Tipo</th><th>Estado</th><th>Última</th><th>Próxima</th><th>Acción</th></tr></thead>
        <tbody>
          <?php foreach ($equipos as $eq): $tone=$stateTone($eq['estado'] ?? 'operativo'); $dias=$daysTo($eq['proxima_revision'] ?? null); ?>
            <tr data-equipment-row>
              <td><div class="mant-eq-main-v39"><i class="fa-solid fa-microchip"></i><div><b><?= e($eq['nombre'] ?? 'Equipo') ?></b><small><?= e($eq['numero_serie'] ?? 'Sin serie') ?></small></div></div></td>
              <td><?= e($eq['alberca'] ?? 'Sin alberca') ?></td>
              <td><span class="mant-eq-type-v39"><?= e($eq['tipo'] ?? 'Equipo') ?></span></td>
              <td><span class="mant-eq-state-v39 <?= e($tone) ?>"><?= e($stateLabel($eq['estado'] ?? 'operativo')) ?></span></td>
              <td><span class="mant-eq-date-v39"><?= e($fmtDate($eq['ultima_revision'] ?? null)) ?></span></td>
              <td><span class="mant-eq-date-v39 <?= ($dias !== null && $dias <= 7) ? 'due' : '' ?>"><?= e($fmtDate($eq['proxima_revision'] ?? null)) ?></span><small class="mant-eq-days-v39"><?= $dias===null?'Sin fecha':($dias<0?'Vencida hace '.abs($dias).'d':($dias===0?'Hoy':'En '.$dias.'d')) ?></small></td>
              <td><button type="button" class="mant-eq-select-v39" data-eq-id="<?= e($eq['idEquipo'] ?? '') ?>" data-eq-name="<?= e($eq['nombre'] ?? 'Equipo') ?>" data-eq-state="<?= e($eq['estado'] ?? 'operativo') ?>" data-eq-next="<?= e($eq['proxima_revision'] ?? '') ?>"><i class="fa-solid fa-pen-to-square"></i> Revisar</button></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <aside class="glass-card mant-eq-form-card-v39">
    <div class="mant-eq-head-v39 compact"><div><span>Registro técnico</span><h3>Actualizar revisión</h3></div></div>
    <form method="post" class="mant-eq-form-v39">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="update_equipo">
      <label><span>Equipo</span><select name="idEquipo" id="eqSelect" required><?php foreach($equipos as $eq): ?><option value="<?= e($eq['idEquipo'] ?? '') ?>"><?= e($eq['nombre'] ?? 'Equipo') ?></option><?php endforeach; ?></select></label>
      <label><span>Estado nuevo</span><select name="estado" id="eqState"><option value="operativo">Operativo</option><option value="revision">En revisión</option><option value="critico">Crítico</option><option value="fuera_servicio">Fuera de servicio</option></select></label>
      <div class="mant-eq-form-row-v39">
        <label><span>Última revisión</span><input type="date" name="ultima_revision" value="<?= e(date('Y-m-d')) ?>"></label>
        <label><span>Próxima</span><input type="date" name="proxima_revision" id="eqNext" value="<?= e(date('Y-m-d',strtotime('+30 days'))) ?>"></label>
      </div>
      <label><span>Comentario rápido</span><textarea name="comentario" rows="2" placeholder="Ej. presión normal, limpieza de filtro, reemplazo pendiente"></textarea></label>
      <button class="btn-aqua w-100" type="submit"><i class="fa-solid fa-floppy-disk"></i> Guardar revisión</button>
    </form>
    <div class="mant-eq-next-v39">
      <span>Siguiente prioridad</span>
      <?php if($next): $dias=$daysTo($next['proxima_revision'] ?? null); ?>
        <b><?= e($next['nombre'] ?? 'Equipo') ?></b>
        <small><?= e($next['alberca'] ?? '') ?> · <?= $dias===null?'sin fecha':($dias<0?'vencida':'en '.$dias.' días') ?></small>
      <?php else: ?>
        <b>Sin equipos</b><small>No hay revisión pendiente.</small>
      <?php endif; ?>
    </div>
  </aside>

  <section class="glass-card mant-eq-pools-v39">
    <div class="mant-eq-head-v39 compact"><div><span>Cobertura</span><h3>Presión técnica por alberca</h3></div></div>
    <div class="mant-eq-pool-list-v39">
      <?php foreach (($porAlberca ?: ['Alberca principal'=>['total'=>0,'ok'=>0,'revision'=>0,'alerta'=>0],'Alberca familiar'=>['total'=>0,'ok'=>0,'revision'=>0,'alerta'=>0],'Alberca infantil'=>['total'=>0,'ok'=>0,'revision'=>0,'alerta'=>0],'Alberca vista al mar'=>['total'=>0,'ok'=>0,'revision'=>0,'alerta'=>0],'Alberca deportiva'=>['total'=>0,'ok'=>0,'revision'=>0,'alerta'=>0]]) as $pool=>$row): $pct=(int)round(((int)$row['total']/$poolMax)*100); ?>
        <div class="mant-eq-pool-row-v39"><b><?= e($pool) ?></b><span><i style="width:<?= $pct ?>%"></i></span><em><?= (int)$row['ok'] ?> ok</em><strong><?= (int)$row['alerta'] + (int)$row['revision'] ?></strong></div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card mant-eq-chart-card-v39">
    <div class="mant-eq-head-v39 compact"><div><span>Distribución</span><h3>Estado de equipos</h3></div></div>
    <div class="mant-eq-chart-body-v39"><canvas id="equipmentStateChart"></canvas><div class="mant-eq-legend-v39"><div><span>Operativos</span><b><?= (int)$operativos ?></b></div><div><span>Revisión</span><b><?= (int)$revision ?></b></div><div><span>Críticos</span><b><?= (int)$criticos+$fuera ?></b></div></div></div>
  </section>

  <section class="glass-card mant-eq-protocol-v39">
    <div class="mant-eq-head-v39 compact"><div><span>Protocolo</span><h3>Regla técnica</h3></div></div>
    <div class="mant-eq-rules-v39">
      <div><i class="fa-solid fa-1"></i><b>Críticos primero</b><small>Equipo fuera/critico bloquea operación de la alberca.</small></div>
      <div><i class="fa-solid fa-2"></i><b>Registrar evidencia</b><small>Cada revisión debe actualizar fecha y estado.</small></div>
      <div><i class="fa-solid fa-3"></i><b>Escalar a ticket</b><small>Si no se resuelve, abrir seguimiento FIFO.</small></div>
    </div>
  </section>
</div>
<script>
(() => {
  const q = document.getElementById('equipmentSearch');
  const rows = [...document.querySelectorAll('[data-equipment-row]')];
  q?.addEventListener('input', () => {
    const term = q.value.trim().toLowerCase();
    rows.forEach(row => row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none');
  });
  document.querySelectorAll('.mant-eq-select-v39').forEach(btn => btn.addEventListener('click', () => {
    const sel = document.getElementById('eqSelect'); const st = document.getElementById('eqState'); const nx = document.getElementById('eqNext');
    if (sel) sel.value = btn.dataset.eqId || sel.value;
    if (st) st.value = btn.dataset.eqState || 'operativo';
    if (nx && btn.dataset.eqNext) nx.value = btn.dataset.eqNext;
  }));
  const stateCtx = document.getElementById('equipmentStateChart');
  if (stateCtx && window.Chart) {
    new Chart(stateCtx, {type:'doughnut', data:{labels:<?= json_encode($stateLabels, JSON_UNESCAPED_UNICODE) ?>, datasets:[{data:<?= json_encode($stateCounts) ?>, borderWidth:0}]}, options:{responsive:true, maintainAspectRatio:false, cutout:'68%', plugins:{legend:{display:false}}}});
  }
})();
</script>
