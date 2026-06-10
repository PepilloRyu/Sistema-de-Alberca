-- Sistema de Albercas B2E - estructura completa MySQL
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

INSERT INTO usuarios (nombre,email,password_hash,idRol,estado) VALUES
('Administrador General','admin@albercas.com','$2y$12$jhLN2PrZLKuNOenP5uCAKu64pcC4EIES5nyARnBqyHxXMSELrL01S',1,'activo'),
('Encargado Demo','encargado@albercas.com','$2y$12$jhLN2PrZLKuNOenP5uCAKu64pcC4EIES5nyARnBqyHxXMSELrL01S',2,'activo'),
('Limpieza Demo','limpieza@albercas.com','$2y$12$jhLN2PrZLKuNOenP5uCAKu64pcC4EIES5nyARnBqyHxXMSELrL01S',3,'activo'),
('Técnico Demo','tecnico@albercas.com','$2y$12$jhLN2PrZLKuNOenP5uCAKu64pcC4EIES5nyARnBqyHxXMSELrL01S',4,'activo'),
('Empleado Pendiente','pendiente@albercas.com','$2y$12$jhLN2PrZLKuNOenP5uCAKu64pcC4EIES5nyARnBqyHxXMSELrL01S',NULL,'pendiente');

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
INSERT INTO catalogo_tipos_mantenimiento (nombre) VALUES ('Preventivo'),('Correctivo'),('Inspección'),('Emergencia');

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
INSERT INTO catalogo_areas_limpieza (nombre) VALUES ('Perímetro'),('Área común'),('Sanitarios'),('Camastros'),('Regaderas'),('Accesos');

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

INSERT INTO calidad_agua_registros (idAlberca,cloro_ppm,ph,temperatura_c,observaciones,registrado_por) VALUES
(1,1.80,7.30,27.0,'Dentro de rango',2),
(2,1.60,7.40,28.0,'Dentro de rango',2),
(3,1.90,7.20,26.0,'Lista para operación',2),
(4,1.70,7.50,28.0,'Monitorear tarde',2),
(5,0.70,7.90,25.0,'Fuera de rango por mantenimiento',2);

INSERT INTO aforo_movimientos (idAlberca,tipo_movimiento,cantidad,registrado_por,registrado_en) VALUES
(1,'entrada',126,2,NOW()),(2,'entrada',112,2,NOW()),(4,'entrada',54,2,NOW()),(1,'salida',22,2,NOW());

INSERT INTO alertas_alberca (idAlberca,titulo,descripcion,nivel,creada_por) VALUES
(5,'Filtro principal en revisión','Equipo técnico revisando presión de bomba','alta',2),
(3,'Limpieza profunda en proceso','Personal de limpieza trabajando zona infantil','media',2),
(2,'Aforo arriba del 70%','Monitorear entradas antes del máximo','media',2);

INSERT INTO equipos_alberca (idAlberca,nombre,tipo,numero_serie,estado,ultima_revision,proxima_revision) VALUES
(1,'Filtro arena PR-01','Filtro','FLT-PR-001','operativo',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 30 DAY)),
(1,'Bomba principal PR-01','Bomba','BMP-PR-001','operativo',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 30 DAY)),
(1,'Clorador PR-01','Dosificador','DOS-PR-001','operativo',DATE_SUB(CURDATE(),INTERVAL 1 DAY),DATE_ADD(CURDATE(),INTERVAL 14 DAY)),
(2,'Bomba familiar FM-01','Bomba','BMP-FM-001','operativo',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 30 DAY)),
(2,'Filtro familiar FM-01','Filtro','FLT-FM-001','operativo',DATE_SUB(CURDATE(),INTERVAL 3 DAY),DATE_ADD(CURDATE(),INTERVAL 21 DAY)),
(3,'Clorador infantil INF-01','Dosificador','DOS-INF-001','revision',DATE_SUB(CURDATE(),INTERVAL 6 DAY),DATE_ADD(CURDATE(),INTERVAL 4 DAY)),
(3,'Bomba infantil INF-01','Bomba','BMP-INF-001','operativo',DATE_SUB(CURDATE(),INTERVAL 2 DAY),DATE_ADD(CURDATE(),INTERVAL 20 DAY)),
(4,'Filtro vista mar VM-01','Filtro','FLT-VM-001','operativo',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 30 DAY)),
(4,'Sistema calefacción VM-01','Calefacción','CAL-VM-001','revision',DATE_SUB(CURDATE(),INTERVAL 10 DAY),DATE_ADD(CURDATE(),INTERVAL 6 DAY)),
(5,'Bomba deportiva DPT-01','Bomba','BMP-DPT-001','critico',DATE_SUB(CURDATE(),INTERVAL 5 DAY),DATE_ADD(CURDATE(),INTERVAL 1 DAY)),
(5,'Filtro deportivo DPT-01','Filtro','FLT-DPT-001','operativo',DATE_SUB(CURDATE(),INTERVAL 4 DAY),DATE_ADD(CURDATE(),INTERVAL 18 DAY));

INSERT INTO equipo_revisiones (idEquipo,estado,ultima_revision,proxima_revision,comentario,revisado_por)
SELECT idEquipo,estado,COALESCE(ultima_revision,CURDATE()),COALESCE(proxima_revision,DATE_ADD(CURDATE(),INTERVAL 30 DAY)),'Carga inicial de inventario tecnico',4 FROM equipos_alberca;

INSERT INTO turnos_limpieza (idUsuario,idAlberca,idAreaLimpieza,fecha,hora_inicio,hora_fin,creado_por) VALUES
(3,1,1,CURDATE(),'07:00:00','13:00:00',1),(3,3,2,CURDATE(),'13:00:00','21:00:00',1);

INSERT INTO checklist_limpieza (fecha,idAlberca,idAreaLimpieza,idTareaLimpieza,asignado_a,hora_limite,completado,observaciones) VALUES
(CURDATE(),1,1,1,3,'10:00:00',1,'Completo'),
(CURDATE(),3,2,2,3,'12:30:00',0,'Pendiente de cierre'),
(CURDATE(),4,3,3,3,'13:00:00',0,'Insumos al 50%'),
(CURDATE(),2,4,4,3,'15:00:00',1,'Completado');

INSERT INTO mantenimientos_programados (idAlberca,idTipoMantenimiento,asignado_a,fecha_programada,hora_inicio,hora_fin,descripcion,creado_por) VALUES
(5,2,4,CURDATE(),'11:00:00','13:00:00','Revisión de bomba secundaria',1),
(1,1,4,DATE_ADD(CURDATE(),INTERVAL 1 DAY),'08:00:00','09:30:00','Inspección de filtros',1);

INSERT INTO mantenimientos_programados (idAlberca,idTipoMantenimiento,asignado_a,fecha_programada,hora_inicio,hora_fin,estado,descripcion,creado_por) VALUES
(5,2,4,DATE_SUB(CURDATE(),INTERVAL 1 DAY),'10:00:00','12:00:00','concluido','Reparación de bomba secundaria y prueba de caudal',1),
(1,1,4,DATE_SUB(CURDATE(),INTERVAL 2 DAY),'08:00:00','09:30:00','concluido','Retrolavado de filtro e inspección de válvulas',1),
(4,3,4,DATE_SUB(CURDATE(),INTERVAL 3 DAY),'13:00:00','14:00:00','concluido','Revisión de calefacción y tablero eléctrico',1),
(3,1,4,DATE_SUB(CURDATE(),INTERVAL 4 DAY),'09:00:00','10:00:00','concluido','Calibración de dosificador infantil',1);

INSERT INTO tickets_mantenimiento (folio,idTipoIncidencia,idAlberca,descripcion,idPrioridad,idEstadoTicket,reportado_por,asignado_a,creado_en) VALUES
('TK-20260609-0001',1,5,'Ruido inusual en bomba secundaria',3,1,2,NULL,DATE_SUB(NOW(),INTERVAL 55 MINUTE)),
('TK-20260609-0002',2,2,'Revisar rejilla lateral',2,3,2,4,DATE_SUB(NOW(),INTERVAL 38 MINUTE));

INSERT INTO tickets_mantenimiento (folio,idTipoIncidencia,idAlberca,descripcion,idPrioridad,idEstadoTicket,reportado_por,asignado_a,creado_en,asignado_en,ultimo_seguimiento_en,cerrado_en,cierre_motivo) VALUES
('TK-20260608-AX401',1,5,'Cambio de sello y prueba de presión en bomba DPT-01',4,4,2,4,DATE_SUB(NOW(),INTERVAL 29 HOUR),DATE_SUB(NOW(),INTERVAL 28 HOUR),DATE_SUB(NOW(),INTERVAL 26 HOUR),DATE_SUB(NOW(),INTERVAL 25 HOUR),'Reparación concluida y validada'),
('TK-20260608-BF210',3,1,'Ajuste de dosificador de cloro y recalibración química',3,4,2,4,DATE_SUB(NOW(),INTERVAL 51 HOUR),DATE_SUB(NOW(),INTERVAL 50 HOUR),DATE_SUB(NOW(),INTERVAL 49 HOUR),DATE_SUB(NOW(),INTERVAL 48 HOUR),'Lecturas en rango'),
('TK-20260607-CM090',2,2,'Rejilla lateral ajustada y asegurada',2,4,3,4,DATE_SUB(NOW(),INTERVAL 74 HOUR),DATE_SUB(NOW(),INTERVAL 73 HOUR),DATE_SUB(NOW(),INTERVAL 72 HOUR),DATE_SUB(NOW(),INTERVAL 71 HOUR),'Zona segura'),
('TK-20260606-VM311',4,4,'Reemplazo de luminaria perimetral',2,4,2,4,DATE_SUB(NOW(),INTERVAL 96 HOUR),DATE_SUB(NOW(),INTERVAL 95 HOUR),DATE_SUB(NOW(),INTERVAL 94 HOUR),DATE_SUB(NOW(),INTERVAL 93 HOUR),'Iluminación operativa'),
('TK-20260605-INF77',5,3,'Ajuste de señalética y revisión de acceso infantil',1,4,3,4,DATE_SUB(NOW(),INTERVAL 121 HOUR),DATE_SUB(NOW(),INTERVAL 120 HOUR),DATE_SUB(NOW(),INTERVAL 119 HOUR),DATE_SUB(NOW(),INTERVAL 118 HOUR),'Acceso liberado');

INSERT INTO ticket_seguimientos (idTicket,idUsuario,comentario,creado_en)
SELECT idTicket,4,'Diagnóstico inicial y aislamiento de área.',DATE_ADD(creado_en,INTERVAL 25 MINUTE) FROM tickets_mantenimiento WHERE folio IN ('TK-20260608-AX401','TK-20260608-BF210','TK-20260607-CM090','TK-20260606-VM311','TK-20260605-INF77');
INSERT INTO ticket_seguimientos (idTicket,idUsuario,comentario,creado_en)
SELECT idTicket,4,'Corrección aplicada y prueba operativa final.',COALESCE(cerrado_en,DATE_ADD(creado_en,INTERVAL 90 MINUTE)) FROM tickets_mantenimiento WHERE folio IN ('TK-20260608-AX401','TK-20260608-BF210','TK-20260607-CM090','TK-20260606-VM311','TK-20260605-INF77');

-- Cierre automático de tickets:
-- La aplicación ejecuta esta regla desde TicketModel cada vez que se consulta la cola,
-- para no depender de permisos EVENT ni de event_scheduler en XAMPP/hosting.
-- Si quieres usar un evento MySQL adicional, puedes crear uno equivalente manualmente.

INSERT INTO auditoria_sistema (idUsuario,entidad,accion,detalle,ip,user_agent) VALUES
(1,'database','seed_inicial',JSON_OBJECT('version','backend_db_real_v1'),'127.0.0.1','schema.sql');

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