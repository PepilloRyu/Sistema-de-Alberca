USE albercas;

-- Contraseña demo para todos: Admin123!
-- Este valor NO es texto plano: es bcrypt generado con password_hash('Admin123!', PASSWORD_BCRYPT, ['cost'=>12]).
UPDATE usuarios
SET password_hash = '$2y$12$jhLN2PrZLKuNOenP5uCAKu64pcC4EIES5nyARnBqyHxXMSELrL01S',
    estado = CASE WHEN email = 'pendiente@albercas.local' THEN 'pendiente' ELSE 'activo' END,
    idRol = CASE email
      WHEN 'admin@albercas.local' THEN 1
      WHEN 'encargado@albercas.local' THEN 2
      WHEN 'limpieza@albercas.local' THEN 3
      WHEN 'tecnico@albercas.local' THEN 4
      WHEN 'pendiente@albercas.local' THEN NULL
      ELSE idRol
    END
WHERE email IN (
  'admin@albercas.local',
  'encargado@albercas.local',
  'limpieza@albercas.local',
  'tecnico@albercas.local',
  'pendiente@albercas.local'
);
