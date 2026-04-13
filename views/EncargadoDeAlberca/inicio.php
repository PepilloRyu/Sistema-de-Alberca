<!-- SECCIÓN INICIO - DASHBOARD PRINCIPAL -->
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
                        $porcentaje = $alberca['capacidad_maxima'] > 0 ? round(($alberca['aforo_actual'] / $alberca['capacidad_maxima']) * 100) : 0;
                        
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
                        <div class="pool-progress"><div class="progress-bar" style="width: <?php echo $porcentaje; ?>%"></div></div>
                        <span class="pool-stats"><?php echo $alberca['aforo_actual']; ?>/<?php echo $alberca['capacidad_maxima']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tarjeta Calidad del Agua -->
        <div class="dashboard-card card-calidad-agua">
            <h3>💧 Calidad del Agua</h3>
            <div class="water-quality-summary">
                <?php 
                $promedios = ['cloro' => 0, 'ph' => 0, 'temp' => 0];
                $count = count($ultima_calidad ?? []);
                foreach (($ultima_calidad ?? []) as $c) {
                    $promedios['cloro'] += $c['cloro_ppm'];
                    $promedios['ph'] += $c['ph'];
                    $promedios['temp'] += $c['temperatura'];
                }
                if ($count > 0) {
                    $promedios['cloro'] /= $count;
                    $promedios['ph'] /= $count;
                    $promedios['temp'] /= $count;
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