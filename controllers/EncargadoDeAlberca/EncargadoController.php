<?php
// controllers/EncargadoDeAlberca/EncargadoController.php
require_once __DIR__ . '/../../includes/database.php';

class EncargadoController {
    private $conn;
    private $usuario_id;
    
    public function __construct($usuario_id) {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
        $this->usuario_id = $usuario_id;
    }
    
    // Procesar acciones
    public function procesarAccion($action, $data) {
        switch ($action) {
            case 'actualizar_horario':
                return $this->actualizarHorario($data);
            case 'registrar_calidad':
                return $this->registrarCalidad($data);
            case 'asignar_personal':
                return $this->asignarPersonal($data);
            case 'reportar_incidencia':
                return $this->reportarIncidencia($data);
            case 'registrar_mantenimiento':
                return $this->registrarMantenimiento($data);
            case 'registrar_acceso':
                return $this->registrarAcceso($data);
            default:
                return ['success' => false, 'message' => 'Acción no válida'];
        }
    }
    
    private function actualizarHorario($data) {
        $apertura = $data['apertura'] ?? '';
        $cierre = $data['cierre'] ?? '';
        $fecha_especial = $data['fecha_especial'] ?? null;
        $fecha = date('Y-m-d');
        
        if ($apertura && $cierre) {
            $stmt = $this->conn->prepare("INSERT INTO horarios (fecha, apertura, cierre, fecha_especial, id_usuario_registro) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $fecha, $apertura, $cierre, $fecha_especial, $this->usuario_id);
            if ($stmt->execute()) {
                $stmt->close();
                return ['success' => true, 'message' => 'Horario actualizado correctamente.'];
            }
            $stmt->close();
            return ['success' => false, 'message' => 'Error al actualizar horario.'];
        }
        return ['success' => false, 'message' => 'Datos incompletos.'];
    }
    
    private function registrarCalidad($data) {
        $id_alberca = $data['alberca_id'] ?? 0;
        $cloro = $data['cloro'] ?? 0;
        $ph = $data['ph'] ?? 0;
        $temperatura = $data['temperatura'] ?? 0;
        $fecha_hora = date('Y-m-d H:i:s');
        
        if ($id_alberca && $cloro && $ph && $temperatura) {
            $stmt = $this->conn->prepare("INSERT INTO calidad_agua (id_alberca, fecha_hora, cloro_ppm, ph, temperatura, id_usuario_registro) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isdddi", $id_alberca, $fecha_hora, $cloro, $ph, $temperatura, $this->usuario_id);
            if ($stmt->execute()) {
                $stmt->close();
                return ['success' => true, 'message' => 'Parámetros registrados correctamente.'];
            }
            $stmt->close();
            return ['success' => false, 'message' => 'Error al registrar parámetros.'];
        }
        return ['success' => false, 'message' => 'Datos incompletos.'];
    }
    
    private function asignarPersonal($data) {
        $id_personal = $data['id_personal'] ?? 0;
        $id_turno = $data['id_turno'] ?? 0;
        $id_area = $data['id_area'] ?? 0;
        $fecha_asignacion = date('Y-m-d');
        
        if ($id_personal && $id_turno && $id_area) {
            $stmt = $this->conn->prepare("INSERT INTO personal_asignaciones (id_personal, id_turno, id_area, fecha_asignacion, id_usuario_asignador) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiisi", $id_personal, $id_turno, $id_area, $fecha_asignacion, $this->usuario_id);
            if ($stmt->execute()) {
                $stmt->close();
                return ['success' => true, 'message' => 'Personal asignado correctamente.'];
            }
            $stmt->close();
            return ['success' => false, 'message' => 'Error al asignar personal.'];
        }
        return ['success' => false, 'message' => 'Datos incompletos.'];
    }
    
    private function reportarIncidencia($data) {
        $id_tipo_incidencia = $data['id_tipo_incidencia'] ?? 0;
        $id_alberca = $data['id_alberca'] ?? null;
        $descripcion = $data['descripcion'] ?? '';
        $id_prioridad = $data['id_prioridad'] ?? 0;
        $fecha_reporte = date('Y-m-d H:i:s');
        $id_estado = 1;
        
        if ($id_tipo_incidencia && $descripcion && $id_prioridad) {
            $stmt = $this->conn->prepare("INSERT INTO reportes_incidencias (id_tipo_incidencia, id_alberca, descripcion, id_prioridad, id_estado, fecha_reporte, id_usuario_reporte) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssis", $id_tipo_incidencia, $id_alberca, $descripcion, $id_prioridad, $id_estado, $fecha_reporte, $this->usuario_id);
            if ($stmt->execute()) {
                $stmt->close();
                return ['success' => true, 'message' => 'Incidencia reportada correctamente.'];
            }
            $stmt->close();
            return ['success' => false, 'message' => 'Error al reportar incidencia.'];
        }
        return ['success' => false, 'message' => 'Datos incompletos.'];
    }
    
    private function registrarMantenimiento($data) {
        $id_alberca = $data['id_alberca'] ?? null;
        $id_equipo = $data['id_equipo'] ?? null;
        $id_tipo_mantenimiento = $data['id_tipo_mantenimiento'] ?? 0;
        $fecha_programada = $data['fecha_programada'] ?? date('Y-m-d');
        $fecha_realizada = date('Y-m-d');
        $descripcion = $data['descripcion'] ?? '';
        $id_tecnico = $data['id_tecnico'] ?? 0;
        $id_estado = 3;
        
        if ($id_tipo_mantenimiento && $descripcion && $id_tecnico) {
            $stmt = $this->conn->prepare("INSERT INTO mantenimientos (id_alberca, id_equipo, id_tipo_mantenimiento, fecha_programada, fecha_realizada, descripcion, id_tecnico, id_estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiisssii", $id_alberca, $id_equipo, $id_tipo_mantenimiento, $fecha_programada, $fecha_realizada, $descripcion, $id_tecnico, $id_estado);
            if ($stmt->execute()) {
                $stmt->close();
                return ['success' => true, 'message' => 'Mantenimiento registrado correctamente.'];
            }
            $stmt->close();
            return ['success' => false, 'message' => 'Error al registrar mantenimiento.'];
        }
        return ['success' => false, 'message' => 'Datos incompletos.'];
    }
    
    private function registrarAcceso($data) {
        $id_alberca = $data['id_alberca'] ?? 0;
        $tipo = $data['tipo_acceso'] ?? '';
        $cantidad = $data['cantidad'] ?? 1;
        $fecha_hora = date('Y-m-d H:i:s');
        
        if ($id_alberca && $tipo && $cantidad > 0) {
            $stmt = $this->conn->prepare("INSERT INTO acceso_registros (id_alberca, fecha_hora, tipo, cantidad_personas, id_usuario_registro) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issii", $id_alberca, $fecha_hora, $tipo, $cantidad, $this->usuario_id);
            if ($stmt->execute()) {
                $stmt->close();
                return ['success' => true, 'message' => 'Acceso registrado correctamente.'];
            }
            $stmt->close();
            return ['success' => false, 'message' => 'Error al registrar acceso.'];
        }
        return ['success' => false, 'message' => 'Datos incompletos.'];
    }
    
    // Obtener datos para el dashboard
    public function getDashboardData() {
        $hoy = date('Y-m-d');
        
        // Visitantes hoy
        $stmt = $this->conn->prepare("SELECT COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN cantidad_personas ELSE -cantidad_personas END), 0) as total FROM acceso_registros WHERE DATE(fecha_hora) = ?");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $visitantes_hoy = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();
        
        // Total entradas hoy
        $stmt = $this->conn->prepare("SELECT COALESCE(SUM(cantidad_personas), 0) as total FROM acceso_registros WHERE DATE(fecha_hora) = ? AND tipo = 'entrada'");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $total_entradas_hoy = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();
        
        // Total salidas hoy
        $stmt = $this->conn->prepare("SELECT COALESCE(SUM(cantidad_personas), 0) as total FROM acceso_registros WHERE DATE(fecha_hora) = ? AND tipo = 'salida'");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $total_salidas_hoy = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();
        
        $personas_actuales = $total_entradas_hoy - $total_salidas_hoy;
        $capacidad_total = 570;
        $porcentaje_ocupacion = $capacidad_total > 0 ? round(($personas_actuales / $capacidad_total) * 100) : 0;
        
        // Incidencias activas
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM reportes_incidencias WHERE id_estado IN (1,2)");
        $stmt->execute();
        $incidencias_activas = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();
        
        // Total albercas
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM cat_albercas WHERE activo = 1");
        $stmt->execute();
        $total_albercas = $stmt->get_result()->fetch_assoc()['total'] ?? 5;
        $stmt->close();
        $albercas_operativas = $total_albercas - 1;
        $albercas_operativas_texto = "$albercas_operativas/$total_albercas";
        
        // Estado de albercas
        $stmt = $this->conn->prepare("
            SELECT a.id_alberca, a.nombre, a.capacidad_maxima,
                   COALESCE(SUM(CASE WHEN ar.tipo = 'entrada' THEN ar.cantidad_personas ELSE 0 END), 0) - 
                   COALESCE(SUM(CASE WHEN ar.tipo = 'salida' THEN ar.cantidad_personas ELSE 0 END), 0) as aforo_actual
            FROM cat_albercas a
            LEFT JOIN acceso_registros ar ON a.id_alberca = ar.id_alberca AND DATE(ar.fecha_hora) = ?
            WHERE a.activo = 1
            GROUP BY a.id_alberca
        ");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $albercas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Calidad del agua
        $result = $this->conn->query("
            SELECT c.nombre as alberca, ca.cloro_ppm, ca.ph, ca.temperatura
            FROM calidad_agua ca
            JOIN cat_albercas c ON ca.id_alberca = c.id_alberca
            WHERE (ca.id_alberca, ca.fecha_hora) IN (
                SELECT id_alberca, MAX(fecha_hora)
                FROM calidad_agua
                GROUP BY id_alberca
            )
            ORDER BY c.id_alberca
        ");
        $ultima_calidad = $result->fetch_all(MYSQLI_ASSOC);
        
        // Próximos mantenimientos
        $result = $this->conn->query("
            SELECT m.fecha_programada, m.descripcion, c.nombre as alberca
            FROM mantenimientos m
            JOIN cat_albercas c ON m.id_alberca = c.id_alberca
            WHERE m.fecha_programada >= CURDATE() AND m.id_estado IN (1,2)
            ORDER BY m.fecha_programada ASC
            LIMIT 3
        ");
        $proximos_mantenimientos = $result->fetch_all(MYSQLI_ASSOC);
        
        // Alertas
        $result = $this->conn->query("
            SELECT DISTINCT a.nombre
            FROM cat_albercas a
            LEFT JOIN mantenimientos m ON a.id_alberca = m.id_alberca AND m.fecha_programada >= CURDATE() AND m.id_estado IN (1,2)
            LEFT JOIN reportes_incidencias r ON a.id_alberca = r.id_alberca AND r.id_estado IN (1,2)
            WHERE m.id_mantenimiento IS NOT NULL OR r.id_reporte IS NOT NULL
        ");
        $alertas_albercas = array_column($result->fetch_all(MYSQLI_ASSOC), 'nombre');
        
        // Horario actual
        $result = $this->conn->query("SELECT apertura, cierre FROM horarios WHERE fecha <= CURDATE() ORDER BY fecha DESC LIMIT 1");
        $horario_actual = $result->fetch_assoc();
        if (!$horario_actual) {
            $horario_actual = ['apertura' => '09:00:00', 'cierre' => '18:00:00'];
        }
        
        // Personal asignado hoy
        $result = $this->conn->query("
            SELECT u.nombre, t.nombre as turno, t.hora_inicio, t.hora_fin, al.nombre as area
            FROM personal_asignaciones pa
            JOIN usuarios u ON pa.id_personal = u.id
            JOIN cat_turnos t ON pa.id_turno = t.id_turno
            JOIN cat_areas_limpieza al ON pa.id_area = al.id_area
            WHERE pa.fecha_asignacion = CURDATE()
        ");
        $personal_asignado = $result->fetch_all(MYSQLI_ASSOC);
        
        // Reportes recientes
        $result = $this->conn->query("
            SELECT r.fecha_reporte, ti.nombre as tipo, r.descripcion, p.nombre as prioridad, e.nombre as estado
            FROM reportes_incidencias r
            JOIN cat_tipo_incidencia ti ON r.id_tipo_incidencia = ti.id_tipo_incidencia
            JOIN cat_prioridad p ON r.id_prioridad = p.id_prioridad
            JOIN cat_estado_reporte e ON r.id_estado = e.id_estado
            ORDER BY r.fecha_reporte DESC
            LIMIT 3
        ");
        $reportes_recientes = $result->fetch_all(MYSQLI_ASSOC);
        
        // Catálogos
        $catalogo_albercas = $this->conn->query("SELECT id_alberca, nombre FROM cat_albercas WHERE activo = 1")->fetch_all(MYSQLI_ASSOC);
        $catalogo_turnos = $this->conn->query("SELECT id_turno, nombre FROM cat_turnos WHERE activo = 1")->fetch_all(MYSQLI_ASSOC);
        $catalogo_areas = $this->conn->query("SELECT id_area, nombre FROM cat_areas_limpieza WHERE activo = 1")->fetch_all(MYSQLI_ASSOC);
        $catalogo_tipos_incidencia = $this->conn->query("SELECT id_tipo_incidencia, nombre FROM cat_tipo_incidencia")->fetch_all(MYSQLI_ASSOC);
        $catalogo_prioridades = $this->conn->query("SELECT id_prioridad, nombre FROM cat_prioridad")->fetch_all(MYSQLI_ASSOC);
        $catalogo_tipos_mantenimiento = $this->conn->query("SELECT id_tipo_mantenimiento, nombre FROM cat_tipo_mantenimiento")->fetch_all(MYSQLI_ASSOC);
        $catalogo_equipos = $this->conn->query("SELECT id_equipo, nombre FROM cat_equipos WHERE activo = 1")->fetch_all(MYSQLI_ASSOC);
        
        // CORRECCIÓN DE ROLES APLICADA AQUÍ: Limpieza = 3, Técnico = 4
        $catalogo_personal_limpieza = $this->conn->query("SELECT id, nombre FROM usuarios WHERE id_rol = 3 AND activo = 1")->fetch_all(MYSQLI_ASSOC);
        $catalogo_tecnicos = $this->conn->query("SELECT id, nombre FROM usuarios WHERE id_rol = 4 AND activo = 1")->fetch_all(MYSQLI_ASSOC);
        
        // Contar estados para gráfica
        $operativas = 0;
        $completas = 0;
        $mantenimiento = 0;
        foreach ($albercas as $alberca) {
            if ($alberca['aforo_actual'] >= $alberca['capacidad_maxima']) {
                $completas++;
            } elseif ($alberca['aforo_actual'] > 0) {
                $operativas++;
            } else {
                $mantenimiento++;
            }
        }
        
        return [
            'visitantes_hoy' => $visitantes_hoy,
            'total_entradas_hoy' => $total_entradas_hoy,
            'total_salidas_hoy' => $total_salidas_hoy,
            'personas_actuales' => $personas_actuales,
            'porcentaje_ocupacion' => $porcentaje_ocupacion,
            'incidencias_activas' => $incidencias_activas,
            'albercas_operativas_texto' => $albercas_operativas_texto,
            'albercas' => $albercas,
            'ultima_calidad' => $ultima_calidad,
            'proximos_mantenimientos' => $proximos_mantenimientos,
            'alertas_albercas' => $alertas_albercas,
            'horario_actual' => $horario_actual,
            'personal_asignado' => $personal_asignado,
            'reportes_recientes' => $reportes_recientes,
            'catalogo_albercas' => $catalogo_albercas,
            'catalogo_turnos' => $catalogo_turnos,
            'catalogo_areas' => $catalogo_areas,
            'catalogo_tipos_incidencia' => $catalogo_tipos_incidencia,
            'catalogo_prioridades' => $catalogo_prioridades,
            'catalogo_tipos_mantenimiento' => $catalogo_tipos_mantenimiento,
            'catalogo_equipos' => $catalogo_equipos,
            'catalogo_tecnicos' => $catalogo_tecnicos,
            'catalogo_personal_limpieza' => $catalogo_personal_limpieza,
            'operativas' => $operativas,
            'completas' => $completas,
            'mantenimiento' => $mantenimiento,
            'capacidad_total' => $capacidad_total
        ];
    }
}
?>