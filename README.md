# Sistema de Albercas B2E Control

Sistema interno en PHP MVC ligero para operar un complejo de albercas: aforo, calidad del agua, limpieza, mantenimiento, tickets FIFO, usuarios y reportes.

## Stack

- PHP puro sin framework.
- MVC ligero: `index.php`, `controllers/`, `models/`, `views/`.
- MySQL con PDO.
- Bootstrap 5, CSS propio, JavaScript vanilla y Chart.js.

## Roles oficiales

| idRol | Rol | Ruta inicial |
|---:|---|---|
| 1 | Administrador | `admin-dashboard` |
| 2 | Encargado de Alberca | `encargado-dashboard` |
| 3 | Personal de Limpieza | `limpieza-dashboard` |
| 4 | Técnico de Mantenimiento | `mantenimiento-dashboard` |

Los usuarios nuevos quedan con `idRol = NULL` y `estado = pendiente`. Solo pueden ver la pantalla `sin-rol` hasta que el Administrador los active.

## Instalación limpia

1. Copia la carpeta a `htdocs/Sistema-de-Alberca`.
2. Importa `database/schema.sql` en MySQL.
3. Ajusta `config/db.php` según tu XAMPP/hosting.
4. Abre `index.php?page=login`.

Configuración incluida:

```php
'host'=>'localhost',
'port'=>'3307',
'database'=>'albercas',
'username'=>'root',
'password'=>'',
```

Si tu MySQL usa el puerto normal de XAMPP, cambia `port` a `3306`.

## Actualización desde una base anterior

Si ya habías importado una versión previa:

1. Haz respaldo de la base `albercas`.
2. Importa `database/migrations/20260610_backend_real.sql`.
3. Importa `database/migrations/20260610_backend_audit_fixes.sql`.
4. Verifica que exista la tabla `equipo_revisiones`.

## Credenciales demo

Contraseña para todos: `Admin123!`

- `admin@albercas.local`
- `encargado@albercas.local`
- `limpieza@albercas.local`
- `tecnico@albercas.local`
- `pendiente@albercas.local`

## Backend conectado a BD

Los formularios principales ya escriben en MySQL:

- Login/registro: `usuarios`.
- Usuarios y roles: `usuarios`, `roles`.
- Aforo: `aforo_movimientos`.
- Calidad del agua: `calidad_agua_registros` y alertas automáticas.
- Alertas: `alertas_alberca`.
- Tickets FIFO: `tickets_mantenimiento`, `ticket_seguimientos`.
- Limpieza: `turnos_limpieza`, `checklist_limpieza`.
- Mantenimiento: `mantenimientos_programados`, `equipos_alberca`, `equipo_revisiones`.
- Auditoría: `auditoria_sistema`.
- Notificaciones internas: `notificaciones`.

## Seguridad

- Contraseñas con bcrypt (`password_hash` / `password_verify`).
- Tokens CSRF en formularios POST.
- Sesión con cookie HTTPOnly y SameSite=Lax.
- Cierre por inactividad configurable en `config/app.php`.
- Control de acceso por rol desde `index.php`.
- Bitácora de acciones críticas en `auditoria_sistema`.

## Auditoría técnica

Lee `AUDITORIA_BACKEND.md` para ver el detalle de cambios, mapa formulario-tabla, pruebas realizadas y pendientes recomendados.


## Auditoría local de conexión

Después de importar la base, ejecuta en terminal desde la carpeta del proyecto:

```bash
php tools/auditoria_backend.php
```

También puedes abrirlo desde `localhost` en XAMPP. La herramienta valida conexión MySQL, extensión PDO, tablas y columnas esperadas por el backend.
