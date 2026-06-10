# Auditoría backend y conexión a base de datos

Fecha de auditoría: 2026-06-10  
Proyecto auditado: **Sistema de Albercas B2E Control**  
Fuente usada: ZIP entregado en esta conversación: `Sistema-de-Alberca.zip`

## Resultado ejecutivo

El proyecto mantiene una arquitectura PHP MVC ligera con conexión por PDO/MySQL y rutas centralizadas en `index.php`. Se revisaron controladores, modelos, vistas, formularios POST, esquema SQL, migraciones y herramientas internas.

Resultado después de correcciones:

- Sintaxis PHP completa: **OK**.
- Render de rutas principales por rol: **OK** sin errores fatales, warnings ni notices en prueba CLI.
- Formularios POST principales con campos incompletos: **OK**, ya no generan warnings por índices faltantes.
- Mapa modelo-tabla: **OK** contra `database/schema.sql` actualizado.
- Conexión real MySQL desde este entorno: **no ejecutada**, porque el PHP CLI disponible no tiene `pdo_mysql` y no hay servidor MySQL/MariaDB local. Se agregó `tools/auditoria_backend.php` para validarlo directamente en XAMPP.

## Correcciones aplicadas

### 1. Persistencia y validación de operaciones que antes podían reportar éxito falso

Se agregó `execAffected()` en `app/core/Model.php` para distinguir entre “SQL ejecutado” y “registro realmente afectado”.

Archivos corregidos:

- `app/core/Model.php`
- `models/AlbercaModel.php`
- `models/LimpiezaModel.php`
- `models/UsuarioModel.php`

Impacto:

- Cambiar estado de alberca ya no registra éxito si el `idAlberca` no existe.
- Completar checklist ya no registra éxito si la tarea no existe, ya está completada o no pertenece al usuario.
- Cambiar rol/estado de usuario ya no registra éxito sobre usuarios inexistentes.
- Se mantiene como válido guardar el mismo estado/rol cuando el registro sí existe.

### 2. Protección contra bloqueo accidental del último administrador

Se corrigió `models/UsuarioModel.php`.

Reglas nuevas:

- Un administrador no puede degradarse o desactivarse a sí mismo.
- Si solo queda un administrador activo, el sistema no permite convertirlo en no-admin o inactivo.
- La aprobación de usuarios nuevos sigue funcionando desde `admin-usuarios`.

Riesgo corregido: bloqueo total del panel administrativo.

### 3. Validación de llaves foráneas antes de insertar

Se reforzaron validaciones en:

- `models/LimpiezaModel.php`
- `models/MantenimientoModel.php`
- `models/OperacionModel.php`
- `models/TicketModel.php`

Ahora se valida antes de insertar:

- Que empleado de limpieza exista, esté activo y tenga `idRol = 3`.
- Que técnico exista, esté activo y tenga `idRol = 4`.
- Que alberca, área, tipo de mantenimiento, tipo de incidencia y prioridad existan.
- Que el usuario que registra aforo/calidad/ticket esté activo.

Riesgo corregido: errores SQL por FK y mensajes genéricos cuando el formulario enviaba IDs inválidos.

### 4. Control de aforo más seguro ante concurrencia

Se reescribió `OperacionModel::aforo()`.

Antes:

- Calculaba ocupación antes de entrar a transacción.
- Dos peticiones simultáneas podían leer la misma ocupación y superar capacidad.

Ahora:

- Lee la alberca dentro de transacción con `FOR UPDATE`.
- Recalcula ocupación dentro de la transacción.
- Valida horario, estado bloqueante, capacidad y salidas negativas antes de insertar.
- Genera alerta de aforo alto dentro de la misma transacción.

Riesgo corregido: sobrecupo por concurrencia.

### 5. Cierre automático de tickets sin depender de eventos MySQL

Se agregó cierre automático en `models/TicketModel.php`.

Antes:

- `schema.sql` intentaba crear un evento MySQL `ev_cerrar_tickets_sin_seguimiento`.
- Eso depende de permisos `EVENT` y de `event_scheduler`, que suelen fallar en hosting o XAMPP mal configurado.

Ahora:

- `TicketModel` ejecuta la regla al consultar cola, métricas, historial o reportes.
- Usa `configuraciones_sistema.ticket_auto_close_hours`; por defecto 12 horas.
- El evento MySQL quedó solo como referencia opcional comentada.

Riesgo corregido: tickets vencidos que nunca cerraban por depender de configuración externa de MySQL.

### 6. Correcciones de vistas conectadas a columnas reales

Archivo corregido:

- `views/admin/albercas.php`

Errores corregidos:

- Se usaba `codigo` para tickets, pero la tabla/modelo usan `folio`.
- Se usaba `programado_para`, pero la agenda real usa `fecha_programada` y `hora_inicio`.

Impacto: el panel de riesgos de albercas ya muestra el folio y la fecha/hora reales.

### 7. Control de POST malformados

Archivo corregido:

- `controllers/EncargadoController.php`

Antes, `encargado-aforo` leía directamente:

- `$_POST['idAlberca']`
- `$_POST['cantidad']`

Ahora usa valores seguros con `??`, evitando warnings si el POST llega incompleto.

### 8. Validación estricta de horas

Archivos corregidos:

- `models/LimpiezaModel.php`
- `models/MantenimientoModel.php`

Antes, `DateTime::createFromFormat()` podía normalizar horas inválidas como `25:00` a `01:00` con warning interno.

Ahora se revisan errores/warnings de parseo y formato exacto.

### 9. Compatibilidad cuando `mbstring` no está activo

Archivo corregido:

- `app/helpers/functions.php`

Se agregó polyfill para:

- `mb_strimwidth()`

El proyecto ya tenía polyfills para `mb_strtolower`, `mb_strtoupper` y `mb_substr`. Esto evita fallos en vistas de mantenimiento si `mbstring` no está habilitado.

### 10. Herramientas internas restringidas a localhost/CLI

Archivos corregidos:

- `hash.php`
- `tools/diagnostico_login.php`

Ambos quedan bloqueados fuera de CLI o `localhost`.

Riesgo corregido: exposición accidental de hashes, datos de diagnóstico o SQL de demo en servidor público.

### 11. Herramienta nueva de auditoría local

Archivo agregado:

- `tools/auditoria_backend.php`

Valida:

- Versión PHP.
- Extensión `pdo_mysql`.
- Extensión `mbstring` o polyfills.
- Conexión MySQL configurada.
- Existencia de tablas críticas.
- Existencia de columnas esperadas por los modelos.
- Conteos básicos por tabla.

Uso recomendado desde la carpeta del proyecto:

```bash
php tools/auditoria_backend.php
```

O abrir desde `localhost` en XAMPP.

## Mapa de conexión formulario -> controlador -> modelo -> tabla

| Módulo | Vista/Formulario | Controlador | Modelo | Tablas principales |
|---|---|---|---|---|
| Login | `views/auth/login.php` | `AuthController::doLogin` | `UsuarioModel` | `usuarios`, `roles`, `auditoria_sistema` |
| Registro | `views/auth/register.php` | `AuthController::doRegister` | `UsuarioModel` | `usuarios`, `auditoria_sistema` |
| Usuarios | `views/admin/usuarios.php` | `AdminController::usuarios` | `UsuarioModel` | `usuarios`, `roles`, `auditoria_sistema` |
| Albercas | `views/admin/albercas.php` | `AdminController::albercas` | `AlbercaModel` | `albercas`, `catalogo_estados_alberca`, `auditoria_sistema` |
| Aforo | `views/encargado/aforo.php` | `EncargadoController::aforo` | `OperacionModel` | `aforo_movimientos`, `albercas`, `alertas_alberca`, `auditoria_sistema` |
| Calidad agua | `views/encargado/calidad.php` | `EncargadoController::calidad` | `OperacionModel` | `calidad_agua_registros`, `alertas_alberca`, `auditoria_sistema` |
| Incidencias encargado | `views/encargado/incidencias.php` | `EncargadoController::incidencias` | `TicketModel` | `tickets_mantenimiento`, `notificaciones`, `auditoria_sistema` |
| Turnos limpieza | `views/admin/limpieza.php` | `AdminController::limpieza` | `LimpiezaModel` | `turnos_limpieza`, `checklist_limpieza`, `notificaciones`, `auditoria_sistema` |
| Checklist limpieza | `views/limpieza/checklist.php` | `LimpiezaController::checklist` | `LimpiezaModel` | `checklist_limpieza`, `auditoria_sistema` |
| Incidencias limpieza | `views/limpieza/incidencias.php` | `LimpiezaController::incidencias` | `TicketModel` | `tickets_mantenimiento`, `notificaciones`, `auditoria_sistema` |
| Programar mantenimiento | `views/admin/mantenimiento.php` | `AdminController::mantenimiento` | `MantenimientoModel` | `mantenimientos_programados`, `notificaciones`, `auditoria_sistema` |
| Tickets técnico | `views/mantenimiento/tickets.php` | `MantenimientoController::tickets` | `TicketModel` | `tickets_mantenimiento`, `ticket_seguimientos`, `notificaciones`, `auditoria_sistema` |
| Equipos técnico | `views/mantenimiento/equipos.php` | `MantenimientoController::equipos` | `MantenimientoModel` | `equipos_alberca`, `equipo_revisiones`, `auditoria_sistema` |

## Rutas renderizadas en prueba

Se renderizaron sin fatal/warning/notice:

- Admin: `admin-dashboard`, `admin-usuarios`, `admin-albercas`, `admin-catalogos`, `admin-limpieza`, `admin-mantenimiento`, `admin-reportes`, `admin-seguridad`.
- Encargado: `encargado-dashboard`, `encargado-aforo`, `encargado-calidad-agua`, `encargado-alertas`, `encargado-incidencias`, `encargado-horarios`.
- Limpieza: `limpieza-dashboard`, `limpieza-turnos`, `limpieza-checklist`, `limpieza-incidencias`, `limpieza-historial`.
- Mantenimiento: `mantenimiento-dashboard`, `mantenimiento-tickets`, `mantenimiento-agenda`, `mantenimiento-equipos`, `mantenimiento-historial`.
- Sin rol: `sin-rol`.

También se probaron POST incompletos con CSRF válido en rutas de escritura para verificar que no generen warnings por índices faltantes.

## Archivos SQL

### Instalación limpia

Usar:

```sql
source database/schema.sql;
```

O importar `database/schema.sql` desde phpMyAdmin.

### Base ya existente

Aplicar en orden:

1. `database/migrations/20260610_backend_real.sql`
2. `database/migrations/20260610_backend_audit_fixes.sql`

## Pendientes recomendados

No bloquean el backend actual, pero son mejoras siguientes:

1. Crear CRUD administrativo completo para catálogos si se quiere editar estados, prioridades, áreas y tareas desde UI.
2. Crear pantalla de notificaciones, porque el backend ya guarda `notificaciones` pero aún no hay inbox visual.
3. Agregar exportación CSV/PDF a reportes si el cliente la necesita.
4. Agregar pruebas automáticas con una base MySQL real en CI o XAMPP.
5. Definir dominio institucional si el registro debe aceptar solo correos de una organización específica.

## Validación final ejecutada

Comandos ejecutados en este entorno:

```bash
find . -path './.git' -prune -o -name '*.php' -print0 | xargs -0 -n1 php -l
```

Resultado: **sin errores de sintaxis**.

Además, se ejecutaron pruebas CLI de render para todas las rutas principales y pruebas POST malformadas con token CSRF válido. Resultado: **sin errores fatales, warnings ni notices**.

Limitación honesta: este entorno no incluye `pdo_mysql` ni un servidor MySQL local, así que la conexión real contra MySQL debe validarse en tu XAMPP con `php tools/auditoria_backend.php`.
