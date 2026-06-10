<?php $user=current_user(); ?>
<header class="topbar">
  <div class="topbar-title">
    <div class="eyebrow">Operación interna · 07:00 - 21:00</div>
    <h1><?= e($pageTitle) ?></h1>
  </div>
  <div class="topbar-actions">
    <div class="session-pill"><i class="fa-regular fa-clock"></i><span id="idleCountdown">15:00</span></div>
    <div class="user-chip"><div class="avatar"><?= e(mb_strtoupper(mb_substr($user['nombre'],0,1))) ?></div><div><strong><?= e($user['nombre']) ?></strong><small><?= e($user['rol']) ?></small></div></div>
    <a class="logout-topbar" href="<?= e(page_url('logout')) ?>" title="Cerrar sesión">
      <i class="fa-solid fa-right-from-bracket"></i><span>Cerrar sesión</span>
    </a>
  </div>
</header>
