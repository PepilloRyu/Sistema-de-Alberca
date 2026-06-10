-- Migración incremental de auditoría backend.
-- Complementa 20260610_backend_real.sql con ajustes de operación segura.
USE albercas;

-- El cierre automático ya se ejecuta desde TicketModel; se elimina el evento si existía
-- para evitar dependencia de permisos EVENT/event_scheduler.
-- DROP EVENT IF EXISTS ev_cerrar_tickets_sin_seguimiento;

INSERT INTO auditoria_sistema (idUsuario,entidad,accion,detalle,ip,user_agent)
SELECT 1,'database','migracion_backend_audit_fixes',JSON_OBJECT('archivo','20260610_backend_audit_fixes.sql'),'127.0.0.1','migration'
WHERE EXISTS (SELECT 1 FROM usuarios WHERE idUsuario=1);
