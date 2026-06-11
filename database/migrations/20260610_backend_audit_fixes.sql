-- Migración incremental de auditoría backend.
-- No inserta datos demo ni registros de auditoría artificiales.
USE albercas;

-- El cierre automático se ejecuta desde TicketModel para no depender de permisos EVENT/event_scheduler.
-- DROP EVENT IF EXISTS ev_cerrar_tickets_sin_seguimiento;
