-- Limpieza de datos demo conocidos en bases existentes.
-- Úsala solo si importaste una versión anterior con datos de ejemplo.
USE albercas;

SET FOREIGN_KEY_CHECKS=0;

DELETE FROM ticket_seguimientos WHERE idTicket IN (
  SELECT idTicket FROM tickets_mantenimiento
  WHERE folio IN ('TK-20260609-0001','TK-20260609-0002','TK-20260608-AX401','TK-20260608-BF210','TK-20260607-CM090','TK-20260606-VM311','TK-20260605-INF77')
);
DELETE FROM tickets_mantenimiento
WHERE folio IN ('TK-20260609-0001','TK-20260609-0002','TK-20260608-AX401','TK-20260608-BF210','TK-20260607-CM090','TK-20260606-VM311','TK-20260605-INF77')
   OR descripcion IN ('Ruido inusual en bomba secundaria','Revisar rejilla lateral','Cambio de sello y prueba de presión en bomba DPT-01','Ajuste de dosificador de cloro y recalibración química','Rejilla lateral ajustada y asegurada','Reemplazo de luminaria perimetral','Ajuste de señalética y revisión de acceso infantil');

DELETE FROM checklist_limpieza WHERE observaciones IN ('Completo','Pendiente de cierre','Insumos al 50%','Completado');
DELETE FROM turnos_limpieza WHERE idUsuario IN (3) AND idAlberca IN (1,3);
DELETE FROM mantenimientos_programados WHERE descripcion IN ('Revisión de bomba secundaria','Inspección de filtros','Reparación de bomba secundaria y prueba de caudal','Retrolavado de filtro e inspección de válvulas','Revisión de calefacción y tablero eléctrico','Calibración de dosificador infantil');
DELETE FROM equipo_revisiones WHERE comentario IN ('Carga inicial de inventario tecnico','Migracion: revision inicial generada');
DELETE FROM equipos_alberca WHERE numero_serie IN ('FLT-PR-001','BMP-PR-001','DOS-PR-001','BMP-FM-001','FLT-FM-001','DOS-INF-001','BMP-INF-001','FLT-VM-001','CAL-VM-001','BMP-DPT-001','FLT-DPT-001');
DELETE FROM alertas_alberca WHERE titulo IN ('Filtro principal en revisión','Limpieza profunda en proceso','Aforo arriba del 70%');
DELETE FROM aforo_movimientos WHERE observaciones IS NULL AND registrado_por IN (2);
DELETE FROM calidad_agua_registros WHERE observaciones IN ('Dentro de rango','Lista para operación','Monitorear tarde','Fuera de rango por mantenimiento');
DELETE FROM notificaciones WHERE titulo IN ('Nuevo ticket FIFO','Ticket asignado','Turno de limpieza asignado','Mantenimiento asignado');
DELETE FROM auditoria_sistema WHERE accion IN ('seed_inicial','migracion_backend_real','migracion_backend_audit_fixes');
DELETE FROM usuarios WHERE email IN ('admin@albercas.com','encargado@albercas.com','limpieza@albercas.com','tecnico@albercas.com','pendiente@albercas.com','admin@albercas.local','encargado@albercas.local','limpieza@albercas.local','tecnico@albercas.local','pendiente@albercas.local')
   OR nombre IN ('Encargado Demo','Limpieza Demo','Técnico Demo','Empleado Pendiente');

SET FOREIGN_KEY_CHECKS=1;
