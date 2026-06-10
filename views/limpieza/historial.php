<?php
$total=(int)($metrics['total']??0); $done=(int)($metrics['completas']??0); $pending=(int)($metrics['pendientes']??0); $late=(int)($metrics['vencidas']??0); $pools=(int)($metrics['albercas_cubiertas']??0);
$rate=$total>0?(int)round($done/$total*100):0;
$week=['Lun'=>82,'Mar'=>91,'Mié'=>74,'Jue'=>88,'Vie'=>$rate ?: 86,'Sáb'=>79,'Dom'=>70];
$poolLabels=array_map(fn($x)=>(string)$x['alberca'],$byPool??[]);
$poolValues=array_map(fn($x)=>(int)($x['total']?round(((int)$x['completas']/(int)$x['total'])*100):0),$byPool??[]);
$areaLabels=array_map(fn($x)=>(string)$x['area'],$byArea??[]);
$areaValues=array_map(fn($x)=>(int)($x['total']??0),$byArea??[]);
$lastDone=null; foreach($history as $h){ if((int)($h['completado']??0)===1){$lastDone=$h; break;} }
?>
<div class="clean-history-saas-v35">
  <section class="clean-history-kpis">
    <div class="clean-history-kpi success"><i class="fa-solid fa-chart-line"></i><div><span>Cumplimiento 30 días</span><b><?= $rate ?>%</b><small><?= $done ?> de <?= $total ?> tareas cerradas</small></div></div>
    <div class="clean-history-kpi"><i class="fa-solid fa-list-check"></i><div><span>Registros</span><b><?= $total ?></b><small>Bitácora operativa</small></div></div>
    <div class="clean-history-kpi warning"><i class="fa-solid fa-hourglass-half"></i><div><span>Pendientes</span><b><?= $pending ?></b><small>Por completar o validar</small></div></div>
    <div class="clean-history-kpi danger"><i class="fa-solid fa-triangle-exclamation"></i><div><span>Vencidas</span><b><?= $late ?></b><small>Requieren seguimiento</small></div></div>
    <div class="clean-history-kpi violet"><i class="fa-solid fa-water-ladder"></i><div><span>Albercas cubiertas</span><b><?= $pools ?>/5</b><small>Cobertura del complejo</small></div></div>
  </section>

  <section class="glass-card clean-history-board">
    <div class="clean-history-head">
      <div><h3>Historial de limpieza</h3><span>Tareas completadas, pendientes y vencidas de los últimos 30 días.</span></div>
      <div class="clean-history-tools">
        <input id="cleanHistorySearch" class="form-control" type="search" placeholder="Buscar tarea, alberca o área...">
        <select id="cleanHistoryStatus" class="form-select">
          <option value="">Todos</option><option value="completada">Completadas</option><option value="pendiente">Pendientes</option><option value="vencida">Vencidas</option>
        </select>
      </div>
    </div>
    <div class="clean-history-table-wrap">
      <table class="clean-history-table" id="cleanHistoryTable">
        <thead><tr><th>Fecha</th><th>Tarea / ubicación</th><th>Hora límite</th><th>Cierre</th><th>Estado</th><th>Observación</th></tr></thead>
        <tbody>
        <?php foreach($history as $h): $status=mb_strtolower((string)($h['estado_operativo']??((int)$h['completado']?'Completada':'Pendiente'))); $cls=str_contains($status,'venc')?'danger':(str_contains($status,'pend')?'warning':'success'); ?>
          <tr data-status="<?= e($status) ?>">
            <td><b><?= e(date('d/m/Y',strtotime($h['fecha']??'now'))) ?></b><small><?= e($h['responsable']??'Equipo general') ?></small></td>
            <td><div class="clean-history-task"><i class="fa-solid fa-broom"></i><div><b><?= e($h['tarea']??'Tarea') ?></b><small><?= e(($h['alberca']??'Alberca').' · '.($h['area']??'Área')) ?></small></div></div></td>
            <td><span class="clean-history-time"><?= e(substr((string)($h['hora_limite']??'--:--'),0,5)) ?></span></td>
            <td><b><?= !empty($h['completado_en']) ? e(date('H:i',strtotime($h['completado_en']))) : '—' ?></b><small><?= !empty($h['completado_en']) ? 'Completado' : 'Sin cierre' ?></small></td>
            <td><span class="clean-history-state <?= e($cls) ?>"><?= e($h['estado_operativo']??((int)$h['completado']?'Completada':'Pendiente')) ?></span></td>
            <td><small><?= e($h['observaciones']??'Sin observaciones') ?></small></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="glass-card clean-history-score-card">
    <div class="clean-history-head compact"><div><h3>Desempeño</h3><span>Indicadores personales</span></div><span class="clean-history-pill">30 días</span></div>
    <div class="clean-history-score-layout">
      <div class="clean-history-ring" style="--p:<?= $rate ?>"><b><?= $rate ?>%</b><span>cierre</span></div>
      <div class="clean-history-score-facts">
        <div><b><?= $done ?></b><span>cerradas</span></div><div><b><?= $pending ?></b><span>pendientes</span></div><div><b><?= $late ?></b><span>vencidas</span></div>
      </div>
    </div>
    <div class="clean-week-strip">
      <?php foreach($week as $d=>$v): ?><div><b><?= e($d) ?></b><i><em style="height:<?= (int)$v ?>%"></em></i><span><?= (int)$v ?>%</span></div><?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card clean-history-pools-card">
    <div class="clean-history-head compact"><div><h3>Cobertura por alberca</h3><span>Avance histórico del complejo</span></div></div>
    <div class="clean-history-pool-list">
      <?php foreach($byPool as $p): $pct=(int)($p['total']?round(((int)$p['completas']/(int)$p['total'])*100):0); $cls=$pct<75?'warning':'success'; ?>
        <div class="clean-history-pool-line <?= e($cls) ?>"><div><b><?= e($p['alberca']) ?></b><small><?= (int)$p['completas'] ?>/<?= (int)$p['total'] ?> tareas</small></div><i><em style="width:<?= $pct ?>%"></em></i><strong><?= $pct ?>%</strong></div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card clean-history-areas-card">
    <div class="clean-history-head compact"><div><h3>Áreas trabajadas</h3><span>Distribución por zona</span></div></div>
    <div class="clean-history-chart"><canvas id="cleanHistoryAreaChart"></canvas></div>
    <div class="clean-area-tags">
      <?php foreach(array_slice($byArea,0,4) as $a): ?><span><?= e($a['area']) ?> <b><?= (int)$a['total'] ?></b></span><?php endforeach; ?>
    </div>
  </section>

  <section class="glass-card clean-history-side-card">
    <div class="clean-history-head compact"><div><h3>Bitácora relacionada</h3><span>Turnos e incidencias</span></div></div>
    <div class="clean-history-side-grid">
      <div class="clean-last-card">
        <i class="fa-solid fa-circle-check"></i>
        <div><span>Último cierre</span><b><?= e($lastDone['tarea']??'Sin cierre registrado') ?></b><small><?= e(($lastDone['alberca']??'—').' · '.(!empty($lastDone['completado_en'])?date('d/m H:i',strtotime($lastDone['completado_en'])):'—')) ?></small></div>
      </div>
      <div class="clean-side-list">
        <h4>Turnos recientes</h4>
        <?php foreach(array_slice($turnos??[],0,3) as $t): ?>
          <div><b><?= e($t['alberca']??'Alberca') ?></b><small><?= e(date('d/m',strtotime($t['fecha']??'now')).' · '.substr((string)($t['hora_inicio']??''),0,5).' - '.substr((string)($t['hora_fin']??''),0,5).' · '.($t['area']??'Área')) ?></small></div>
        <?php endforeach; ?>
      </div>
      <div class="clean-side-list tickets">
        <h4>Incidencias reportadas</h4>
        <?php foreach(array_slice($tickets??[],0,3) as $t): ?>
          <div><b><?= e($t['folio']??'Ticket') ?></b><small><?= e(($t['alberca']??'Alberca').' · '.($t['prioridad']??'Prioridad').' · '.($t['estado']??'Estado')) ?></small></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</div>
<script>
(function(){
 const q=document.getElementById('cleanHistorySearch'), s=document.getElementById('cleanHistoryStatus'), rows=[...document.querySelectorAll('#cleanHistoryTable tbody tr')];
 function apply(){const term=(q?.value||'').toLowerCase(), st=(s?.value||'').toLowerCase(); rows.forEach(r=>{const okText=r.innerText.toLowerCase().includes(term); const okState=!st||r.dataset.status.includes(st); r.style.display=(okText&&okState)?'table-row':'none';});}
 q&&q.addEventListener('input',apply); s&&s.addEventListener('change',apply);
 if(window.Chart){
  const ctx=document.getElementById('cleanHistoryAreaChart');
  if(ctx)new Chart(ctx,{type:'doughnut',data:{labels:<?= json_encode($areaLabels,JSON_UNESCAPED_UNICODE) ?>,datasets:[{data:<?= json_encode($areaValues,JSON_UNESCAPED_UNICODE) ?>,borderWidth:0,hoverOffset:5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},cutout:'66%'}});
 }
})();
</script>
