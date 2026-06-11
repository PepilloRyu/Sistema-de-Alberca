<?php
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
require dirname(__DIR__).'/app/helpers/functions.php';
if (PHP_SAPI !== 'cli') {
  http_response_code(403);
  exit("Ejecuta esta herramienta desde consola: php tools/crear_admin_inicial.php\n");
}
require ALBERCAS_ROOT.'/app/core/Database.php';
require ALBERCAS_ROOT.'/app/core/Model.php';

function prompt_line(string $label): string {
  echo $label;
  $line = fgets(STDIN);
  return trim((string)$line);
}

function prompt_secret(string $label): string {
  if (stripos(PHP_OS_FAMILY, 'Windows') === false) {
    echo $label;
    system('stty -echo');
    $line = fgets(STDIN);
    system('stty echo');
    echo PHP_EOL;
    return trim((string)$line);
  }
  return prompt_line($label);
}

function password_is_secure(string $password): bool {
  return strlen($password) >= 8
    && preg_match('/[A-Z]/', $password) === 1
    && preg_match('/[a-z]/', $password) === 1
    && preg_match('/\d/', $password) === 1;
}

$pdo = Database::connection();
if (!$pdo) {
  echo "ERROR: No hay conexión con MySQL.\n";
  echo "Detalle: ".(Database::error() ?: 'sin detalle')."\n";
  exit(1);
}

$existing = $pdo->query("SELECT COUNT(*) total FROM usuarios WHERE idRol=1 AND estado='activo'")->fetch();
if ((int)($existing['total'] ?? 0) > 0) {
  echo "Ya existe al menos un administrador activo. No se creó otro usuario.\n";
  exit(0);
}

echo "Crear administrador inicial real\n";
echo "Esta herramienta no usa datos demo ni contraseñas predefinidas.\n\n";
$name = prompt_line('Nombre completo: ');
$email = strtolower(prompt_line('Correo institucional: '));
$password = prompt_secret('Contraseña segura: ');
$confirm = prompt_secret('Confirmar contraseña: ');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo "ERROR: nombre o correo inválido.\n";
  exit(1);
}
if ($password !== $confirm) {
  echo "ERROR: las contraseñas no coinciden.\n";
  exit(1);
}
if (!password_is_secure($password)) {
  echo "ERROR: la contraseña debe tener mínimo 8 caracteres, mayúscula, minúscula y número.\n";
  exit(1);
}

$stmt = $pdo->prepare("SELECT idUsuario FROM usuarios WHERE LOWER(email)=LOWER(:email) LIMIT 1");
$stmt->execute(['email'=>$email]);
if ($stmt->fetch()) {
  echo "ERROR: ya existe un usuario con ese correo.\n";
  exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);
$pdo->beginTransaction();
try {
  $ins = $pdo->prepare("INSERT INTO usuarios(nombre,email,password_hash,idRol,estado,creado_en) VALUES(:nombre,:email,:hash,1,'activo',NOW())");
  $ins->execute(['nombre'=>$name,'email'=>$email,'hash'=>$hash]);
  $id = (int)$pdo->lastInsertId();
  $audit = $pdo->prepare("INSERT INTO auditoria_sistema(idUsuario,entidad,accion,detalle,ip,user_agent,creado_en) VALUES(:id,'usuarios','crear_admin_inicial',JSON_OBJECT('email',:email),'cli','tools/crear_admin_inicial.php',NOW())");
  $audit->execute(['id'=>$id,'email'=>$email]);
  $pdo->commit();
  echo "OK: administrador inicial creado.\n";
  echo "Correo: {$email}\n";
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  echo "ERROR: no se pudo crear el administrador.\n";
  echo $e->getMessage()."\n";
  exit(1);
}
