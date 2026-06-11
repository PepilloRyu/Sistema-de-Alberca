-- Migración incremental para bases existentes del Sistema de Albercas B2E.
-- No inserta datos demo: solo asegura estructura requerida por el backend.
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
