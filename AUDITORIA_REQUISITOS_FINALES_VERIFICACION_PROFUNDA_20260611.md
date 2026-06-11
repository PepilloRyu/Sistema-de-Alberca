# Auditoría profunda contra requisitos_FINALES.docx

## Resultado ejecutivo

El proyecto fue verificado contra `requisitos_FINALES.docx` y queda alineado al alcance activo del documento: usuarios/roles, sesión, aforo, calidad del agua, alertas, tickets FIFO, mantenimiento, equipos, limpieza, reportes, notificaciones, auditoría, seguridad y arquitectura MVC.

Se corrigieron dos hallazgos encontrados durante la revisión:

1. `database/schema.sql` tenía una fila duplicada en `catalogo_tareas_limpieza` (`Desinfección de barandales`). Esa duplicidad rompía la importación por la restricción `UNIQUE`. Se dejó una sola fila.
2. El ZIP incluía `.git/`. Se eliminó del paquete final para no entregar metadatos internos del repositorio.

También se dejó el `schema.sql` sin usuarios demo precargados. El primer administrador debe crearse con `php tools/crear_admin_inicial.php`, manteniendo el flujo real de usuarios y evitando contraseñas predefinidas en la instalación.

## Pruebas ejecutadas

| Prueba | Resultado |
|---|---:|
| PHP lint (`php -l`) | 62 archivos PHP sin errores |
| Rutas GET renderizadas con sesión simulada | 25 vistas principales + auth/sin rol sin fatal errors |
| Rutas POST con CSRF válido y datos mínimos | 13 acciones sin fatal errors |
| CSS modular | 29 archivos CSS con llaves balanceadas |
| Esquema SQL | 23 tablas detectadas; sin `INSERT INTO usuarios`; sin duplicado de tarea limpieza |
| Sidebars por rol | Solo rutas necesarias por rol |
| Empaquetado | ZIP sin `.git`; `unzip -t` sin errores |

> Limitación: en este entorno no está disponible `pdo_mysql` ni un servidor MySQL/XAMPP, por lo que la auditoría de ejecución real de consultas debe completarse localmente con `php tools/auditoria_backend.php` y `php tools/diagnostico_login.php`.

## Sidebars verificados

### Administrador
Pantallas visibles: Dashboard, Usuarios y roles, Albercas, Catálogos, Turnos limpieza, Mantenimiento, Reportes, Seguridad.

Justificación: cubre configuración, activación/asignación de usuarios, supervisión estadística, albercas, limpieza, mantenimiento, reportes, notificaciones/auditoría y seguridad.

### Encargado de Alberca
Pantallas visibles: Dashboard, Aforo, Calidad del agua, Alertas, Incidencias, Horarios.

Justificación: cubre supervisión de uso, seguridad/aforo, horarios, calidad del agua y registro de incidencias.

### Personal de Limpieza
Pantallas visibles: Dashboard, Turnos, Checklist diario, Reportar incidencia.

Justificación: cubre turnos, checklist diario y reporte de incidencias desde limpieza.

### Técnico de Mantenimiento
Pantallas visibles: Dashboard, Tickets FIFO, Agenda, Equipos.

Justificación: cubre tickets FIFO, seguimientos/cierre, mantenimientos programados y revisión de equipos.

## Matriz de requerimientos funcionales

| Código | Estado auditado | Evidencia |
|---|---|---|
| RF01 | Cumple | `AuthController::doRegister`, `UsuarioModel::createPending`, `views/auth/register.php` |
| RF02 | Cumple | Registro crea `idRol=NULL`, `estado='pendiente'`; `sin-rol` bloquea acceso operativo |
| RF03 | Cumple | `AuthController::doLogin`, `logout`, `default_route_for_role()` |
| RF04 | Cumple | `OperacionModel::kpis`, dashboards por rol |
| RF05 | Cumple | `OperacionModel::saveQuality`, `OperacionModel::quality`, vista `encargado/calidad.php` |
| RF06 | Cumple | `OperacionModel::alerts`, dashboard y alertas |
| RF07 | Cumple | `TicketModel::create`, vistas de incidencias encargado/limpieza |
| RF08 | Cumple | `MantenimientoModel::program`, admin/mantenimiento, agenda técnico |
| RF09 | Cumple | `LimpiezaModel::assignShift`, admin/limpieza |
| RF10 | Cumple | `LimpiezaModel::checklist`, `complete`, vista checklist |
| RF11 | Cumple | `AlbercaModel::status`, catálogo de estados y admin/albercas |
| RF12 | No implementado intencional | El documento lo marca como no modelado; no hay tabla/ruta de zonas internas |
| RF13 | No implementado intencional | El documento lo marca no implementado; no hay módulo salvavidas |
| RF14 | No implementado intencional | El documento lo marca no implementado; no hay módulo actividades |
| RF15 | No implementado intencional | El documento lo marca no implementado; no hay cobros/precios |
| RF16 | Cumple | Folio, tipo, área/evidencia/origen en `TicketModel::descriptionWithContext` |
| RF17 | Cumple | `TicketModel::assign`, técnico toma ticket FIFO |
| RF18 | Cumple | `TicketModel::follow` permite estados finales con fecha/motivo |
| RF19 | No implementado intencional | El documento lo marca no implementado; no hay carga de archivos/fotos |
| RF20 | Parcial como documento | Inventario cubre equipos de mantenimiento mediante `equipos_alberca` |
| RF21 | No implementado intencional | El documento lo marca no implementado; turnos son manuales |
| RF22 | No implementado intencional | El documento lo marca no implementado; no hay regla por tamaño |
| RF23 | Cumple | `AdminController::reportes` consolida pH/cloro, incidencias, limpieza y mantenimiento |
| RF24 | No implementado intencional | El documento lo marca no implementado; mobiliario solo aparece como tarea de limpieza |
| RF25 | Cumple | `OperacionModel::aforo` rechaza capacidad excedida |
| RF26 | Cumple | `catalogo_estados_alberca.bloquea_aforo` + validación en `aforo()` |
| RF27 | Cumple | `aforo()` valida horario apertura/cierre |
| RF28 | Cumple | `aforo()` rechaza salidas mayores a ocupación actual |
| RF29 | Cumple | `aforo()` crea alerta de aforo alto >= 85% sin duplicar por día |
| RF30 | Cumple | `TicketModel::nextFolio` genera `TK-AAAAMMDD-XXXX` |
| RF31 | Cumple | `TicketModel::closeExpiredTickets` autocierra por umbral configurable |
| RF32 | Cumple | `TicketModel::create` inserta notificación al crear ticket |
| RF33 | Cumple | `descriptionWithContext` agrega área, evidencia y origen |
| RF34 | Cumple | Validación de usuario activo y catálogos en modelos de operación |
| RF35 | Cumple | `flash()` y renderizado de `flash-stack` en layout |
| RF36 | Cumple | Chart.js cargado en layout y usado en dashboards/reportes |
| RF37 | Cumple | `window.ALBERCAS_SESSION_TIMEOUT` y lógica de sesión en `js/app.js` |
| RF38 | Cumple | Validación de contraseña en backend y medidor en vivo en registro |

## Matriz de requerimientos no funcionales

| Código | Estado auditado | Evidencia |
|---|---|---|
| RNF01 | Cumple | `password_hash`, `password_verify`, CSRF en `Seguridad` |
| RNF02 | Cumple | Timeout 900 segundos en config/BD + `Seguridad::enforceIdle` |
| RNF03 | Cumple | `controllers`, `models`, `views`, `app/core` |
| RNF04 | Cumple | Dashboards operativos B2E, sin marketing/ventas |
| RNF05 | Cumple | Consultas de aforo/calidad consultan datos actuales de BD |
| RNF06 | Parcial como documento | Horario configurado y validado; disponibilidad operativa depende del hosting |
| RNF07 | Parcial como documento | UI de lectura rápida implementada; medición formal no automatizada |
| RNF08 | Parcial como documento | Bootstrap + CSS responsive modular; validación formal depende de navegador/dispositivo |
| RNF09 | Cumple | `auditoria_sistema` + `Model::audit` |
| RNF10 | Parcial como documento | Catálogos existen; altas administrativas completas no están en todos los catálogos |
| RNF11 | Cumple | Tabla `notificaciones` y escrituras en tickets/turnos/mantenimientos |
| RNF12 | Cumple | Token por sesión y HTTP 419 en CSRF inválido |
| RNF13 | Cumple | Salidas revisadas con `e()` / `htmlspecialchars` |
| RNF14 | Cumple | PDO preparado, sin emulación, parámetros enlazados |
| RNF15 | Cumple | Cabeceras en `index.php` |
| RNF16 | Cumple | Cookies HttpOnly, SameSite=Lax, Secure si HTTPS |
| RNF17 | Cumple | Rutas protegidas por rol en `index.php` |
| RNF18 | Cumple | `Seguridad::securePassword` |
| RNF19 | Cumple | Acceso operativo exige `usuario_estado='activo'` |
| RNF20 | Cumple | Transacciones en aforo, tickets, limpieza, mantenimiento, equipos |
| RNF21 | Cumple | `FOR UPDATE` en registro de aforo |
| RNF22 | Cumple | Auditoría guarda usuario, entidad, acción, IP y user agent |
| RNF23 | Cumple | Logs `db.log`, `sql.log`; mensajes al usuario son genéricos |
| RNF24 | Cumple | `.htaccess` bloquea índices y archivos `.sql`, `.log`, `.env`, ocultos |
| RNF25 | Cumple | `Database::connection` controla fallo y modo `strict` configurable |

## Matriz de reglas de negocio

| Código | Estado auditado | Evidencia |
|---|---|---|
| RN01 | Cumple | Albercas 07:00–21:00 en esquema y validación de aforo |
| RN02 | Cumple | Capacidades oficiales en `albercas` |
| RN03 | Cumple | 5 estados en `catalogo_estados_alberca` |
| RN04 | Cumple | `ticket_auto_close_hours=12` + `closeExpiredTickets()` |
| RN05 | Cumple | Cola ordenada por prioridad y fecha de creación |
| RN06 | No implementado intencional | Documento lo marca como no implementado |
| RN07 | No implementado intencional | Documento lo marca como no implementado |
| RN08 | No implementado intencional | Documento lo marca como no implementado |
| RN09 | No implementado intencional | Documento lo marca como no implementado |
| RN13 | No implementado intencional | Documento lo marca como no implementado; sistema es B2E interno |
| RN14 | Cumple | `albercas.uso_eventos=1` para deportiva |
| RN15 | Parcial como documento | Inventario limitado a equipos de mantenimiento |
| RN16 | No implementado intencional | Documento lo marca como no implementado |
| RN17 | Cumple | Folio, descripción con contexto y seguimientos |
| RN18 | Cumple | Aforo valida máximo de capacidad |
| RN19 | Cumple | Estados con `bloquea_aforo=1` impiden entradas |
| RN20 | Cumple | Aforo valida horario operativo |
| RN21 | Cumple | Umbral 85% crea alerta de aforo alto |
| RN22 | Cumple | Solo usuarios activos operan modelos críticos |
| RN23 | Cumple | Complejidad mínima de contraseña |
| RN24 | Cumple | `usuarios.email UNIQUE` + validación de duplicado |

## Requerimientos técnicos

| Código | Estado auditado | Evidencia |
|---|---|---|
| RT01 | Cumple | Archivos PHP principales con `declare(strict_types=1)` |
| RT02 | Cumple estático | Configuración MySQL/MariaDB con PDO utf8mb4; prueba real requiere XAMPP |
| RT03 | Cumple | MVC ligero con core propio |
| RT04 | Cumple | `index.php` como front controller y rutas `?page=` |
| RT05 | Cumple | `spl_autoload_register` para controllers/models |
| RT06 | Cumple | `Database`, `Model`, transacciones y auditoría centralizadas |
| RT07 | Cumple | Catálogos en tablas parametrizables |
| RT08 | Cumple | `config/` + `configuraciones_sistema` |
| RT09 | Cumple | `America/Mexico_City` y `SET time_zone='-06:00'` |
| RT10 | Cumple | Bootstrap, Chart.js y JS vanilla |
| RT11 | Cumple | Índices para tickets, aforo y calidad |
| RT12 | Cumple | FK y CHECK en esquema |
| RT13 | Cumple | Migraciones versionadas y semilla de catálogos/base operativa |
| RT14 | Cumple | Herramientas `tools/*.php` |
| RT15 | Cumple | Logs controlados y respuesta 404 básica |

## Conclusión

El sistema cumple el alcance implementado definido por `requisitos_FINALES.docx`. Las funcionalidades marcadas en el documento como no implementadas o propuestas futuras permanecen fuera de rutas, sidebars y tablas activas para evitar crecimiento fuera del alcance. El paquete final corrige los problemas encontrados en el esquema y queda listo para validación local con MySQL/XAMPP.
