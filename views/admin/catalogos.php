<?php
$roles = $roles ?? [];
$mantTipos = $mantTipos ?? [];
$areas = $areas ?? [];
$tareas = $tareas ?? [];
$ticketTipos = $cats['tipos'] ?? [];
$prioridades = $cats['prioridades'] ?? [];
$ticketEstados = $cats['estados'] ?? [];

$catalogosTotal = count($roles) + count($estados) + count($ticketTipos) + count($prioridades) + count($ticketEstados) + count($mantTipos) + count($areas) + count($tareas);
$activosCriticos = count($estados) + count($prioridades) + count($ticketEstados);
$poolOfficialStates = ['Disponible','En uso (profesional solamente)','En limpieza','En mantenimiento','Cerrada'];
$roleIcons = [1=>'fa-user-tie',2=>'fa-water-ladder',3=>'fa-broom',4=>'fa-screwdriver-wrench'];
$stateTone = ['success'=>'ok','info'=>'info','warning'=>'warn','danger'=>'danger','dark'=>'dark'];
?>

<div class="catalog-saas-page">
  <section class="catalog-kpi-strip">
    <article class="catalog-kpi-card">
      <i class="fa-solid fa-layer-group"></i>
      <div><span>Catálogos</span><b><?= (int)$catalogosTotal ?></b><small>registros maestros</small></div>
    </article>
    <article class="catalog-kpi-card violet">
      <i class="fa-solid fa-user-shield"></i>
      <div><span>Roles B2E</span><b><?= count($roles) ?></b><small>redirección por idRol</small></div>
    </article>
    <article class="catalog-kpi-card aqua">
      <i class="fa-solid fa-water"></i>
      <div><span>Estados alberca</span><b><?= count($estados) ?>/5</b><small>RN03 oficial</small></div>
    </article>
    <article class="catalog-kpi-card coral">
      <i class="fa-solid fa-ticket"></i>
      <div><span>Tickets</span><b><?= count($ticketTipos) ?></b><small>tipos de incidencia</small></div>
    </article>
    <article class="catalog-kpi-card amber">
      <i class="fa-solid fa-list-check"></i>
      <div><span>Reglas vivas</span><b><?= (int)$activosCriticos ?></b><small>operación controlada</small></div>
    </article>
  </section>

  <section class="catalog-main-grid">
    <article class="glass-card catalog-board-card">
      <div class="section-head compact catalog-head-row">
        <div>
          <h3>Catálogos maestros del sistema</h3>
          <span>Base de datos operativa para usuarios, albercas, tickets, limpieza y mantenimiento.</span>
        </div>
        <label class="catalog-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input data-catalog-filter type="search" placeholder="Buscar catálogo, rol, estado o prioridad">
        </label>
      </div>

      <div class="catalog-groups-grid">
        <div class="catalog-box roles-box" data-catalog-section>
          <div class="catalog-box-title"><i class="fa-solid fa-user-shield"></i><div><b>Roles y acceso</b><small>Panel asignado automáticamente después del login.</small></div></div>
          <div class="catalog-rows compact">
            <?php foreach($roles as $r): $rid=(int)($r['idRol'] ?? 0); ?>
              <div class="catalog-row" data-catalog-item>
                <i class="fa-solid <?= e($roleIcons[$rid] ?? 'fa-user') ?>"></i>
                <div><b><?= e($r['nombre']) ?></b><small>idRol <?= $rid ?> · acceso <?= e(role_slug($rid)) ?></small></div>
                <span class="pill-state ok">activo</span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="catalog-box states-box" data-catalog-section>
          <div class="catalog-box-title"><i class="fa-solid fa-swimming-pool"></i><div><b>Estados oficiales de alberca</b><small>Solo estos 5 estados son válidos en operación.</small></div></div>
          <div class="state-catalog-grid">
            <?php foreach($estados as $e): $tone=$stateTone[$e['clase_ui'] ?? 'info'] ?? 'info'; ?>
              <div class="state-catalog-item <?= e($tone) ?>" data-catalog-item>
                <span></span>
                <b><?= e($e['nombre']) ?></b>
                <small><?= !empty($e['bloquea_aforo']) ? 'bloquea aforo' : 'permite operación' ?></small>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="catalog-box tickets-box" data-catalog-section>
          <div class="catalog-box-title"><i class="fa-solid fa-screwdriver-wrench"></i><div><b>Tickets de mantenimiento</b><small>Clasificación, prioridad y avance FIFO.</small></div></div>
          <div class="ticket-catalog-layout">
            <div class="ticket-chip-list">
              <?php foreach($ticketTipos as $t): ?>
                <span class="catalog-chip" data-catalog-item><?= e($t['nombre']) ?></span>
              <?php endforeach; ?>
            </div>
            <div class="priority-ladder">
              <?php foreach($prioridades as $p): $nivel=(int)($p['nivel'] ?? 0); ?>
                <div data-catalog-item><b><?= e($p['nombre']) ?></b><i style="width:<?= min(100,max(20,$nivel*25)) ?>%"></i><small>Nivel <?= $nivel ?></small></div>
              <?php endforeach; ?>
            </div>
            <div class="ticket-state-strip">
              <?php foreach($ticketEstados as $s): ?>
                <span data-catalog-item><?= e($s['nombre']) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="catalog-box operation-box" data-catalog-section>
          <div class="catalog-box-title"><i class="fa-solid fa-calendar-check"></i><div><b>Mantenimiento y limpieza</b><small>Catálogos que alimentan turnos, agenda y checklist.</small></div></div>
          <div class="operation-catalog-grid">
            <div>
              <strong>Tipos mantenimiento</strong>
              <?php foreach($mantTipos as $m): ?><span data-catalog-item><?= e($m['nombre']) ?></span><?php endforeach; ?>
            </div>
            <div>
              <strong>Áreas limpieza</strong>
              <?php foreach($areas as $a): ?><span data-catalog-item><?= e($a['nombre']) ?></span><?php endforeach; ?>
            </div>
            <div class="span2">
              <strong>Tareas checklist</strong>
              <div class="task-chip-grid">
                <?php foreach(array_slice($tareas,0,8) as $ta): ?><span data-catalog-item><?= e($ta['nombre']) ?></span><?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </article>

    <aside class="catalog-side-stack">
      <article class="glass-card catalog-rules-card">
        <div class="section-head compact">
          <div><h3>Reglas amarradas</h3><span>Validaciones que dependen de catálogos.</span></div>
        </div>
        <div class="rule-timeline">
          <div><i>RN01</i><b>Horario operativo</b><span>07:00 - 21:00</span></div>
          <div><i>RN02</i><b>Capacidad por alberca</b><span>620 personas total</span></div>
          <div><i>RN03</i><b>Estados oficiales</b><span>5 valores controlados</span></div>
          <div><i>RN04</i><b>Cierre automático</b><span>12 h sin seguimiento</span></div>
          <div><i>RN05</i><b>Atención FIFO</b><span>prioridad + llegada</span></div>
        </div>
      </article>

      <article class="glass-card catalog-map-card">
        <div class="section-head compact">
          <div><h3>Impacto operativo</h3><span>De catálogo a módulo del sistema.</span></div>
        </div>
        <div class="module-impact-list">
          <div><i class="fa-solid fa-user-check"></i><b>Usuarios</b><small>rol + estado definen acceso.</small></div>
          <div><i class="fa-solid fa-water-ladder"></i><b>Albercas</b><small>estado bloquea o permite aforo.</small></div>
          <div><i class="fa-solid fa-ticket"></i><b>Tickets</b><small>tipo + prioridad ordenan la cola.</small></div>
          <div><i class="fa-solid fa-broom"></i><b>Limpieza</b><small>área + tarea forman checklist diario.</small></div>
          <div><i class="fa-solid fa-toolbox"></i><b>Mantenimiento</b><small>tipo define agenda preventiva/correctiva.</small></div>
        </div>
      </article>
    </aside>
  </section>
</div>
