<?php
$capacidadTotal = array_sum(array_map(fn($x)=>(int)$x['capacidad_maxima'], $p));
$ocupacionTotal = array_sum(array_map(fn($x)=>max(0,(int)$x['ocupacion_actual']), $p));
$libresTotal = max(0, $capacidadTotal - $ocupacionTotal);
$ocupacionPct = pct($ocupacionTotal, $capacidadTotal);
$entradas = (int)($k['entradas'] ?? 0);
$salidas = (int)($k['salidas'] ?? 0);
$enRiesgo = 0;
$cerradas = 0;
$poolTop = null;
foreach ($p as $pool) {
  $pc = pct((int)$pool['ocupacion_actual'], (int)$pool['capacidad_maxima']);
  $estado = mb_strtolower((string)$pool['estado_nombre']);
  if ($pc >= 80 || str_contains($estado,'mantenimiento') || str_contains($estado,'limpieza') || str_contains($estado,'cerrada')) $enRiesgo++;
  if (str_contains($estado,'mantenimiento') || str_contains($estado,'limpieza') || str_contains($estado,'cerrada')) $cerradas++;
  if (!$poolTop || $pc > pct((int)$poolTop['ocupacion_actual'], (int)$poolTop['capacidad_maxima'])) $poolTop = $pool;
}
$horaActual = date('H:i');
$openTimes = array_filter(array_map(fn($pool)=>substr((string)($pool['horario_apertura'] ?? ''),0,5), $p));
$closeTimes = array_filter(array_map(fn($pool)=>substr((string)($pool['horario_cierre'] ?? ''),0,5), $p));
$turnoInicio = $openTimes ? min($openTimes) : '--:--';
$turnoFin = $closeTimes ? max($closeTimes) : '--:--';
$horaPico = 'Sin datos';
if (!empty($flow['entradas'])) {
  $max = max($flow['entradas']);
  $idx = array_search($max, $flow['entradas'], true);
  $horaPico = isset($flow['labels'][$idx]) ? ($flow['labels'][$idx] . ':00') : 'Sin datos';
}
$presion = $ocupacionPct >= 85 ? 'Alta' : ($ocupacionPct >= 65 ? 'Media' : 'Controlada');
$recomendacion = $enRiesgo > 0 ? 'Revisar albercas con alerta antes de permitir más entradas.' : 'Operación estable: mantener monitoreo cada 15 minutos.';
?>
<div class="aforo-saas-v26">
  <section class="aforo-kpi-strip">
    <article class="aforo-kpi-card primary"><i class="fa-solid fa-users-viewfinder"></i><span>Ocupación actual</span><b><?= e($ocupacionTotal) ?></b><small><?= e($ocupacionPct) ?>% del complejo</small></article>
    <article class="aforo-kpi-card"><i class="fa-solid fa-door-open"></i><span>Espacios libres</span><b><?= e($libresTotal) ?></b><small>Capacidad total <?= e($capacidadTotal) ?></small></article>
    <article class="aforo-kpi-card success"><i class="fa-solid fa-arrow-right-to-bracket"></i><span>Entradas hoy</span><b><?= e($entradas) ?></b><small>Desde <?= e($turnoInicio) ?></small></article>
    <article class="aforo-kpi-card coral"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Salidas hoy</span><b><?= e($salidas) ?></b><small>Flujo neto <?= e(max(0,$entradas-$salidas)) ?></small></article>
    <article class="aforo-kpi-card warning"><i class="fa-solid fa-triangle-exclamation"></i><span>Atención</span><b><?= e($enRiesgo) ?></b><small>Albercas en revisión</small></article>
  </section>

  <section class="glass-card aforo-command-card">
    <div class="aforo-head compact"><div><h3>Registrar movimiento</h3><span>Entradas y salidas del turno</span></div><em><?= e($turnoInicio) ?> - <?= e($turnoFin) ?></em></div>
    <form method="POST" class="aforo-form" action="<?= e(page_url('encargado-aforo')) ?>">
      <?= csrf_field() ?>
      <label>Alberca</label>
      <select name="idAlberca" class="form-select" required>
        <?php foreach($p as $pool): ?>
          <option value="<?= e($pool['idAlberca']) ?>"><?= e($pool['nombre']) ?> · <?= e($pool['ocupacion_actual']) ?>/<?= e($pool['capacidad_maxima']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>Movimiento</label>
      <div class="aforo-toggle">
        <input type="radio" name="tipo_movimiento" id="movEntrada" value="entrada" checked>
        <label for="movEntrada"><i class="fa-solid fa-plus"></i> Entrada</label>
        <input type="radio" name="tipo_movimiento" id="movSalida" value="salida">
        <label for="movSalida"><i class="fa-solid fa-minus"></i> Salida</label>
      </div>
      <label>Cantidad</label>
      <input class="form-control aforo-cantidad" type="number" name="cantidad" min="1" value="1" required>
      <div class="aforo-quick-qty" aria-label="Cantidades rápidas">
        <?php foreach([1,5,10,15] as $qty): ?><button type="button" data-aforo-qty="<?= e($qty) ?>"><?= e($qty) ?></button><?php endforeach; ?>
      </div>
      <button class="btn btn-aqua w-100"><i class="fa-solid fa-check me-1"></i> Confirmar movimiento</button>
    </form>
    <div class="aforo-command-summary">
      <div><b><?= e($horaActual) ?></b><span>Hora actual</span></div>
      <div><b><?= e($presion) ?></b><span>Presión</span></div>
    </div>
  </section>

  <section class="glass-card aforo-live-card">
    <div class="aforo-head"><div><h3>Estado vivo de aforo</h3><span>Control estricto por capacidad máxima</span></div><a href="<?= e(page_url('encargado-alertas')) ?>">Ver alertas</a></div>
    <div class="aforo-pool-list">
      <?php foreach($p as $pool):
        $actual=max(0,(int)$pool['ocupacion_actual']); $cap=(int)$pool['capacidad_maxima']; $pc=pct($actual,$cap); $free=max(0,$cap-$actual);
        $estado=(string)$pool['estado_nombre']; $st=mb_strtolower($estado);
        $tone=$pc>=90||str_contains($st,'mantenimiento')||str_contains($st,'cerrada')?'danger':($pc>=75||str_contains($st,'limpieza')?'warning':'success');
      ?>
      <article class="aforo-pool-row <?= e($tone) ?>">
        <div class="aforo-pool-title"><i class="fa-solid fa-water-ladder"></i><div><b><?= e($pool['nombre']) ?></b><small><?= e($estado) ?></small></div></div>
        <div class="aforo-pressure"><div class="aforo-pressure-line"><strong><?= e($actual) ?>/<?= e($cap) ?></strong><span><?= e($free) ?> libres</span><em><?= e($pc) ?>%</em></div><div class="aforo-progress"><span style="width:<?= e($pc) ?>%"></span></div></div>
        <div class="aforo-mini-actions">
          <form method="POST" action="<?= e(page_url('encargado-aforo')) ?>"><?= csrf_field() ?><input type="hidden" name="idAlberca" value="<?= e($pool['idAlberca']) ?>"><input type="hidden" name="tipo_movimiento" value="entrada"><input type="hidden" name="cantidad" value="1"><button title="Entrada rápida +1">+1</button></form>
          <form method="POST" action="<?= e(page_url('encargado-aforo')) ?>"><?= csrf_field() ?><input type="hidden" name="idAlberca" value="<?= e($pool['idAlberca']) ?>"><input type="hidden" name="tipo_movimiento" value="salida"><input type="hidden" name="cantidad" value="1"><button title="Salida rápida -1">-1</button></form>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card aforo-flow-card">
    <div class="aforo-head"><div><h3>Flujo del turno</h3><span>Entradas vs salidas por hora</span></div><em><?= e($horaPico) ?> pico</em></div>
    <div class="aforo-chart-wrap"><canvas id="aforoFlowChart"></canvas></div>
    <div class="aforo-flow-metrics">
      <div><span>Hora pico</span><b><?= e($horaPico) ?></b></div>
      <div><span>Neto</span><b><?= e(max(0,$entradas-$salidas)) ?></b></div>
      <div><span>Control</span><b><?= e($presion) ?></b></div>
    </div>
  </section>

  <section class="glass-card aforo-recent-card">
    <div class="aforo-head"><div><h3>Bitácora reciente</h3><span>Últimos movimientos del día</span></div><span class="aforo-pill">FIFO visual</span></div>
    <div class="aforo-table-wrap">
      <table class="aforo-table">
        <thead><tr><th>Hora</th><th>Alberca</th><th>Tipo</th><th>Cantidad</th><th>Registró</th></tr></thead>
        <tbody>
        <?php foreach($recent as $mov): $isIn=($mov['tipo_movimiento']??'')==='entrada'; ?>
          <tr>
            <td><b><?= e(date('H:i', strtotime((string)$mov['registrado_en']))) ?></b></td>
            <td><strong><?= e($mov['alberca']) ?></strong></td>
            <td><span class="aforo-move <?= $isIn?'in':'out' ?>"><?= $isIn?'<i class="fa-solid fa-arrow-right-to-bracket"></i> Entrada':'<i class="fa-solid fa-arrow-right-from-bracket"></i> Salida' ?></span></td>
            <td><?= e($mov['cantidad']) ?></td>
            <td><?= e($mov['usuario'] ?: 'Sistema') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="glass-card aforo-guard-card">
    <div class="aforo-head compact"><div><h3>Semáforo operativo</h3><span>Acciones recomendadas</span></div></div>
    <div class="aforo-ring"><b><?= e($ocupacionPct) ?>%</b><span>ocupación global</span></div>
    <div class="aforo-guard-list">
      <div><i class="fa-solid fa-star"></i><b><?= e($poolTop['nombre'] ?? 'Sin datos') ?></b><span>Mayor presión actual</span></div>
      <div><i class="fa-solid fa-lock"></i><b><?= e($cerradas) ?></b><span>Sin entrada operativa</span></div>
      <div><i class="fa-solid fa-route"></i><b>Según turno</b><span>Revisión sugerida</span></div>
    </div>
    <p class="aforo-recommendation"><i class="fa-solid fa-bolt"></i><?= e($recomendacion) ?></p>
  </section>
</div>
<script>
(function(){
  document.querySelectorAll('[data-aforo-qty]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var input = document.querySelector('.aforo-cantidad');
      if(input) input.value = btn.getAttribute('data-aforo-qty');
    });
  });
  if(!window.Chart) return;
  var canvas=document.getElementById('aforoFlowChart');
  if(!canvas) return;
  new Chart(canvas,{type:'line',data:{labels:<?= json_encode($flow['labels'] ?? []) ?>,datasets:[{label:'Entradas',data:<?= json_encode($flow['entradas'] ?? []) ?>,tension:.42,fill:true,borderWidth:3,pointRadius:0},{label:'Salidas',data:<?= json_encode($flow['salidas'] ?? []) ?>,tension:.42,fill:true,borderWidth:3,pointRadius:0}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,labels:{boxWidth:10,usePointStyle:true,font:{weight:'800',size:10}}},tooltip:{mode:'index',intersect:false}},scales:{x:{grid:{display:false},ticks:{font:{size:10,weight:'800'}}},y:{beginAtZero:true,grid:{color:'rgba(18,56,95,.08)'},ticks:{font:{size:10,weight:'800'}}}}}});
})();
</script>
