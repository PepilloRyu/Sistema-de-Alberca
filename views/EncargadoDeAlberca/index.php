<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Encargado - Nuestras Albercas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/EncargadoDeAlberca/index.css">
</head>
<body>

    <!-- Header con información del usuario -->
    <div class="header">
        <div class="user-info">
            <h1>Panel del Encargado de Alberca</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Encargado'); ?></p>
        </div>
        <div class="logout">
            <a href="../../logout.php">Cerrar sesión</a>
        </div>
    </div>

    <!-- Mensajes de notificación -->
    <?php if (isset($mensaje) && $mensaje): ?>
    <div class="notification <?php echo $tipo_mensaje ?? ''; ?>">
        <?php echo htmlspecialchars($mensaje); ?>
    </div>
    <?php endif; ?>

    <!-- Navegación principal con botón hamburguesa -->
    <div class="nav">
        <button class="hamburger-btn" id="hamburgerBtn">
            <div class="hamburger-line"></div>
            <div class="hamburger-line"></div>
            <div class="hamburger-line"></div>
        </button>
        <a href="#" data-section="inicio" class="active"><span>INICIO</span></a>
        <a href="#" data-section="horarios"><span>Horarios</span></a>
        <a href="#" data-section="calidad-agua"><span>Calidad del Agua</span></a>
        <a href="#" data-section="personal"><span>Personal</span></a>
        <a href="#" data-section="reportes"><span>Reportes</span></a>
        <a href="#" data-section="mantenimiento"><span>Mantenimiento</span></a>
        <a href="#" data-section="acceso"><span>Control de Acceso</span></a>
    </div>

    <!-- Contenido principal -->
    <div class="content" id="mainContent">

        <!-- ═══════════════════════════════════════════════════════════
             SECCIÓN INICIO - DASHBOARD PRINCIPAL
        ═══════════════════════════════════════════════════════════ -->
        <div id="inicio" class="section-container active">
            <div class="dashboard-grid" id="dashboardGrid">

                <!-- Fila superior: bienvenida + estadísticas rápidas -->
                <div class="welcome-stats">
                    <div class="welcome">
                        <h2>Bienvenido, Encargado</h2>
                        <p>Panel de control general</p>
                        <div class="date-time">
                            <?php echo date('l, d \d\e F \d\e Y'); ?>
                            <span class="time"><?php echo date('h:i A'); ?></span>
                        </div>
                    </div>
                    <div class="stats-mini">
                        <div class="stat-mini">
                            <span class="stat-mini-icon">👥</span>
                            <div>
                                <span class="stat-mini-number"><?php echo $visitantes_hoy ?? 0; ?></span>
                                <span class="stat-mini-label">Visitantes hoy</span>
                            </div>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-icon">🏊</span>
                            <div>
                                <span class="stat-mini-number"><?php echo $porcentaje_ocupacion ?? 0; ?>%</span>
                                <span class="stat-mini-label">Ocupación</span>
                            </div>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-icon">⚠️</span>
                            <div>
                                <span class="stat-mini-number"><?php echo $incidencias_activas ?? 0; ?></span>
                                <span class="stat-mini-label">Incidencias</span>
                            </div>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-icon">✅</span>
                            <div>
                                <span class="stat-mini-number"><?php echo $albercas_operativas_texto ?? '4/5'; ?></span>
                                <span class="stat-mini-label">Albercas</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Estado de Albercas con gráfica de dona -->
                <div class="dashboard-card card-estado-albercas">
                    <h3>📊 Estado de Albercas</h3>
                    <div class="estado-albercas-container">
                        <div class="dona-chart-container">
                            <canvas id="donaChart" width="200" height="200"></canvas>
                            <div class="chart-legend">
                                <div class="legend-item">
                                    <span class="legend-color" style="background: #2ecc71;"></span>
                                    <span>Operativas</span>
                                    <span class="legend-value" id="operativasCount">0</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color" style="background: #e74c3c;"></span>
                                    <span>Completas</span>
                                    <span class="legend-value" id="completasCount">0</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color" style="background: #f39c12;"></span>
                                    <span>Mantenimiento</span>
                                    <span class="legend-value" id="mantenimientoCount">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="pool-status-list">
                            <?php foreach (($albercas ?? []) as $alberca):
                                $porcentaje = $alberca['capacidad_maxima'] > 0
                                    ? round(($alberca['aforo_actual'] / $alberca['capacidad_maxima']) * 100)
                                    : 0;
                                if ($alberca['aforo_actual'] >= $alberca['capacidad_maxima']) {
                                    $clase_estado = 'warning';
                                    $texto_estado = 'Completa';
                                } elseif ($alberca['aforo_actual'] > 0) {
                                    $clase_estado = 'operational';
                                    $texto_estado = 'Operativa';
                                } else {
                                    $clase_estado = 'maintenance';
                                    $texto_estado = 'Mantenimiento';
                                }
                            ?>
                            <div class="pool-status-item">
                                <span class="pool-name"><?php echo htmlspecialchars($alberca['nombre']); ?></span>
                                <span class="pool-status-badge <?php echo $clase_estado; ?>"><?php echo $texto_estado; ?></span>
                                <div class="pool-progress">
                                    <div class="progress-bar" style="width: <?php echo $porcentaje; ?>%"></div>
                                </div>
                                <span class="pool-stats"><?php echo $alberca['aforo_actual']; ?>/<?php echo $alberca['capacidad_maxima']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Calidad del Agua (resumen dashboard) -->
                <div class="dashboard-card card-calidad-agua">
                    <h3>💧 Calidad del Agua</h3>
                    <div class="water-quality-summary">
                        <?php
                        $promedios = ['cloro' => 0, 'ph' => 0, 'temp' => 0];
                        $count = count($ultima_calidad ?? []);
                        foreach (($ultima_calidad ?? []) as $c) {
                            $promedios['cloro'] += $c['cloro_ppm'];
                            $promedios['ph']    += $c['ph'];
                            $promedios['temp']  += $c['temperatura'];
                        }
                        if ($count > 0) {
                            $promedios['cloro'] /= $count;
                            $promedios['ph']    /= $count;
                            $promedios['temp']  /= $count;
                        }
                        ?>
                        <div class="quality-item">
                            <span class="quality-label">Cloro</span>
                            <span class="quality-value"><?php echo number_format($promedios['cloro'], 1); ?> ppm</span>
                            <span class="quality-status good">Óptimo</span>
                        </div>
                        <div class="quality-item">
                            <span class="quality-label">pH</span>
                            <span class="quality-value"><?php echo number_format($promedios['ph'], 1); ?></span>
                            <span class="quality-status good">Balanceado</span>
                        </div>
                        <div class="quality-item">
                            <span class="quality-label">Temperatura</span>
                            <span class="quality-value"><?php echo number_format($promedios['temp'], 1); ?>°C</span>
                            <span class="quality-status normal">Normal</span>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Mantenimientos Programados -->
                <div class="dashboard-card card-mantenimientos">
                    <h3>🔧 Próximos Mantenimientos</h3>
                    <ul class="maintenance-list">
                        <?php foreach (($proximos_mantenimientos ?? []) as $m): ?>
                        <li>
                            <span class="date"><?php echo date('d/m', strtotime($m['fecha_programada'])); ?></span>
                            <span class="task"><?php echo htmlspecialchars($m['descripcion']); ?></span>
                            <span class="pool"><?php echo htmlspecialchars($m['alberca']); ?></span>
                        </li>
                        <?php endforeach; ?>
                        <?php if (empty($proximos_mantenimientos ?? [])): ?>
                        <li>No hay mantenimientos programados próximamente.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Tarjeta Alertas -->
                <div class="dashboard-card card-alertas">
                    <h3>⚠️ Alertas</h3>
                    <?php if (!empty($alertas_albercas ?? [])): ?>
                        <?php foreach (($alertas_albercas ?? []) as $alerta): ?>
                        <div class="alert-item">
                            <span class="alert-icon">🔴</span>
                            <div class="alert-content">
                                <strong><?php echo htmlspecialchars($alerta); ?> en mantenimiento</strong>
                                <p>Requiere atención o seguimiento.</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert-item success">
                            <span class="alert-icon">✅</span>
                            <div class="alert-content">
                                <strong>Sin alertas activas</strong>
                                <p>Todas las albercas operan con normalidad.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tarjetas Estadísticas Extra -->
                <div class="stat-extra-card stat-entradas">
                    <div class="stat-extra-number"><?php echo $total_entradas_hoy ?? 0; ?></div>
                    <div class="stat-extra-label">Entradas hoy</div>
                </div>
                <div class="stat-extra-card stat-salidas">
                    <div class="stat-extra-number"><?php echo $total_salidas_hoy ?? 0; ?></div>
                    <div class="stat-extra-label">Salidas hoy</div>
                </div>
                <div class="stat-extra-card stat-personas">
                    <div class="stat-extra-number"><?php echo $personas_actuales ?? 0; ?></div>
                    <div class="stat-extra-label">Personas en el parque</div>
                </div>
                <div class="stat-extra-card stat-mantenimientos">
                    <div class="stat-extra-number"><?php echo $mantenimientos_completados ?? 0; ?></div>
                    <div class="stat-extra-label">Mant. completados</div>
                </div>

            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             SECCIÓN HORARIOS
        ═══════════════════════════════════════════════════════════ -->
        <div id="horarios" class="section-container">
            <section>
                <h2>Control de Horarios</h2>
                <div>
                    <h3>Horario Actual</h3>
                    <p>Apertura: <?php echo date('g:i A', strtotime($horario_actual['apertura'] ?? '09:00:00')); ?></p>
                    <p>Cierre: <?php echo date('g:i A', strtotime($horario_actual['cierre'] ?? '18:00:00')); ?></p>
                </div>
                <div>
                    <h3>Modificar Horario</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="actualizar_horario">
                        <div>
                            <label>Hora de apertura:</label>
                            <input type="time" name="apertura" value="<?php echo substr($horario_actual['apertura'] ?? '09:00:00', 0, 5); ?>" required>
                        </div>
                        <div>
                            <label>Hora de cierre:</label>
                            <input type="time" name="cierre" value="<?php echo substr($horario_actual['cierre'] ?? '18:00:00', 0, 5); ?>" required>
                        </div>
                        <div>
                            <label>Fecha especial (opcional):</label>
                            <input type="date" name="fecha_especial">
                        </div>
                        <button type="submit">Actualizar Horario</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             SECCIÓN CALIDAD DEL AGUA CON MAPA INTERACTIVO
        ═══════════════════════════════════════════════════════════ -->
        <div id="calidad-agua" class="section-container">
            <section>
                <h2>💧 Calidad del Agua</h2>

                <!-- Mapa interactivo -->
                <div class="mapa-interactivo-container">
                    <h3>🌍 Mapa Interactivo del Parque</h3>
                    <p class="mapa-instruccion">🖱️ Pasa el mouse sobre cualquier área para ver información de calidad del agua</p>

                    <!-- 
                        Usamos un contenedor position:relative con la imagen debajo
                        y un SVG encima al 100% de tamaño. Los polígonos del SVG
                        tienen viewBox igual a las dimensiones originales de la imagen
                        (2560 x 1449) por lo que se escalan automáticamente sin
                        necesidad de recalcular coordenadas.
                    -->
                    <div class="mapa-imagen-wrapper" style="position:relative; display:inline-block; width:100%;">

                        <!-- Imagen base (sin usemap, ya no lo necesitamos) -->
                        <img src="../../img/mapa-albercas.png"
                             alt="Mapa del Parque Acuático"
                             class="mapa-imagen"
                             id="mapaImagen"
                             style="width:100%; height:auto; display:block;">

                        <!-- SVG superpuesto: viewBox = dimensiones reales de la imagen -->
                        <svg id="mapaSVG"
                             viewBox="0 0 2560 1449"
                             preserveAspectRatio="xMidYMid meet"
                             style="position:absolute; top:0; left:0; width:100%; height:100%; cursor:pointer;">

                            <!-- ALBERCA VISTA AL MAR -->
                            <polygon class="hotspot-area"
                                data-alberca="vista-mar"
                                points="708,260 850,293 1078,372 1116,484 831,581 697,465 753,376"/>

                            <!-- ALBERCA PRINCIPAL -->
                            <polygon class="hotspot-area"
                                data-alberca="principal"
                                points="985,724 1329,522 1505,480 1688,555 1808,798 1741,873 1449,825 1209,873 981,873"/>

                            <!-- ALBERCA DEPORTIVA -->
                            <polygon class="hotspot-area"
                                data-alberca="deportiva"
                                points="177,1053 491,832 715,877 457,1143"/>

                            <!-- ALBERCA FAMILIAR -->
                            <polygon class="hotspot-area"
                                data-alberca="familiar"
                                points="2119,615 2291,660 2377,731 2216,862 1943,739 1973,668"/>

                            <!-- ALBERCA INFANTIL -->
                            <polygon class="hotspot-area"
                                data-alberca="infantil"
                                points="1011,1225 1329,1240 1546,1266 1561,1393 962,1382 876,1300"/>

                            <!-- POOL CAFE -->
                            <polygon class="hotspot-area"
                                data-alberca="pool-cafe"
                                points="689,982 959,963 1194,982 1355,1184 1011,1188 708,1154"/>

                            <!-- VESTIBULARIOS -->
                            <polygon class="hotspot-area"
                                data-alberca="vestibularios"
                                points="1194,982 1464,1008 1482,1161 1355,1184"/>

                            <!-- PIÑA COLADA SHACK -->
                            <polygon class="hotspot-area"
                                data-alberca="pina-colada"
                                points="1568,978 1666,986 1733,1128 1651,1218 1482,1161 1464,1008"/>

                            <!-- STAGE -->
                            <polygon class="hotspot-area"
                                data-alberca="stage"
                                points="2126,948 2324,1008 2418,1120 2160,1199 1845,1173 1883,959"/>

                        </svg>
                    </div>

                    <!-- Tooltip flotante — IMPORTANTE: en tu CSS .mapa-tooltip debe tener position:fixed -->
                    <div id="mapaTooltip" class="mapa-tooltip" style="position:fixed; z-index:9999; pointer-events:none;">
                        <div class="tooltip-arrow"></div>
                        <div class="tooltip-title" id="tooltipTitulo">Alberca Principal</div>
                        <div class="tooltip-calidad" id="tooltipCalidad">
                            <div class="calidad-row">
                                <span class="label">💧 Cloro:</span>
                                <span class="value" id="valorCloro">-- ppm</span>
                            </div>
                            <div class="calidad-row">
                                <span class="label">🧪 pH:</span>
                                <span class="value" id="valorPh">--</span>
                            </div>
                            <div class="calidad-row">
                                <span class="label">🌡️ Temp:</span>
                                <span class="value" id="valorTemp">--°C</span>
                            </div>
                            <div class="calidad-row">
                                <span class="label">📊 Estado:</span>
                                <span class="value estado" id="valorEstado">--</span>
                            </div>
                        </div>
                    </div>

                    <!-- Leyenda -->
                    <div class="mapa-leyenda">
                        <div class="leyenda-item">
                            <div class="leyenda-color optimo"></div>
                            <span>Óptimo</span>
                        </div>
                        <div class="leyenda-item">
                            <div class="leyenda-color atencion"></div>
                            <span>Requiere atención</span>
                        </div>
                        <div class="leyenda-item">
                            <div class="leyenda-color critico"></div>
                            <span>Crítico</span>
                        </div>
                        <div class="leyenda-item">
                            <div class="leyenda-icon">🖱️</div>
                            <span>Pasa el mouse para ver datos</span>
                        </div>
                    </div>
                </div>

                <!-- Tabla de calidad del agua -->
                <div class="calidad-tabla">
                    <h3>📊 Parámetros de Calidad por Área</h3>
                    <div class="tabla-container">
                        <table class="calidad-table">
                            <thead>
                                <tr>
                                    <th>Área</th>
                                    <th>Cloro (ppm)</th>
                                    <th>pH</th>
                                    <th>Temperatura (°C)</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Alberca Principal</strong></td>
                                    <td class="calidad-good">1.5 ppm</td>
                                    <td class="calidad-good">7.2</td>
                                    <td class="calidad-good">26°C</td>
                                    <td><span class="estado-badge optimo">Óptimo</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Alberca Vista al Mar</strong></td>
                                    <td class="calidad-good">1.3 ppm</td>
                                    <td class="calidad-good">7.2</td>
                                    <td class="calidad-good">26°C</td>
                                    <td><span class="estado-badge optimo">Óptimo</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Alberca Deportiva</strong></td>
                                    <td class="calidad-good">1.4 ppm</td>
                                    <td class="calidad-good">7.1</td>
                                    <td class="calidad-good">25°C</td>
                                    <td><span class="estado-badge optimo">Óptimo</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Alberca Familiar</strong></td>
                                    <td class="calidad-good">1.4 ppm</td>
                                    <td class="calidad-good">7.3</td>
                                    <td class="calidad-good">27°C</td>
                                    <td><span class="estado-badge optimo">Óptimo</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Alberca Infantil</strong></td>
                                    <td class="calidad-good">1.0 ppm</td>
                                    <td class="calidad-good">7.0</td>
                                    <td class="calidad-good">28°C</td>
                                    <td><span class="estado-badge optimo">Óptimo</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Pool Cafe</strong></td>
                                    <td colspan="4" class="text-muted">Área sin monitoreo de agua</td>
                                </tr>
                                <tr>
                                    <td><strong>Vestibularios</strong></td>
                                    <td colspan="4" class="text-muted">Área sin monitoreo de agua</td>
                                </tr>
                                <tr>
                                    <td><strong>Piña Colada Shack</strong></td>
                                    <td colspan="4" class="text-muted">Área sin monitoreo de agua</td>
                                </tr>
                                <tr>
                                    <td><strong>Stage</strong></td>
                                    <td colspan="4" class="text-muted">Área sin monitoreo de agua</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Formulario para registrar parámetros -->
                <div class="calidad-formulario">
                    <h3>📝 Registrar Nuevos Parámetros</h3>
                    <form method="POST" class="form-calidad">
                        <input type="hidden" name="action" value="registrar_calidad">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Seleccionar alberca:</label>
                                <select name="alberca_id" required>
                                    <option value="1">Alberca Principal</option>
                                    <option value="2">Alberca Vista al Mar</option>
                                    <option value="3">Alberca Deportiva</option>
                                    <option value="4">Alberca Familiar</option>
                                    <option value="5">Alberca Infantil</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Cloro (ppm):</label>
                                <input type="number" step="0.1" name="cloro" placeholder="1.0 - 3.0" required>
                            </div>
                            <div class="form-group">
                                <label>pH:</label>
                                <input type="number" step="0.1" name="ph" placeholder="7.2 - 7.8" required>
                            </div>
                            <div class="form-group">
                                <label>Temperatura (°C):</label>
                                <input type="number" step="0.1" name="temperatura" placeholder="24 - 28" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-registrar">Registrar Parámetros</button>
                    </form>
                </div>

            </section>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             SECCIÓN PERSONAL
        ═══════════════════════════════════════════════════════════ -->
        <div id="personal" class="section-container">
            <section>
                <h2>Personal de Limpieza</h2>
                <div>
                    <h3>Personal Asignado Hoy</h3>
                    <ul>
                        <?php foreach (($personal_asignado ?? []) as $p): ?>
                        <li>
                            <?php echo htmlspecialchars($p['nombre']); ?> -
                            <?php echo htmlspecialchars($p['turno']); ?>
                            (<?php echo date('g:i A', strtotime($p['hora_inicio'])); ?> -
                             <?php echo date('g:i A', strtotime($p['hora_fin'])); ?>) -
                            Área: <?php echo htmlspecialchars($p['area']); ?>
                        </li>
                        <?php endforeach; ?>
                        <?php if (empty($personal_asignado ?? [])): ?>
                        <li>No hay personal asignado hoy.</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div>
                    <h3>Asignar Personal</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="asignar_personal">
                        <div>
                            <label>Personal de limpieza:</label>
                            <select name="id_personal" required>
                                <?php foreach (($catalogo_personal_limpieza ?? []) as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Turno:</label>
                            <select name="id_turno" required>
                                <?php foreach (($catalogo_turnos ?? []) as $t): ?>
                                <option value="<?php echo $t['id_turno']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Área asignada:</label>
                            <select name="id_area" required>
                                <?php foreach (($catalogo_areas ?? []) as $a): ?>
                                <option value="<?php echo $a['id_area']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit">Asignar</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             SECCIÓN REPORTES
        ═══════════════════════════════════════════════════════════ -->
        <div id="reportes" class="section-container">
            <section>
                <h2>Reportes e Incidencias</h2>
                <div>
                    <h3>Reportes Recientes</h3>
                    <ul>
                        <?php foreach (($reportes_recientes ?? []) as $r): ?>
                        <li>
                            <?php echo date('Y-m-d', strtotime($r['fecha_reporte'])); ?>:
                            <?php echo htmlspecialchars($r['descripcion']); ?> -
                            <?php echo htmlspecialchars($r['estado']); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h3>Reportar Nueva Incidencia</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="reportar_incidencia">
                        <div>
                            <label>Tipo de incidencia:</label>
                            <select name="id_tipo_incidencia" required>
                                <?php foreach (($catalogo_tipos_incidencia ?? []) as $ti): ?>
                                <option value="<?php echo $ti['id_tipo_incidencia']; ?>"><?php echo htmlspecialchars($ti['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Alberca/Área:</label>
                            <select name="id_alberca">
                                <option value="">Seleccione una alberca (opcional)</option>
                                <?php foreach (($catalogo_albercas ?? []) as $a): ?>
                                <option value="<?php echo $a['id_alberca']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Descripción:</label>
                            <textarea name="descripcion" rows="3" required></textarea>
                        </div>
                        <div>
                            <label>Prioridad:</label>
                            <select name="id_prioridad" required>
                                <?php foreach (($catalogo_prioridades ?? []) as $p): ?>
                                <option value="<?php echo $p['id_prioridad']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit">Reportar Incidencia</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             SECCIÓN MANTENIMIENTO
        ═══════════════════════════════════════════════════════════ -->
        <div id="mantenimiento" class="section-container">
            <section>
                <h2>Registro de Mantenimiento</h2>
                <div>
                    <h3>Mantenimientos Programados</h3>
                    <ul>
                        <?php foreach (($proximos_mantenimientos ?? []) as $m): ?>
                        <li>
                            <?php echo $m['fecha_programada']; ?>:
                            <?php echo htmlspecialchars($m['descripcion']); ?> -
                            <?php echo htmlspecialchars($m['alberca']); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h3>Registrar Mantenimiento Realizado</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="registrar_mantenimiento">
                        <div>
                            <label>Alberca:</label>
                            <select name="id_alberca">
                                <option value="">Seleccione (opcional)</option>
                                <?php foreach (($catalogo_albercas ?? []) as $a): ?>
                                <option value="<?php echo $a['id_alberca']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Equipo (opcional):</label>
                            <select name="id_equipo">
                                <option value="">Seleccione un equipo</option>
                                <?php foreach (($catalogo_equipos ?? []) as $e): ?>
                                <option value="<?php echo $e['id_equipo']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Tipo de mantenimiento:</label>
                            <select name="id_tipo_mantenimiento" required>
                                <?php foreach (($catalogo_tipos_mantenimiento ?? []) as $tm): ?>
                                <option value="<?php echo $tm['id_tipo_mantenimiento']; ?>"><?php echo htmlspecialchars($tm['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Fecha programada:</label>
                            <input type="date" name="fecha_programada" required>
                        </div>
                        <div>
                            <label>Descripción del trabajo realizado:</label>
                            <textarea name="descripcion" rows="3" required></textarea>
                        </div>
                        <div>
                            <label>Técnico responsable:</label>
                            <select name="id_tecnico" required>
                                <?php foreach (($catalogo_tecnicos ?? []) as $t): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit">Registrar Mantenimiento</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             SECCIÓN CONTROL DE ACCESO
        ═══════════════════════════════════════════════════════════ -->
        <div id="acceso" class="section-container">
            <section>
                <h2>Control de Acceso y Capacidad</h2>
                <div>
                    <h3>Aforo Actual</h3>
                    <ul>
                        <?php foreach (($albercas ?? []) as $a):
                            $porcentaje = $a['capacidad_maxima'] > 0
                                ? round(($a['aforo_actual'] / $a['capacidad_maxima']) * 100)
                                : 0;
                        ?>
                        <li>
                            <?php echo htmlspecialchars($a['nombre']); ?>:
                            <?php echo $a['aforo_actual']; ?>/<?php echo $a['capacidad_maxima']; ?> personas
                            (<?php echo $porcentaje; ?>%)
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h3>Total de visitantes hoy: <?php echo $visitantes_hoy ?? 0; ?> personas</h3>
                    <h3>Capacidad máxima total: <?php echo $capacidad_total ?? 570; ?> personas</h3>
                    <h3>Porcentaje de ocupación: <?php echo $porcentaje_ocupacion ?? 0; ?>%</h3>
                </div>
                <div>
                    <h3>Registrar Acceso</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="registrar_acceso">
                        <div>
                            <label>Tipo de registro:</label>
                            <select name="tipo_acceso" required>
                                <option value="entrada">Entrada de visitante</option>
                                <option value="salida">Salida de visitante</option>
                            </select>
                        </div>
                        <div>
                            <label>Cantidad de personas:</label>
                            <input type="number" name="cantidad" min="1" max="20" value="1" required>
                        </div>
                        <div>
                            <label>Alberca destino:</label>
                            <select name="id_alberca" required>
                                <?php foreach (($albercas ?? []) as $a): ?>
                                <option value="<?php echo $a['id_alberca']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit">Registrar</button>
                    </form>
                </div>
            </section>
        </div>

    </div><!-- /.content -->

    <!-- Footer -->
    <div class="footer">
        <p>© 2026 Nuestras Albercas - Sistema de Gestión para Encargados</p>
        <p>Última actualización: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         SCRIPTS
    ═══════════════════════════════════════════════════════════ -->
    <script>
        /* ── Navegación entre secciones ── */
        document.querySelectorAll('.nav a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const sectionId = this.getAttribute('data-section');

                document.querySelectorAll('.section-container').forEach(s => s.classList.remove('active'));
                const activeSection = document.getElementById(sectionId);
                if (activeSection) activeSection.classList.add('active');

                document.querySelectorAll('.nav a').forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        /* ── Menú hamburguesa ── */
        const hamburgerBtn  = document.getElementById('hamburgerBtn');
        const nav           = document.querySelector('.nav');
        const content       = document.getElementById('mainContent');
        const dashboardGrid = document.getElementById('dashboardGrid');
        let isExpanded = true;

        function toggleNavbar() {
            if (isExpanded) {
                nav.classList.add('collapsed');
                content.classList.add('expanded');
                if (dashboardGrid) {
                    dashboardGrid.classList.add('grid-expanded');
                    document.documentElement.style.setProperty('--grid-cols', '21');
                }
                hamburgerBtn.classList.add('active');
                isExpanded = false;
            } else {
                nav.classList.remove('collapsed');
                content.classList.remove('expanded');
                if (dashboardGrid) {
                    dashboardGrid.classList.remove('grid-expanded');
                    document.documentElement.style.setProperty('--grid-cols', '20');
                }
                hamburgerBtn.classList.remove('active');
                isExpanded = true;
            }
        }

        if (hamburgerBtn) hamburgerBtn.addEventListener('click', toggleNavbar);

        document.addEventListener('DOMContentLoaded', function() {
            if (dashboardGrid && !dashboardGrid.classList.contains('grid-expanded')) {
                document.documentElement.style.setProperty('--grid-cols', '20');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
    /* ── Gráfica de dona ── */
    document.addEventListener('DOMContentLoaded', function() {
        const operativas   = <?php echo json_encode($operativas   ?? 0); ?>;
        const completas    = <?php echo json_encode($completas    ?? 0); ?>;
        const mantenimiento = <?php echo json_encode($mantenimiento ?? 0); ?>;

        const operativasCountEl   = document.getElementById('operativasCount');
        const completasCountEl    = document.getElementById('completasCount');
        const mantenimientoCountEl = document.getElementById('mantenimientoCount');

        if (operativasCountEl)    operativasCountEl.textContent   = operativas;
        if (completasCountEl)     completasCountEl.textContent    = completas;
        if (mantenimientoCountEl) mantenimientoCountEl.textContent = mantenimiento;

        const donaChartEl = document.getElementById('donaChart');
        if (donaChartEl && (operativas > 0 || completas > 0 || mantenimiento > 0)) {
            const ctx = donaChartEl.getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Operativas', 'Completas', 'Mantenimiento'],
                    datasets: [{
                        data: [operativas, completas, mantenimiento],
                        backgroundColor: ['#2ecc71', '#e74c3c', '#f39c12'],
                        borderColor: 'rgba(255,255,255,0.2)',
                        borderWidth: 2,
                        hoverOffset: 10,
                        cutout: '60%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = operativas + completas + mantenimiento;
                                    const pct   = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} alberca(s) (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    /* ── Mapa interactivo: tooltip + escalado automático ── */
    document.addEventListener('DOMContentLoaded', function() {

        /* -- Datos de calidad (sustituir valores con salida PHP real) -- */
        const calidadData = {
            'principal':     { cloro: <?php echo $calidad_principal['cloro_ppm']    ?? 1.5; ?>, ph: <?php echo $calidad_principal['ph']       ?? 7.2; ?>, temp: <?php echo $calidad_principal['temperatura']  ?? 26; ?> },
            'vista-mar':     { cloro: <?php echo $calidad_vista_mar['cloro_ppm']    ?? 1.3; ?>, ph: <?php echo $calidad_vista_mar['ph']       ?? 7.2; ?>, temp: <?php echo $calidad_vista_mar['temperatura']  ?? 26; ?> },
            'deportiva':     { cloro: <?php echo $calidad_deportiva['cloro_ppm']    ?? 1.4; ?>, ph: <?php echo $calidad_deportiva['ph']       ?? 7.1; ?>, temp: <?php echo $calidad_deportiva['temperatura']  ?? 25; ?> },
            'familiar':      { cloro: <?php echo $calidad_familiar['cloro_ppm']     ?? 1.4; ?>, ph: <?php echo $calidad_familiar['ph']        ?? 7.3; ?>, temp: <?php echo $calidad_familiar['temperatura']   ?? 27; ?> },
            'infantil':      { cloro: <?php echo $calidad_infantil['cloro_ppm']     ?? 1.0; ?>, ph: <?php echo $calidad_infantil['ph']        ?? 7.0; ?>, temp: <?php echo $calidad_infantil['temperatura']   ?? 28; ?> },
            'pool-cafe':     null,
            'vestibularios': null,
            'pina-colada':   null,
            'stage':         null
        };

        const nombresAreas = {
            'principal':     '🏊 Alberca Principal',
            'vista-mar':     '🌊 Alberca Vista al Mar',
            'deportiva':     '🏅 Alberca Deportiva',
            'familiar':      '👨‍👩‍👧 Alberca Familiar',
            'infantil':      '🧸 Alberca Infantil',
            'pool-cafe':     '☕ Pool Cafe',
            'vestibularios': '🚿 Vestibularios',
            'pina-colada':   '🍹 Piña Colada Shack',
            'stage':         '🎭 Stage'
        };

        const tooltip       = document.getElementById('mapaTooltip');
        const tooltipTitulo = document.getElementById('tooltipTitulo');
        const valorCloro    = document.getElementById('valorCloro');
        const valorPh       = document.getElementById('valorPh');
        const valorTemp     = document.getElementById('valorTemp');
        const valorEstado   = document.getElementById('valorEstado');

        function getEstado(cloro, ph, temp) {
            if (!cloro || !ph || !temp) return { texto: 'Sin datos', clase: 'atencion' };
            const cloroOk = cloro >= 1.0 && cloro <= 3.0;
            const phOk    = ph    >= 7.2 && ph    <= 7.8;
            const tempOk  = temp  >= 24  && temp  <= 28;
            if (cloroOk && phOk && tempOk) return { texto: 'Óptimo',             clase: 'optimo'  };
            if (cloro < 1.0 || ph < 7.0 || temp > 30) return { texto: 'Crítico', clase: 'critico' };
            return { texto: 'Requiere atención', clase: 'atencion' };
        }

        function posicionarTooltip(e) {
            const tw = tooltip.offsetWidth  || 220;
            const th = tooltip.offsetHeight || 140;
            const vw = window.innerWidth;
            const vh = window.innerHeight;

            let x = e.clientX - tw - 50;
            let y = e.clientY - th + 10;



            tooltip.style.left = x + 'px';
            tooltip.style.top  = y + 'px';
        }

        function updateTooltip(areaKey, e) {
            const nombre = nombresAreas[areaKey] || 'Área';
            const datos  = calidadData[areaKey];
            tooltipTitulo.textContent = nombre;
            if (datos) {
                const estado = getEstado(datos.cloro, datos.ph, datos.temp);
                valorCloro.textContent  = `${datos.cloro} ppm`;
                valorPh.textContent     = datos.ph;
                valorTemp.textContent   = `${datos.temp}°C`;
                valorEstado.textContent = estado.texto;
                valorEstado.className   = `value estado ${estado.clase}`;
            } else {
                valorCloro.textContent  = '-- ppm';
                valorPh.textContent     = '--';
                valorTemp.textContent   = '--°C';
                valorEstado.textContent = 'Sin monitoreo';
                valorEstado.className   = 'value estado atencion';
            }
            tooltip.classList.add('visible');
            posicionarTooltip(e);
        }

        /*
         * ── SVG overlay: eventos sobre los <polygon> ──────────────────
         * El SVG tiene viewBox="0 0 2560 1449" y width/height=100%,
         * por lo que escala automáticamente con la imagen. No se necesita
         * recalcular coordenadas en ningún momento.
         * ─────────────────────────────────────────────────────────────
         */
        const poligonos = document.querySelectorAll('#mapaSVG .hotspot-area');

        /* Estilos base de los polígonos vía JS (complementan el CSS) */
        poligonos.forEach(pol => {
            pol.style.fill            = 'rgba(255,255,255,0)'; /* transparente por defecto */
            pol.style.stroke          = 'rgba(255,255,255,0)';
            pol.style.transition      = 'fill 0.2s, stroke 0.2s';
            pol.style.strokeWidth     = '3';

            const areaKey = pol.getAttribute('data-alberca');

            pol.addEventListener('mouseenter', function(e) {
                this.style.fill   = 'rgba(255,255,255,0.18)';
                this.style.stroke = 'rgba(255,255,255,0.7)';
                updateTooltip(areaKey, e);
            });

            pol.addEventListener('mousemove', function(e) {
                posicionarTooltip(e);
            });

            pol.addEventListener('mouseleave', function() {
                this.style.fill   = 'rgba(255,255,255,0)';
                this.style.stroke = 'rgba(255,255,255,0)';
                tooltip.classList.remove('visible');
            });
        });
    });
    </script>

</body>
</html>