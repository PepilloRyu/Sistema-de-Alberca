-- Migracion incremental para bases existentes del Sistema de Albercas B2E.
-- Si vas a instalar desde cero, importa database/schema.sql y no necesitas esta migracion.
USE albercas;

CREATE TABLE IF NOT EXISTS equipo_revisiones (
 idRevision BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 idEquipo BIGINT UNSIGNED NOT NULL,
 estado ENUM('operativo','revision','critico','fuera_servicio') NOT NULL,
 ultima_revision DATE NOT NULL,
 proxima_revision DATE NOT NULL,
 comentario VARCHAR(500),
 revisado_por INT UNSIGNED NULL,
 revisado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_revision_equipo FOREIGN KEY (idEquipo) REFERENCES equipos_alberca(idEquipo) ON DELETE CASCADE,
 CONSTRAINT fk_revision_usuario FOREIGN KEY (revisado_por) REFERENCES usuarios(idUsuario),
 INDEX idx_revision_equipo_fecha (idEquipo, revisado_en)
) ENGINE=InnoDB;

INSERT INTO equipo_revisiones (idEquipo,estado,ultima_revision,proxima_revision,comentario,revisado_por)
SELECT e.idEquipo,e.estado,COALESCE(e.ultima_revision,CURDATE()),COALESCE(e.proxima_revision,DATE_ADD(CURDATE(),INTERVAL 30 DAY)),'Migracion: revision inicial generada',4
FROM equipos_alberca e
WHERE NOT EXISTS (SELECT 1 FROM equipo_revisiones r WHERE r.idEquipo=e.idEquipo);

INSERT INTO auditoria_sistema (idUsuario,entidad,accion,detalle,ip,user_agent)
VALUES (1,'database','migracion_backend_real',JSON_OBJECT('archivo','20260610_backend_real.sql'),'127.0.0.1','migration');
