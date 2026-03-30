<?php
// usuarios/EncargadoDeAlberca/index.php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: ../../login.php');
    exit;
}

require_once '../../includes/database.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar formularios (igual que antes)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'actualizar_horario':
            $apertura = $_POST['apertura'] ?? '';
            $cierre = $_POST['cierre'] ?? '';
            $fecha_especial = $_POST['fecha_especial'] ?? null;
            $fecha = date('Y-m-d');
            $id_usuario = $_SESSION['usuario_id'];
            if ($apertura && $cierre) {
                $stmt = $conn->prepare("INSERT INTO horarios (fecha, apertura, cierre, fecha_especial, id_usuario_registro) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $fecha, $apertura, $cierre, $fecha_especial, $id_usuario);
                if ($stmt->execute()) {
                    $mensaje = "Horario actualizado correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al actualizar horario: " . $conn->error;
                    $tipo_mensaje = "error";
                }
                $stmt->close();
            }
            break;
        case 'registrar_calidad':
            $id_alberca = $_POST['alberca_id'] ?? 0;
            $cloro = $_POST['cloro'] ?? 0;
            $ph = $_POST['ph'] ?? 0;
            $temperatura = $_POST['temperatura'] ?? 0;
            $fecha_hora = date('Y-m-d H:i:s');
            $id_usuario = $_SESSION['usuario_id'];
            if ($id_alberca && $cloro && $ph && $temperatura) {
                $stmt = $conn->prepare("INSERT INTO calidad_agua (id_alberca, fecha_hora, cloro_ppm, ph, temperatura, id_usuario_registro) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isdddi", $id_alberca, $fecha_hora, $cloro, $ph, $temperatura, $id_usuario);
                if ($stmt->execute()) {
                    $mensaje = "Parámetros de calidad registrados correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al registrar: " . $conn->error;
                    $tipo_mensaje = "error";
                }
                $stmt->close();
            }
            break;
        case 'asignar_personal':
            $id_personal = $_POST['id_personal'] ?? 0;
            $id_turno = $_POST['id_turno'] ?? 0;
            $id_area = $_POST['id_area'] ?? 0;
            $fecha_asignacion = date('Y-m-d');
            $id_asignador = $_SESSION['usuario_id'];
            if ($id_personal && $id_turno && $id_area) {
                $stmt = $conn->prepare("INSERT INTO personal_asignaciones (id_personal, id_turno, id_area, fecha_asignacion, id_usuario_asignador) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiisi", $id_personal, $id_turno, $id_area, $fecha_asignacion, $id_asignador);
                if ($stmt->execute()) {
                    $mensaje = "Personal asignado correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al asignar: " . $conn->error;
                    $tipo_mensaje = "error";
                }
                $stmt->close();
            }
            break;
        case 'reportar_incidencia':
            $id_tipo_incidencia = $_POST['id_tipo_incidencia'] ?? 0;
            $id_alberca = $_POST['id_alberca'] ?? null;
            $area_descripcion = $_POST['area_descripcion'] ?? null;
            $descripcion = $_POST['descripcion'] ?? '';
            $id_prioridad = $_POST['id_prioridad'] ?? 0;
            $fecha_reporte = date('Y-m-d H:i:s');
            $id_usuario = $_SESSION['usuario_id'];
            $id_estado = 1;
            if ($id_tipo_incidencia && $descripcion && $id_prioridad) {
                $stmt = $conn->prepare("INSERT INTO reportes_incidencias (id_tipo_incidencia, id_alberca, area_descripcion, descripcion, id_prioridad, id_estado, fecha_reporte, id_usuario_reporte) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisssisi", $id_tipo_incidencia, $id_alberca, $area_descripcion, $descripcion, $id_prioridad, $id_estado, $fecha_reporte, $id_usuario);
                if ($stmt->execute()) {
                    $mensaje = "Incidencia reportada correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al reportar: " . $conn->error;
                    $tipo_mensaje = "error";
                }
                $stmt->close();
            }
            break;
        case 'registrar_mantenimiento':
            $id_alberca = $_POST['id_alberca'] ?? null;
            $id_equipo = $_POST['id_equipo'] ?? null;
            $id_tipo_mantenimiento = $_POST['id_tipo_mantenimiento'] ?? 0;
            $fecha_programada = $_POST['fecha_programada'] ?? date('Y-m-d');
            $fecha_realizada = date('Y-m-d');
            $descripcion = $_POST['descripcion'] ?? '';
            $id_tecnico = $_POST['id_tecnico'] ?? 0;
            $id_estado = 3;
            if ($id_tipo_mantenimiento && $descripcion && $id_tecnico) {
                $stmt = $conn->prepare("INSERT INTO mantenimientos (id_alberca, id_equipo, id_tipo_mantenimiento, fecha_programada, fecha_realizada, descripcion, id_tecnico, id_estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiisssii", $id_alberca, $id_equipo, $id_tipo_mantenimiento, $fecha_programada, $fecha_realizada, $descripcion, $id_tecnico, $id_estado);
                if ($stmt->execute()) {
                    $mensaje = "Mantenimiento registrado correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al registrar: " . $conn->error;
                    $tipo_mensaje = "error";
                }
                $stmt->close();
            }
            break;
        case 'registrar_acceso':
            $id_alberca = $_POST['id_alberca'] ?? 0;
            $tipo = $_POST['tipo_acceso'] ?? '';
            $cantidad = $_POST['cantidad'] ?? 1;
            $fecha_hora = date('Y-m-d H:i:s');
            $id_usuario = $_SESSION['usuario_id'];
            if ($id_alberca && $tipo && $cantidad > 0) {
                $stmt = $conn->prepare("INSERT INTO acceso_registros (id_alberca, fecha_hora, tipo, cantidad_personas, id_usuario_registro) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issii", $id_alberca, $fecha_hora, $tipo, $cantidad, $id_usuario);
                if ($stmt->execute()) {
                    $mensaje = "Acceso registrado correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al registrar acceso: " . $conn->error;
                    $tipo_mensaje = "error";
                }
                $stmt->close();
            }
            break;
    }
}

// Obtener datos (igual que antes)
$hoy = date('Y-m-d');
$stmt = $conn->prepare("SELECT SUM(CASE WHEN tipo = 'entrada' THEN cantidad_personas ELSE -cantidad_personas END) as total FROM acceso_registros WHERE DATE(fecha_hora) = ?");
$stmt->bind_param("s", $hoy);
$stmt->execute();
$visitantes_hoy = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT SUM(cantidad_personas) as total_actual FROM acceso_registros WHERE DATE(fecha_hora) = ? AND tipo = 'entrada'");
$stmt->bind_param("s", $hoy);
$stmt->execute();
$total_entradas = $stmt->get_result()->fetch_assoc()['total_actual'] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT SUM(cantidad_personas) as total_salidas FROM acceso_registros WHERE DATE(fecha_hora) = ? AND tipo = 'salida'");
$stmt->bind_param("s", $hoy);
$stmt->execute();
$total_salidas = $stmt->get_result()->fetch_assoc()['total_salidas'] ?? 0;
$stmt->close();

$personas_actuales = $total_entradas - $total_salidas;
$capacidad_total = 570;
$porcentaje_ocupacion = $capacidad_total > 0 ? round(($personas_actuales / $capacidad_total) * 100) : 0;

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM reportes_incidencias WHERE id_estado IN (1,2)");
$stmt->execute();
$incidencias_activas = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM cat_albercas WHERE activo = 1");
$stmt->execute();
$total_albercas = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();
$albercas_operativas = $total_albercas - 1;
$albercas_operativas_texto = "$albercas_operativas/$total_albercas";

$stmt = $conn->prepare("SELECT a.id_alberca, a.nombre, a.capacidad_maxima, COALESCE(SUM(CASE WHEN ar.tipo = 'entrada' THEN ar.cantidad_personas ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN ar.tipo = 'salida' THEN ar.cantidad_personas ELSE 0 END), 0) as aforo_actual FROM cat_albercas a LEFT JOIN acceso_registros ar ON a.id_alberca = ar.id_alberca AND DATE(ar.fecha_hora) = ? GROUP BY a.id_alberca");
$stmt->bind_param("s", $hoy);
$stmt->execute();
$albercas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("SELECT c.nombre as alberca, ca.cloro_ppm, ca.ph, ca.temperatura FROM calidad_agua ca JOIN cat_albercas c ON ca.id_alberca = c.id_alberca WHERE (ca.id_alberca, ca.fecha_hora) IN (SELECT id_alberca, MAX(fecha_hora) FROM calidad_agua GROUP BY id_alberca) ORDER BY c.id_alberca");
$stmt->execute();
$ultima_calidad = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("SELECT m.fecha_programada, m.descripcion, c.nombre as alberca FROM mantenimientos m JOIN cat_albercas c ON m.id_alberca = c.id_alberca WHERE m.fecha_programada >= CURDATE() AND m.id_estado IN (1,2) ORDER BY m.fecha_programada ASC LIMIT 3");
$stmt->execute();
$proximos_mantenimientos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("SELECT DISTINCT a.nombre FROM cat_albercas a JOIN mantenimientos m ON a.id_alberca = m.id_alberca WHERE m.fecha_programada >= CURDATE() AND m.id_estado IN (1,2) UNION SELECT DISTINCT a.nombre FROM cat_albercas a JOIN reportes_incidencias r ON a.id_alberca = r.id_alberca WHERE r.id_estado IN (1,2) AND r.id_prioridad >= 3");
$stmt->execute();
$alertas_albercas = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'nombre');
$stmt->close();

$stmt = $conn->prepare("SELECT apertura, cierre FROM horarios WHERE fecha <= CURDATE() ORDER BY fecha DESC LIMIT 1");
$stmt->execute();
$horario_actual = $stmt->get_result()->fetch_assoc();
if (!$horario_actual) $horario_actual = ['apertura' => '09:00:00', 'cierre' => '18:00:00'];
$stmt->close();

$stmt = $conn->prepare("SELECT u.nombre, t.nombre as turno, t.hora_inicio, t.hora_fin, al.nombre as area FROM personal_asignaciones pa JOIN usuarios u ON pa.id_personal = u.id JOIN cat_turnos t ON pa.id_turno = t.id_turno JOIN cat_areas_limpieza al ON pa.id_area = al.id_area WHERE pa.fecha_asignacion = CURDATE()");
$stmt->execute();
$personal_asignado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("SELECT r.fecha_reporte, ti.nombre as tipo, r.descripcion, p.nombre as prioridad, e.nombre as estado FROM reportes_incidencias r JOIN cat_tipo_incidencia ti ON r.id_tipo_incidencia = ti.id_tipo_incidencia JOIN cat_prioridad p ON r.id_prioridad = p.id_prioridad JOIN cat_estado_reporte e ON r.id_estado = e.id_estado ORDER BY r.fecha_reporte DESC LIMIT 3");
$stmt->execute();
$reportes_recientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$catalogo_albercas = $conn->query("SELECT id_alberca, nombre FROM cat_albercas WHERE activo = 1");
$catalogo_turnos = $conn->query("SELECT id_turno, nombre FROM cat_turnos WHERE activo = 1");
$catalogo_areas = $conn->query("SELECT id_area, nombre FROM cat_areas_limpieza WHERE activo = 1");
$catalogo_tipos_incidencia = $conn->query("SELECT id_tipo_incidencia, nombre FROM cat_tipo_incidencia");
$catalogo_prioridades = $conn->query("SELECT id_prioridad, nombre FROM cat_prioridad");
$catalogo_tipos_mantenimiento = $conn->query("SELECT id_tipo_mantenimiento, nombre FROM cat_tipo_mantenimiento");
$catalogo_equipos = $conn->query("SELECT id_equipo, nombre FROM cat_equipos WHERE activo = 1");
$catalogo_tecnicos = $conn->query("SELECT id, nombre FROM usuarios WHERE id_rol = 3 AND activo = 1");
$catalogo_personal_limpieza = $conn->query("SELECT id, nombre FROM usuarios WHERE id_rol = 2 AND activo = 1");

// Incluir la vista
include '../../views/EncargadoDeAlberca/index.html';
?>