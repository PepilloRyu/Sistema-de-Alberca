<?php
$users = $users ?? [];
$roles = $roles ?? [];
$metrics = $metrics ?? [];
$audits = $audits ?? [];
$total = max(1,(int)($metrics['total'] ?? count($users) ?: 1));
$activos = (int)($metrics['activos'] ?? 0);
$pendientes = (int)($metrics['pendientes'] ?? 0);
$sinRol = (int)($metrics['sin_rol'] ?? 0);
$sinAcceso = (int)($metrics['sin_acceso'] ?? 0);
$admins = (int)($metrics['admins'] ?? 1);
$timeout = (int)config('session_timeout',900);
$sessionStarted = (int)($_SESSION['last_activity'] ?? time());
$sessionAge = max(0,time() - $sessionStarted);
$remaining = max(0,$timeout - $sessionAge);
$score = max(72,min(100, 100 - ($pendientes*5) - ($sinRol*7) - ($sinAcceso*2)));
$sessionPct = pct($remaining,$timeout);
$roleMap = [];
foreach($roles as $r){ $roleMap[(int)$r['idRol']] = $r['nombre']; }
$permRows = [
 ['rol'=>'Administrador','icon'=>'fa-user-shield','modulos'=>'Usuarios, catálogos, seguridad, reportes','nivel'=>'Total'],
 ['rol'=>'Encargado','icon'=>'fa-water','modulos'=>'Aforo, agua, alertas, incidencias','nivel'=>'Operativo'],
 ['rol'=>'Limpieza','icon'=>'fa-broom','modulos'=>'Turnos, checklist, incidencias','nivel'=>'Ejecución'],
 ['rol'=>'Técnico','icon'=>'fa-screwdriver-wrench','modulos'=>'Tickets, agenda, equipos','nivel'=>'Técnico'],
];
$roleCounts=[];
foreach($users as $u){ $rn = $u['rol'] ?? 'Sin Rol'; $roleCounts[$rn]=($roleCounts[$rn]??0)+1; }
$chartLabels=array_keys($roleCounts) ?: ['Administrador','Encargado','Limpieza','Técnico'];
$chartValues=array_values($roleCounts) ?: [1,1,1,1];
function securityRiskLabel(array $u): array {
  $estado = strtolower((string)($u['estado'] ?? ''));
  $rol = (int)($u['idRol'] ?? 0);
  $last = $u['ultimo_acceso'] ?? null;
  if($estado === 'pendiente' || $rol === 0) return ['Alto','danger','Aprobar rol'];
  if($estado === 'inactivo') return ['Medio','warning','Revisar acceso'];
  if(empty($last)) return ['Bajo','info','Primer acceso'];
  return ['OK','success','Operativo'];
}
?>
<div class="security-saas-page security-v23">
  <section class="security-kpi-row">
    <article class="security-kpi">
      <i class="fa-solid fa-shield-halved"></i>
      <span>Índice seguro</span>
      <b><?= e($score) ?>%</b>
      <small>bcrypt · CSRF · roles</small>
    </article>
    <article class="security-kpi kpi-blue">
      <i class="fa-solid fa-clock-rotate-left"></i>
      <span>Sesión RNF02</span>
      <b><?= e((int)($timeout/60)) ?> min</b>
      <small><span id="idleCountdown"><?= e(sprintf('%02d:%02d', floor($remaining/60), $remaining%60)) ?></span> restantes</small>
    </article>
    <article class="security-kpi kpi-mint">
      <i class="fa-solid fa-users-gear"></i>
      <span>Usuarios activos</span>
      <b><?= e($activos) ?></b>
      <small><?= e($total) ?> empleados totales</small>
    </article>
    <article class="security-kpi kpi-amber">
      <i class="fa-solid fa-user-clock"></i>
      <span>Pendientes</span>
      <b><?= e($pendientes + $sinRol) ?></b>
      <small>sin rol o aprobación</small>
    </article>
    <article class="security-kpi kpi-coral">
      <i class="fa-solid fa-key"></i>
      <span>Administradores</span>
      <b><?= e($admins) ?></b>
      <small>privilegios máximos</small>
    </article>
  </section>

  <section class="security-left-grid">
    <article class="security-card security-session-card">
      <header class="security-headline">
        <div>
          <h3>Centro de seguridad de acceso</h3>
          <span>RNF01, RNF02 y control B2E interno.</span>
        </div>
        <b class="security-pill"><i class="fa-solid fa-lock"></i> Activo</b>
      </header>
      <div class="security-session-layout">
        <div class="security-ring-wrap">
          <canvas id="securityScoreChart" data-score="<?= e($score) ?>"></canvas>
          <div class="security-ring-text"><b><?= e($score) ?></b><span>salud</span></div>
        </div>
        <div class="security-control-grid">
          <div class="security-control-tile ok">
            <i class="fa-solid fa-fingerprint"></i>
            <b>Contraseñas</b>
            <span>password_hash bcrypt</span>
          </div>
          <div class="security-control-tile ok">
            <i class="fa-solid fa-user-lock"></i>
            <b>Sesiones</b>
            <span><?= e($timeout) ?> segundos backend/frontend</span>
          </div>
          <div class="security-control-tile ok">
            <i class="fa-solid fa-code"></i>
            <b>CSRF</b>
            <span>POST protegido por token</span>
          </div>
          <div class="security-control-tile ok">
            <i class="fa-solid fa-cookie-bite"></i>
            <b>Cookie</b>
            <span>HTTPOnly · SameSite=Lax</span>
          </div>
        </div>
      </div>
      <div class="security-session-strip">
        <div><span>Tiempo restante</span><b><?= e(sprintf('%02d:%02d', floor($remaining/60), $remaining%60)) ?></b></div>
        <div><span>Progreso sesión</span><b><?= e($sessionPct) ?>%</b></div>
        <div><span>Header seguro</span><b>X-Frame DENY</b></div>
      </div>
    </article>

    <article class="security-card security-users-card">
      <header class="security-headline">
        <div>
          <h3>Usuarios por riesgo</h3>
          <span>Acceso, rol, estado y última actividad.</span>
        </div>
        <b class="security-pill"><?= e(count($users)) ?> visibles</b>
      </header>
      <div class="security-user-list">
        <?php foreach(array_slice($users,0,6) as $u): [$risk,$cls,$action]=securityRiskLabel($u); ?>
          <div class="security-user-line">
            <div class="security-user-avatar"><?= e(mb_strtoupper(mb_substr((string)$u['nombre'],0,1))) ?></div>
            <div>
              <b><?= e($u['nombre']) ?></b>
              <small><?= e($u['email']) ?></small>
            </div>
            <span><?= e($u['rol'] ?? 'Sin Rol') ?></span>
            <em class="risk-<?= e($cls) ?>"><?= e($risk) ?></em>
            <strong><?= e($action) ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
    </article>

    <article class="security-card security-roles-card">
      <header class="security-headline compact">
        <div>
          <h3>Matriz de permisos</h3>
          <span>Redirección automática según idRol.</span>
        </div>
      </header>
      <div class="security-permission-grid">
        <?php foreach($permRows as $p): ?>
          <div class="security-permission-tile">
            <i class="fa-solid <?= e($p['icon']) ?>"></i>
            <b><?= e($p['rol']) ?></b>
            <span><?= e($p['modulos']) ?></span>
            <em><?= e($p['nivel']) ?></em>
          </div>
        <?php endforeach; ?>
      </div>
    </article>
  </section>

  <aside class="security-side-grid">
    <article class="security-card security-audit-card">
      <header class="security-headline">
        <div>
          <h3>Auditoría reciente</h3>
          <span>Eventos, entidad, usuario e IP.</span>
        </div>
        <b class="security-pill"><i class="fa-solid fa-list-check"></i> <?= e(count($audits)) ?></b>
      </header>
      <div class="security-audit-list">
        <?php foreach(array_slice($audits,0,6) as $a): ?>
          <div class="security-audit-line">
            <i class="fa-solid fa-circle-nodes"></i>
            <div>
              <b><?= e(ucfirst((string)$a['accion'])) ?></b>
              <small><?= e($a['entidad']) ?> · <?= e($a['usuario'] ?? 'Sistema') ?></small>
            </div>
            <span><?= e(fdt($a['creado_en'] ?? null)) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </article>

    <article class="security-card security-distribution-card">
      <header class="security-headline compact">
        <div>
          <h3>Distribución de roles</h3>
          <span>Balance de privilegios.</span>
        </div>
      </header>
      <div class="security-distribution-layout">
        <canvas id="securityRoleChart" data-labels='<?= e(json_encode($chartLabels, JSON_UNESCAPED_UNICODE)) ?>' data-values='<?= e(json_encode($chartValues)) ?>'></canvas>
        <div class="security-role-mini-list">
          <?php foreach(array_slice($roleCounts,0,4,true) as $role=>$count): ?>
            <div><b><?= e($count) ?></b><span><?= e($role) ?></span></div>
          <?php endforeach; ?>
        </div>
      </div>
    </article>

    <article class="security-card security-rules-card">
      <header class="security-headline compact">
        <div>
          <h3>Controles activos</h3>
          <span>Reglas técnicas del sistema.</span>
        </div>
      </header>
      <div class="security-rule-list">
        <div><i class="fa-solid fa-check"></i><b>RNF01</b><span>bcrypt antes de guardar contraseñas.</span></div>
        <div><i class="fa-solid fa-check"></i><b>RNF02</b><span>Destrucción automática por 15 min sin actividad.</span></div>
        <div><i class="fa-solid fa-check"></i><b>Roles</b><span>Bloqueo para usuarios sin rol oficial.</span></div>
        <div><i class="fa-solid fa-check"></i><b>Headers</b><span>DENY, nosniff y referrer policy activo.</span></div>
      </div>
    </article>
  </aside>
</div>
<script>
(function(){
  if(!window.Chart) return;
  const scoreEl=document.getElementById('securityScoreChart');
  if(scoreEl){
    const score=Number(scoreEl.dataset.score||0);
    new Chart(scoreEl,{type:'doughnut',data:{datasets:[{data:[score,Math.max(0,100-score)],borderWidth:0,cutout:'72%'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{enabled:false}}}});
  }
  const roleEl=document.getElementById('securityRoleChart');
  if(roleEl){
    let labels=[],values=[];
    try{labels=JSON.parse(roleEl.dataset.labels||'[]');values=JSON.parse(roleEl.dataset.values||'[]');}catch(e){}
    new Chart(roleEl,{type:'bar',data:{labels:labels.map(x=>String(x).replace(' de Alberca','').replace(' de Mantenimiento','')),datasets:[{data:values,borderRadius:10}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{font:{size:8,weight:'bold'}}},y:{display:false,beginAtZero:true}}}});
  }
})();
</script>
