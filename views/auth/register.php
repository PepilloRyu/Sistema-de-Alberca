<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><base href="<?= e(url('')) ?>"><title><?= e($pageTitle) ?></title><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="<?= asset('css/base.css') ?>?v=<?= e(asset_version('css/base.css')) ?>"><link rel="stylesheet" href="<?= asset('css/auth/auth.css') ?>?v=<?= e(asset_version('css/auth/auth.css')) ?>"><link rel="stylesheet" href="<?= asset('css/fixes/no-overlap.css') ?>?v=<?= e(asset_version('css/fixes/no-overlap.css')) ?>"></head><body class="auth-body"><section class="auth-card register-card"><div class="auth-visual"><div class="brand brand-auth"><span class="brand-mark">⌁</span><span><strong>Albercas</strong><small>Registro interno</small></span></div><h1>Alta de empleado</h1><p>Tu cuenta quedará como <b>Sin Rol</b> hasta que un administrador la apruebe.</p></div><form class="auth-form" method="POST" action="<?= e(page_url('do-register')) ?>"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><h2>Crear solicitud</h2><?php if($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?><label>Nombre completo</label><input class="form-control" name="nombre" required><label>Correo institucional</label><input class="form-control" type="email" name="email" required><label>Contraseña segura</label><input class="form-control" type="password" name="password" id="passwordInput" minlength="8" required><div class="password-strength" id="passwordStrength"><div class="password-strength-bar"><span></span></div><small>Mínimo 8 caracteres, mayúscula, minúscula y número.</small></div><label>Confirmar contraseña</label><input class="form-control" type="password" name="password_confirm" minlength="8" required><button class="btn btn-aqua w-100">Enviar solicitud</button><p class="auth-link"><a href="<?= e(page_url('login')) ?>">Volver al login</a></p></form></section><script>
(function(){
  const input=document.getElementById('passwordInput');
  const box=document.getElementById('passwordStrength');
  if(!input||!box)return;
  const bar=box.querySelector('span');
  const msg=box.querySelector('small');
  function render(){
    const v=input.value||'';
    const checks=[v.length>=8,/[A-Z]/.test(v),/[a-z]/.test(v),/[0-9]/.test(v)];
    const score=checks.filter(Boolean).length;
    box.dataset.score=String(score);
    if(bar)bar.style.width=(score*25)+'%';
    if(msg)msg.textContent=score===4?'Contraseña segura.':'Mínimo 8 caracteres, mayúscula, minúscula y número.';
  }
  input.addEventListener('input',render);
  render();
})();
</script></body></html>
