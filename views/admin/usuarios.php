<?php
$totalUsers = count($users);
$activeUsers = 0;
$pendingUsers = 0;
$inactiveUsers = 0;
$withoutRole = 0;
$recentAccess = null;
$roleTotals = [];
$roleInfo = [];
foreach ($roles as $r) {
  $rid = (int)($r['idRol'] ?? 0);
  $roleTotals[$rid] = 0;
  $roleInfo[$rid] = (string)($r['nombre'] ?? 'Rol');
}
foreach ($users as $u) {
  $estado = (string)($u['estado'] ?? 'pendiente');
  if ($estado === 'activo') $activeUsers++;
  elseif ($estado === 'inactivo') $inactiveUsers++;
  else $pendingUsers++;
  $rid = (int)($u['idRol'] ?? 0);
  if ($rid === 0) $withoutRole++;
  if ($rid > 0) $roleTotals[$rid] = ($roleTotals[$rid] ?? 0) + 1;
  if (!empty($u['ultimo_acceso'])) {
    $ts = strtotime((string)$u['ultimo_acceso']);
    if ($ts && ($recentAccess === null || $ts > $recentAccess)) $recentAccess = $ts;
  }
}
$approvalQueue = array_values(array_filter($users, fn($u) => ($u['estado'] ?? '') === 'pendiente' || empty($u['idRol'])));
$statusClass = fn($s) => match((string)$s) { 'activo' => 'success', 'pendiente' => 'warning', 'inactivo' => 'dark', default => 'info' };
$roleIcon = fn($id) => match((int)$id) { 1=>'fa-user-tie', 2=>'fa-water-ladder', 3=>'fa-broom', 4=>'fa-screwdriver-wrench', default=>'fa-user-clock' };
$roleShort = fn($name) => str_replace([' de Alberca',' de Mantenimiento','Personal de '], ['', '', ''], (string)$name);
?>
<div class="users-command-page" data-users-page>
  <section class="user-kpis-row">
    <article class="user-kpi-card aqua"><i class="fa-solid fa-users"></i><div><span>Total empleados</span><b><?= e($totalUsers) ?></b><small>registrados en el sistema</small></div></article>
    <article class="user-kpi-card success"><i class="fa-solid fa-user-check"></i><div><span>Activos</span><b><?= e($activeUsers) ?></b><small>con acceso autorizado</small></div></article>
    <article class="user-kpi-card warning"><i class="fa-solid fa-user-clock"></i><div><span>Pendientes</span><b><?= e($pendingUsers) ?></b><small>requieren aprobación</small></div></article>
    <article class="user-kpi-card violet"><i class="fa-solid fa-id-badge"></i><div><span>Sin rol</span><b><?= e($withoutRole) ?></b><small>bloqueados del panel</small></div></article>
    <article class="user-kpi-card dark"><i class="fa-solid fa-shield-halved"></i><div><span>Último acceso</span><b><?= e($recentAccess ? date('H:i', $recentAccess) : '—') ?></b><small><?= e($recentAccess ? date('d/m/Y', $recentAccess) : 'sin actividad') ?></small></div></article>
  </section>

  <section class="users-main-grid">
    <article class="glass-card users-directory-card">
      <div class="users-headline">
        <div>
          <h3>Directorio de usuarios</h3>
          <span>Aprueba empleados, cambia roles y controla el acceso interno B2E.</span>
        </div>
        <a class="btn btn-aqua btn-sm" href="<?= e(page_url('registro')) ?>"><i class="fa-solid fa-user-plus me-1"></i> Nuevo empleado</a>
      </div>

      <div class="users-toolbar">
        <label class="users-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" data-user-search placeholder="Buscar por nombre, correo o rol"></label>
        <div class="users-filters">
          <select data-user-status class="form-select form-select-sm">
            <option value="all">Todos los estados</option>
            <option value="pendiente">Pendientes</option>
            <option value="activo">Activos</option>
            <option value="inactivo">Inactivos</option>
          </select>
          <select data-user-role class="form-select form-select-sm">
            <option value="all">Todos los roles</option>
            <option value="0">Sin Rol</option>
            <?php foreach($roles as $r): ?><option value="<?= e($r['idRol']) ?>"><?= e($roleShort($r['nombre'])) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="users-count"><b data-user-visible><?= e($totalUsers) ?></b><span>/ <?= e($totalUsers) ?> visibles</span></div>
      </div>

      <div class="users-table-wrap">
        <table class="users-table">
          <thead><tr><th>Empleado</th><th>Rol actual</th><th>Estado</th><th>Actividad</th><th>Gestión rápida</th></tr></thead>
          <tbody>
          <?php foreach($users as $u):
            $rid = (int)($u['idRol'] ?? 0);
            $estado = (string)($u['estado'] ?? 'pendiente');
            $name = (string)($u['nombre'] ?? 'Empleado');
            $email = (string)($u['email'] ?? '');
            $initials = mb_strtoupper(mb_substr($name,0,1));
            $search = mb_strtolower($name.' '.$email.' '.($u['rol'] ?? 'Sin Rol').' '.$estado);
          ?>
            <tr data-user-row data-search="<?= e($search) ?>" data-status="<?= e($estado) ?>" data-role="<?= e($rid) ?>">
              <td data-label="Empleado">
                <div class="user-identity"><div class="user-avatar-sm"><?= e($initials) ?></div><div><b><?= e($name) ?></b><small><?= e($email) ?></small></div></div>
              </td>
              <td data-label="Rol actual"><span class="role-pill role-<?= e($rid ?: 'none') ?>"><i class="fa-solid <?= e($roleIcon($rid)) ?>"></i><?= e($rid ? $roleShort($u['rol'] ?? 'Rol') : 'Sin Rol') ?></span></td>
              <td data-label="Estado"><span class="status-pill <?= e($statusClass($estado)) ?>"><?= e($estado) ?></span></td>
              <td data-label="Actividad"><div class="activity-cell"><b><?= e(fdt($u['ultimo_acceso'] ?? null)) ?></b><small>Alta: <?= e(fdt($u['creado_en'] ?? null)) ?></small></div></td>
              <td data-label="Gestión rápida">
                <form class="user-action-form" method="POST" action="<?= e(page_url('admin-usuarios')) ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="idUsuario" value="<?= e($u['idUsuario']) ?>">
                  <select name="idRol" class="form-select form-select-sm" aria-label="Rol de <?= e($name) ?>">
                    <?php foreach($roles as $r): $selected = $rid === (int)$r['idRol']; ?>
                      <option value="<?= e($r['idRol']) ?>" <?= $selected ? 'selected' : '' ?>><?= e($roleShort($r['nombre'])) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <select name="estado" class="form-select form-select-sm" aria-label="Estado de <?= e($name) ?>">
                    <?php foreach(['activo','pendiente','inactivo'] as $st): ?>
                      <option value="<?= e($st) ?>" <?= $estado === $st ? 'selected' : '' ?>><?= e(ucfirst($st)) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn btn-aqua btn-sm" title="Guardar cambios"><i class="fa-solid fa-floppy-disk"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </article>

    <aside class="users-side-panel">
      <article class="glass-card approval-card">
        <div class="users-headline mini"><div><h3>Cola de aprobación</h3><span>RF02 · usuarios nuevos sin acceso al panel.</span></div><b><?= e(count($approvalQueue)) ?></b></div>
        <div class="approval-list">
          <?php if(!$approvalQueue): ?>
            <div class="empty-state"><i class="fa-solid fa-circle-check"></i><b>Sin pendientes</b><small>Todos los usuarios tienen rol y estado definido.</small></div>
          <?php endif; ?>
          <?php foreach(array_slice($approvalQueue,0,6) as $u): $name=(string)($u['nombre']??'Empleado'); ?>
            <form class="approval-item" method="POST" action="<?= e(page_url('admin-usuarios')) ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="idUsuario" value="<?= e($u['idUsuario']) ?>">
              <input type="hidden" name="estado" value="activo">
              <div class="user-avatar-sm pending"><?= e(mb_strtoupper(mb_substr($name,0,1))) ?></div>
              <div class="approval-copy"><b><?= e($name) ?></b><small><?= e($u['email'] ?? '') ?></small></div>
              <select name="idRol" class="form-select form-select-sm">
                <?php foreach($roles as $r): ?><option value="<?= e($r['idRol']) ?>"><?= e($roleShort($r['nombre'])) ?></option><?php endforeach; ?>
              </select>
              <button class="btn btn-aqua btn-sm">Aprobar</button>
            </form>
          <?php endforeach; ?>
        </div>
      </article>

      <article class="glass-card roles-overview-card">
        <div class="users-headline mini"><div><h3>Distribución por rol</h3><span>Acceso según idRol y redirección automática.</span></div></div>
        <div class="role-distribution">
          <?php foreach($roles as $r):
            $rid = (int)$r['idRol'];
            $count = (int)($roleTotals[$rid] ?? 0);
            $pctRole = pct($count, max(1,$totalUsers));
          ?>
            <div class="role-metric role-<?= e($rid) ?>"><i class="fa-solid <?= e($roleIcon($rid)) ?>"></i><div><b><?= e($roleShort($r['nombre'])) ?></b><small><?= e($count) ?> usuarios · <?= e($pctRole) ?>%</small><span><em style="width:<?= e($pctRole) ?>%"></em></span></div></div>
          <?php endforeach; ?>
          <div class="role-metric role-none"><i class="fa-solid fa-user-lock"></i><div><b>Sin Rol</b><small><?= e($withoutRole) ?> bloqueados</small><span><em style="width:<?= e(pct($withoutRole, max(1,$totalUsers))) ?>%"></em></span></div></div>
        </div>
      </article>


    </aside>
  </section>
</div>
<script>
(function(){
  const root=document.querySelector('[data-users-page]'); if(!root) return;
  const search=root.querySelector('[data-user-search]');
  const status=root.querySelector('[data-user-status]');
  const role=root.querySelector('[data-user-role]');
  const rows=[...root.querySelectorAll('[data-user-row]')];
  const visible=root.querySelector('[data-user-visible]');
  function apply(){
    const q=(search?.value||'').toLowerCase().trim();
    const st=status?.value||'all';
    const rl=role?.value||'all';
    let count=0;
    rows.forEach(row=>{
      const okQ=!q || (row.dataset.search||'').includes(q);
      const okSt=st==='all' || row.dataset.status===st;
      const okRl=rl==='all' || row.dataset.role===rl;
      const show=okQ&&okSt&&okRl;
      row.style.display=show?'':'none';
      if(show) count++;
    });
    if(visible) visible.textContent=count;
  }
  [search,status,role].forEach(el=>el&&el.addEventListener('input',apply));
  [status,role].forEach(el=>el&&el.addEventListener('change',apply));
  apply();
})();
</script>
