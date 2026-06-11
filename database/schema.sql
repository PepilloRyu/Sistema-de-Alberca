
CREATE DATABASE IF NOT EXISTS albercas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE albercas;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS auditoria_sistema, notificaciones, checklist_limpieza, turnos_limpieza, mantenimientos_programados,
 ticket_seguimientos, tickets_mantenimiento, alertas_alberca, calidad_agua_registros, aforo_movimientos, equipo_revisiones, equipos_alberca,
 albercas, catalogo_tareas_limpieza, catalogo_areas_limpieza, catalogo_tipos_mantenimiento, catalogo_estados_ticket,
 catalogo_prioridades, catalogo_tipos_incidencia, catalogo_estados_alberca, usuarios, roles, configuraciones_sistema;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE roles (
 idRol TINYINT UNSIGNED PRIMARY KEY,
 nombre VARCHAR(80) NOT NULL UNIQUE,
 descripcion VARCHAR(255),
 activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO roles VALUES
(1,'Administrador','Configura el sistema, aprueba usuarios y supervisa estadísticas generales.',1),
(2,'Encargado de Alberca','Supervisa aforo, seguridad, horarios y métricas.',1),
(3,'Personal de Limpieza','Mantenimiento higiénico de albercas y áreas comunes.',1),
(4,'Técnico de Mantenimiento','Atiende fallas mediante tickets FIFO.',1);

CREATE TABLE usuarios (
 idUsuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(140) NOT NULL,
 email VARCHAR(160) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 idRol TINYINT UNSIGNED NULL,
 estado ENUM('pendiente','activo','inactivo') NOT NULL DEFAULT 'pendiente',
 telefono VARCHAR(30),
 ultimo_acceso DATETIME NULL,
 creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 actualizado_en DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_usuarios_roles FOREIGN KEY (idRol) REFERENCES roles(idRol)
) ENGINE=InnoDB;

-- Sin usuarios demo: crea el primer administrador con tools/crear_admin_inicial.php.

CREATE TABLE catalogo_estados_alberca (
 idEstadoAlberca TINYINT UNSIGNED PRIMARY KEY,
 nombre VARCHAR(90) NOT NULL UNIQUE,
 clase_ui VARCHAR(30) NOT NULL,
 bloquea_aforo TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO catalogo_estados_alberca VALUES
(1,'Disponible','success',0),
(2,'En uso (profesional solamente)','info',0),
(3,'En limpieza','warning',1),
(4,'En mantenimiento','danger',1),
(5,'Cerrada','dark',1);

CREATE TABLE albercas (
 idAlberca TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL UNIQUE,
 capacidad_maxima SMALLINT UNSIGNED NOT NULL,
 ubicacion VARCHAR(160),
 uso_eventos TINYINT(1) NOT NULL DEFAULT 0,
 idEstadoAlberca TINYINT UNSIGNED NOT NULL DEFAULT 1,
 horario_apertura TIME NOT NULL DEFAULT '07:00:00',
 horario_cierre TIME NOT NULL DEFAULT '21:00:00',
 creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 actualizado_en DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_albercas_estado FOREIGN KEY (idEstadoAlberca) REFERENCES catalogo_estados_alberca(idEstadoAlberca),
 CONSTRAINT chk_capacidad CHECK (capacidad_maxima > 0)
) ENGINE=InnoDB;

INSERT INTO albercas (idAlberca,nombre,capacidad_maxima,ubicacion,uso_eventos,idEstadoAlberca) VALUES
(1,'Alberca principal',200,'Zona central',0,1),
(2,'Alberca familiar',150,'Zona familiar',0,1),
(3,'Alberca infantil',80,'Zona infantil',0,1),
(4,'Alberca vista al mar',90,'Terraza marina',0,1),
(5,'Alberca deportiva',100,'Zona deportiva',1,1);

CREATE TABLE catalogo_tipos_incidencia (
 idTipoIncidencia TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(90) NOT NULL UNIQUE,
 descripcion VARCHAR(255)
) ENGINE=InnoDB;

INSERT INTO catalogo_tipos_incidencia (nombre,descripcion) VALUES
('Bomba / filtro','Fallas en bombas, filtros, presión o recirculación'),
('Estructural','Grietas, azulejos, rejillas, escaleras o bordes'),
('Calidad de agua','Parámetros químicos fuera de rango'),
('Iluminación','Luminarias, cableado visible o cortos'),
('Seguridad','Riesgos a usuarios o personal'),
('Limpieza','Residuos, higiene o áreas comunes');

CREATE TABLE catalogo_prioridades (
 idPrioridad TINYINT UNSIGNED PRIMARY KEY,
 nombre VARCHAR(40) NOT NULL UNIQUE,
 nivel TINYINT UNSIGNED NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO catalogo_prioridades VALUES
(1,'Baja',1),(2,'Media',2),(3,'Alta',3),(4,'Crítica',4);

CREATE TABLE catalogo_estados_ticket (
 idEstadoTicket TINYINT UNSIGNED PRIMARY KEY,
 nombre VARCHAR(80) NOT NULL UNIQUE,
 es_final TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO catalogo_estados_ticket VALUES
(1,'Nuevo',0),(2,'Asignado',0),(3,'En proceso',0),(4,'Concluido',1),(5,'Cerrado automáticamente',1),(6,'Cancelado',1);

CREATE TABLE aforo_movimientos (
 idMovimiento BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 idAlberca TINYINT UNSIGNED NOT NULL,
 tipo_movimiento ENUM('entrada','salida') NOT NULL,
 cantidad SMALLINT UNSIGNED NOT NULL,
 registrado_por INT UNSIGNED NOT NULL,
 registrado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 observaciones VARCHAR(255),
 CONSTRAINT fk_aforo_alberca FOREIGN KEY (idAlberca) REFERENCES albercas(idAlberca),
 CONSTRAINT fk_aforo_usuario FOREIGN KEY (registrado_por) REFERENCES usuarios(idUsuario),
 INDEX idx_aforo_fecha (idAlberca,registrado_en)
) ENGINE=InnoDB;

CREATE TABLE calidad_agua_registros (
 idCalidadAgua BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 idAlberca TINYINT UNSIGNED NOT NULL,
 cloro_ppm DECIMAL(4,2) NOT NULL,
 ph DECIMAL(4,2) NOT NULL,
 temperatura_c DECIMAL(4,1) NOT NULL,
 observaciones VARCHAR(255),
 registrado_por INT UNSIGNED NOT NULL,
 registrado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_calidad_alberca FOREIGN KEY (idAlberca) REFERENCES albercas(idAlberca),
 CONSTRAINT fk_calidad_usuario FOREIGN KEY (registrado_por) REFERENCES usuarios(idUsuario),
 CONSTRAINT chk_ph CHECK (ph BETWEEN 0 AND 14),
 CONSTRAINT chk_cloro CHECK (cloro_ppm >= 0),
 INDEX idx_calidad_ultima (idAlberca,registrado_en)
) ENGINE=InnoDB;

CREATE TABLE alertas_alberca (
 idAlerta BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 idAlberca TINYINT UNSIGNED NOT NULL,
 titulo VARCHAR(140) NOT NULL,
 descripcion VARCHAR(255),
 nivel ENUM('baja','media','alta','critica') NOT NULL DEFAULT 'media',
 estado ENUM('abierta','atendida','cerrada') NOT NULL DEFAULT 'abierta',
 creada_por INT UNSIGNED NULL,
 creada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 cerrada_en DATETIME NULL,
 CONSTRAINT fk_alerta_alberca FOREIGN KEY (idAlberca) REFERENCES albercas(idAlberca),
 CONSTRAINT fk_alerta_usuario FOREIGN KEY (creada_por) REFERENCES usuarios(idUsuario),
 INDEX idx_alerta_estado (estado, nivel, creada_en)
) ENGINE=InnoDB;

CREATE TABLE tickets_mantenimiento (
 idTicket BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 folio VARCHAR(40) NOT NULL UNIQUE,
 idTipoIncidencia TINYINT UNSIGNED NOT NULL,
 idAlberca TINYINT UNSIGNED NOT NULL,
 descripcion TEXT NOT NULL,
 idPrioridad TINYINT UNSIGNED NOT NULL,
 idEstadoTicket TINYINT UNSIGNED NOT NULL DEFAULT 1,
 reportado_por INT UNSIGNED NOT NULL,
 asignado_a INT UNSIGNED NULL,
 creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 asignado_en DATETIME NULL,
 ultimo_seguimiento_en DATETIME NULL,
 actualizado_en DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
 cerrado_en DATETIME NULL,
 cierre_motivo VARCHAR(255),
 CONSTRAINT fk_ticket_tipo FOREIGN KEY (idTipoIncidencia) REFERENCES catalogo_tipos_incidencia(idTipoIncidencia),
 CONSTRAINT fk_ticket_alberca FOREIGN KEY (idAlberca) REFERENCES albercas(idAlberca),
 CONSTRAINT fk_ticket_prioridad FOREIGN KEY (idPrioridad) REFERENCES catalogo_prioridades(idPrioridad),
 CONSTRAINT fk_ticket_estado FOREIGN KEY (idEstadoTicket) REFERENCES catalogo_estados_ticket(idEstadoTicket),
 CONSTRAINT fk_ticket_reporta FOREIGN KEY (reportado_por) REFERENCES usuarios(idUsuario),
 CONSTRAINT fk_ticket_asignado FOREIGN KEY (asignado_a) REFERENCES usuarios(idUsuario),
 INDEX idx_ticket_fifo (idEstadoTicket,idPrioridad,creado_en),
 INDEX idx_ticket_caducidad (idEstadoTicket,creado_en,ultimo_seguimiento_en)
) ENGINE=InnoDB;

CREATE TABLE ticket_seguimientos (
 idSeguimiento BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 idTicket BIGINT UNSIGNED NOT NULL,
 idUsuario INT UNSIGNED NOT NULL,
 comentario TEXT NOT NULL,
 creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_seg_ticket FOREIGN KEY (idTicket) REFERENCES tickets_mantenimiento(idTicket) ON DELETE CASCADE,
 CONSTRAINT fk_seg_usuario FOREIGN KEY (idUsuario) REFERENCES usuarios(idUsuario)
) ENGINE=InnoDB;

CREATE TABLE catalogo_tipos_mantenimiento (
 idTipoMantenimiento TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO catalogo_tipos_mantenimiento (nombre) VALUES
('Preventivo'),('Correctivo'),('Inspección'),('Emergencia');

CREATE TABLE mantenimientos_programados (
 idMantenimiento BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 idAlberca TINYINT UNSIGNED NOT NULL,
 idTipoMantenimiento TINYINT UNSIGNED NOT NULL,
 asignado_a INT UNSIGNED NOT NULL,
 fecha_programada DATE NOT NULL,
 hora_inicio TIME NOT NULL,
 hora_fin TIME NOT NULL,
 estado ENUM('programado','en_proceso','concluido','cancelado') NOT NULL DEFAULT 'programado',
 descripcion VARCHAR(255),
 creado_por INT UNSIGNED NOT NULL,
 creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_mant_alberca FOREIGN KEY (idAlberca) REFERENCES albercas(idAlberca),
 CONSTRAINT fk_mant_tipo FOREIGN KEY (idTipoMantenimiento) REFERENCES catalogo_tipos_mantenimiento(idTipoMantenimiento),
 CONSTRAINT fk_mant_asignado FOREIGN KEY (asignado_a) REFERENCES usuarios(idUsuario),
 CONSTRAINT fk_mant_creador FOREIGN KEY (creado_por) REFERENCES usuarios(idUsuario),
 INDEX idx_mant_agenda (fecha_programada, hora_inicio, asignado_a, estado)
) ENGINE=InnoDB;

CREATE TABLE equipos_alberca (
 idEquipo BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 idAlberca TINYINT UNSIGNED NOT NULL,
 nombre VARCHAR(120) NOT NULL,
 tipo VARCHAR(80) NOT NULL,
 numero_serie VARCHAR(80),
 estado ENUM('operativo','revision','critico','fuera_servicio') NOT NULL DEFAULT 'operativo',
 ultima_revision DATE NULL,
 proxima_revision DATE NULL,
 CONSTRAINT fk_equipo_alberca FOREIGN KEY (idAlberca) REFERENCES albercas(idAlberca),
 INDEX idx_equipo_estado (estado, proxima_revision),
 INDEX idx_equipo_alberca (idAlberca, tipo)
) ENGINE=InnoDB;

CREATE TABLE equipo_revisiones (
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

CREATE TABLE catalogo_areas_limpieza (
 idAreaLimpieza TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(90) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO catalogo_areas_limpieza (nombre) VALUES
('Perímetro'),('Área común'),('Sanitarios'),('Camastros'),('Regaderas'),('Accesos');

CREATE TABLE catalogo_tareas_limpieza (
 idTareaLimpieza TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(120) NOT NULL UNIQUE,
 descripcion VARCHAR(255)
) ENGINE=InnoDB;

INSERT INTO catalogo_tareas_limpieza (nombre,descripcion) VALUES
('Retirar residuos visibles','Limpieza superficial de zona asignada'),
('Desinfección de barandales','Aplicar solución desinfectante'),
('Reponer insumos','Papel, jabón, gel y bolsas'),
('Acomodar mobiliario','Camastros y sombrillas'),
('Limpieza de regaderas','Lavado y desinfección'),
('Revisión de accesos','Retiro de obstáculos y señalética');

CREATE TABLE turnos_limpieza (
 idTurno BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 idUsuario INT UNSIGNED NOT NULL,
 idAlberca TINYINT UNSIGNED NOT NULL,
 idAreaLimpieza TINYINT UNSIGNED NOT NULL,
 fecha DATE NOT NULL,
 hora_inicio TIME NOT NULL,
 hora_fin TIME NOT NULL,
 estado ENUM('asignado','en_proceso','concluido','cancelado') NOT NULL DEFAULT 'asignado',
 creado_por INT UNSIGNED NOT NULL,
 creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_turno_usuario FOREIGN KEY (idUsuario) REFERENCES usuarios(idUsuario),
 CONSTRAINT fk_turno_alberca FOREIGN KEY (idAlberca) REFERENCES albercas(idAlberca),
 CONSTRAINT fk_turno_area FOREIGN KEY (idAreaLimpieza) REFERENCES catalogo_areas_limpieza(idAreaLimpieza),
 CONSTRAINT fk_turno_creador FOREIGN KEY (creado_por) REFERENCES usuarios(idUsuario),
 INDEX idx_turnos_agenda (fecha, hora_inicio, idUsuario)
) ENGINE=InnoDB;

CREATE TABLE checklist_limpieza (
 idChecklist BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 fecha DATE NOT NULL,
 idAlberca TINYINT UNSIGNED NOT NULL,
 idAreaLimpieza TINYINT UNSIGNED NOT NULL,
 idTareaLimpieza TINYINT UNSIGNED NOT NULL,
 asignado_a INT UNSIGNED NULL,
 hora_limite TIME NOT NULL,
 completado TINYINT(1) NOT NULL DEFAULT 0,
 completado_en DATETIME NULL,
 observaciones VARCHAR(255),
 creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 actualizado_en DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_chk_alberca FOREIGN KEY (idAlberca) REFERENCES albercas(idAlberca),
 CONSTRAINT fk_chk_area FOREIGN KEY (idAreaLimpieza) REFERENCES catalogo_areas_limpieza(idAreaLimpieza),
 CONSTRAINT fk_chk_tarea FOREIGN KEY (idTareaLimpieza) REFERENCES catalogo_tareas_limpieza(idTareaLimpieza),
 CONSTRAINT fk_chk_usuario FOREIGN KEY (asignado_a) REFERENCES usuarios(idUsuario),
 INDEX idx_checklist_dia (fecha,completado,hora_limite)
) ENGINE=InnoDB;

CREATE TABLE configuraciones_sistema (
 clave VARCHAR(80) PRIMARY KEY,
 valor VARCHAR(255) NOT NULL,
 descripcion VARCHAR(255)
) ENGINE=InnoDB;

INSERT INTO configuraciones_sistema VALUES
('session_timeout_seconds','900','Cierre automático por inactividad'),
('horario_apertura','07:00:00','Inicio operativo general'),
('horario_cierre','21:00:00','Cierre operativo general'),
('ticket_auto_close_hours','12','Caducidad de tickets sin seguimiento');

CREATE TABLE notificaciones (
 idNotificacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 idUsuario INT UNSIGNED NULL,
 titulo VARCHAR(140) NOT NULL,
 mensaje VARCHAR(255) NOT NULL,
 leida TINYINT(1) NOT NULL DEFAULT 0,
 creada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_notif_usuario FOREIGN KEY (idUsuario) REFERENCES usuarios(idUsuario)
) ENGINE=InnoDB;

CREATE TABLE auditoria_sistema (
 idAuditoria BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 idUsuario INT UNSIGNED NULL,
 entidad VARCHAR(80) NOT NULL,
 accion VARCHAR(80) NOT NULL,
 detalle JSON NULL,
 ip VARCHAR(45),
 user_agent VARCHAR(255),
 creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_audit_usuario FOREIGN KEY (idUsuario) REFERENCES usuarios(idUsuario)
) ENGINE=InnoDB;

-- Sin usuarios demo ni contraseñas predefinidas.
-- Crea el primer administrador real con:
--   php tools/crear_admin_inicial.php
