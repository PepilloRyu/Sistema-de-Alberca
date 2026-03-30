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
            <p>Bienvenido, [Nombre del Encargado]</p>
        </div>
        <div class="logout">
            <a href="../../logout.php">Cerrar sesión</a>
        </div>
    </div>

    <!-- Navegación principal -->
    <div class="nav">
        <a href="#" data-section="inicio" class="active">🏠 INICIO</a>
        <a href="#" data-section="horarios">⏰ Horarios</a>
        <a href="#" data-section="calidad-agua">💧 Calidad del Agua</a>
        <a href="#" data-section="personal">👥 Personal</a>
        <a href="#" data-section="reportes">📋 Reportes</a>
        <a href="#" data-section="mantenimiento">🔧 Mantenimiento</a>
        <a href="#" data-section="acceso">🚪 Control de Acceso</a>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        
        <!-- SECCIÓN INICIO - DASHBOARD PRINCIPAL (SIN SCROLL) -->
        <div id="inicio" class="section-container active">
            <div class="dashboard-grid">

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
                                <span class="stat-mini-number">112</span>
                                <span class="stat-mini-label">Visitantes hoy</span>
                            </div>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-icon">🏊</span>
                            <div>
                                <span class="stat-mini-number">19%</span>
                                <span class="stat-mini-label">Ocupación</span>
                            </div>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-icon">⚠️</span>
                            <div>
                                <span class="stat-mini-number">1</span>
                                <span class="stat-mini-label">Incidencias</span>
                            </div>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-icon">✅</span>
                            <div>
                                <span class="stat-mini-number">4/5</span>
                                <span class="stat-mini-label">Albercas</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado de albercas (columna izquierda) -->
                <div class="card compact">
                    <h3>📊 Estado de Albercas</h3>
                    <div class="pool-status-list compact">
                        <div class="pool-status-item">
                            <span class="pool-name">Principal</span>
                            <span class="pool-status-badge operational">Operativa</span>
                            <div class="pool-progress"><div class="progress-bar" style="width: 22%"></div></div>
                            <span class="pool-stats">45/200</span>
                        </div>
                        <div class="pool-status-item">
                            <span class="pool-name">Familiar</span>
                            <span class="pool-status-badge operational">Operativa</span>
                            <div class="pool-progress"><div class="progress-bar" style="width: 21%"></div></div>
                            <span class="pool-stats">32/150</span>
                        </div>
                        <div class="pool-status-item">
                            <span class="pool-name">Infantil</span>
                            <span class="pool-status-badge maintenance">Mantenimiento</span>
                            <div class="pool-progress"><div class="progress-bar" style="width: 18%"></div></div>
                            <span class="pool-stats">15/80</span>
                        </div>
                        <div class="pool-status-item">
                            <span class="pool-name">Vista al Mar</span>
                            <span class="pool-status-badge operational">Operativa</span>
                            <div class="pool-progress"><div class="progress-bar" style="width: 30%"></div></div>
                            <span class="pool-stats">12/40</span>
                        </div>
                        <div class="pool-status-item">
                            <span class="pool-name">Deportiva</span>
                            <span class="pool-status-badge operational">Operativa</span>
                            <div class="pool-progress"><div class="progress-bar" style="width: 8%"></div></div>
                            <span class="pool-stats">8/100</span>
                        </div>
                    </div>
                </div>

                <!-- Calidad del agua (columna derecha) -->
                <div class="card compact">
                    <h3>💧 Calidad del Agua</h3>
                    <div class="water-quality-summary compact">
                        <div class="quality-item">
                            <span class="quality-label">Cloro</span>
                            <span class="quality-value">1.4 ppm</span>
                            <span class="quality-status good">Óptimo</span>
                        </div>
                        <div class="quality-item">
                            <span class="quality-label">pH</span>
                            <span class="quality-value">7.2</span>
                            <span class="quality-status good">Balanceado</span>
                        </div>
                        <div class="quality-item">
                            <span class="quality-label">Temperatura</span>
                            <span class="quality-value">25.2°C</span>
                            <span class="quality-status normal">Normal</span>
                        </div>
                    </div>
                </div>

                <!-- Mantenimientos programados -->
                <div class="card compact">
                    <h3>🔧 Próximos Mantenimientos</h3>
                    <ul class="maintenance-list compact">
                        <li><span class="date">25/03</span><span class="task">Cambio filtros</span><span class="pool">Principal</span></li>
                        <li><span class="date">28/03</span><span class="task">Limpieza bombas</span><span class="pool">Familiar</span></li>
                        <li><span class="date">01/04</span><span class="task">Revisión cloración</span><span class="pool">Todas</span></li>
                    </ul>
                </div>

                <!-- Alertas importantes -->
                <div class="card compact alert-card">
                    <h3>⚠️ Alertas</h3>
                    <div class="alert-item">
                        <span class="alert-icon">🔴</span>
                        <div class="alert-content">
                            <strong>Alberca Infantil en mantenimiento</strong>
                            <p>Reparación estimada 2 días</p>
                        </div>
                    </div>
                    <div class="alert-item success">
                        <span class="alert-icon">✅</span>
                        <div class="alert-content">
                            <strong>Mantenimiento completado</strong>
                            <p>Cambio filtros Principal</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- SECCIÓN HORARIOS -->
        <div id="horarios" class="section-container">
            <section>
                <h2>Control de Horarios</h2>
                <div>
                    <h3>Horario Actual</h3>
                    <p>Apertura: 9:00 AM</p>
                    <p>Cierre: 6:00 PM</p>
                </div>
                <div>
                    <h3>Modificar Horario</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Hora de apertura:</label>
                            <input type="time" name="apertura" value="09:00">
                        </div>
                        <div>
                            <label>Hora de cierre:</label>
                            <input type="time" name="cierre" value="18:00">
                        </div>
                        <div>
                            <label>Fecha especial:</label>
                            <input type="date" name="fecha_especial">
                        </div>
                        <button type="submit">Actualizar Horario</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- SECCIÓN CALIDAD DEL AGUA -->
        <div id="calidad-agua" class="section-container">
            <section>
                <h2>Calidad del Agua</h2>
                <div>
                    <h3>Parámetros Actuales</h3>
                    <ul>
                        <li>Alberca Principal: Cloro 1.5 ppm | pH 7.2 | Temperatura 26°C</li>
                        <li>Alberca Familiar: Cloro 1.3 ppm | pH 7.0 | Temperatura 25°C</li>
                        <li>Alberca Infantil: Cloro 1.0 ppm | pH 7.1 | Temperatura 28°C</li>
                        <li>Alberca Vista al Mar: Cloro 1.4 ppm | pH 7.2 | Temperatura 24°C</li>
                        <li>Alberca Deportiva: Cloro 1.6 ppm | pH 7.3 | Temperatura 23°C</li>
                    </ul>
                </div>
                <div>
                    <h3>Registrar Nuevos Parámetros</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Seleccionar alberca:</label>
                            <select name="alberca">
                                <option value="principal">Alberca Principal</option>
                                <option value="familiar">Alberca Familiar</option>
                                <option value="infantil">Alberca Infantil</option>
                                <option value="vista_mar">Alberca Vista al Mar</option>
                                <option value="deportiva">Alberca Deportiva</option>
                            </select>
                        </div>
                        <div>
                            <label>Cloro (ppm):</label>
                            <input type="number" step="0.1" name="cloro" placeholder="1.0 - 3.0">
                        </div>
                        <div>
                            <label>pH:</label>
                            <input type="number" step="0.1" name="ph" placeholder="7.2 - 7.8">
                        </div>
                        <div>
                            <label>Temperatura (°C):</label>
                            <input type="number" step="0.1" name="temperatura" placeholder="24 - 28">
                        </div>
                        <button type="submit">Registrar</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- SECCIÓN PERSONAL -->
        <div id="personal" class="section-container">
            <section>
                <h2>Personal de Limpieza</h2>
                <div>
                    <h3>Personal Asignado Hoy</h3>
                    <ul>
                        <li>María López - Turno Matutino (8:00 AM - 2:00 PM)</li>
                        <li>Carlos Ruiz - Turno Vespertino (2:00 PM - 8:00 PM)</li>
                        <li>Ana Martínez - Turno Nocturno (8:00 PM - 2:00 AM)</li>
                    </ul>
                </div>
                <div>
                    <h3>Asignar Personal</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Nombre del personal:</label>
                            <input type="text" name="nombre_personal" placeholder="Nombre completo">
                        </div>
                        <div>
                            <label>Turno:</label>
                            <select name="turno">
                                <option value="matutino">Matutino (8:00 AM - 2:00 PM)</option>
                                <option value="vespertino">Vespertino (2:00 PM - 8:00 PM)</option>
                                <option value="nocturno">Nocturno (8:00 PM - 2:00 AM)</option>
                            </select>
                        </div>
                        <div>
                            <label>Área asignada:</label>
                            <select name="area">
                                <option value="principal">Alberca Principal</option>
                                <option value="familiar">Alberca Familiar</option>
                                <option value="infantil">Alberca Infantil</option>
                                <option value="vista_mar">Alberca Vista al Mar</option>
                                <option value="deportiva">Alberca Deportiva</option>
                                <option value="areas_comunes">Áreas Comunes</option>
                            </select>
                        </div>
                        <button type="submit">Asignar</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- SECCIÓN REPORTES -->
        <div id="reportes" class="section-container">
            <section>
                <h2>Reportes e Incidencias</h2>
                <div>
                    <h3>Reportes Recientes</h3>
                    <ul>
                        <li>2026-03-20: Fuga de agua en alberca infantil - En reparación</li>
                        <li>2026-03-19: Cambio de filtros en alberca principal - Completado</li>
                        <li>2026-03-18: Limpieza profunda de áreas comunes - Realizada</li>
                    </ul>
                </div>
                <div>
                    <h3>Reportar Nueva Incidencia</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Tipo de incidencia:</label>
                            <select name="tipo_incidencia">
                                <option value="fuga">Fuga de agua</option>
                                <option value="quimico">Problema químico (cloro/pH)</option>
                                <option value="mecanico">Fallo mecánico</option>
                                <option value="limpieza">Problema de limpieza</option>
                                <option value="seguridad">Problema de seguridad</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div>
                            <label>Alberca/Área:</label>
                            <select name="area_incidencia">
                                <option value="principal">Alberca Principal</option>
                                <option value="familiar">Alberca Familiar</option>
                                <option value="infantil">Alberca Infantil</option>
                                <option value="vista_mar">Alberca Vista al Mar</option>
                                <option value="deportiva">Alberca Deportiva</option>
                                <option value="areas_comunes">Áreas Comunes</option>
                            </select>
                        </div>
                        <div>
                            <label>Descripción:</label>
                            <textarea name="descripcion" rows="3" placeholder="Describa la incidencia detalladamente..."></textarea>
                        </div>
                        <div>
                            <label>Prioridad:</label>
                            <select name="prioridad">
                                <option value="baja">Baja</option>
                                <option value="media">Media</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                        <button type="submit">Reportar Incidencia</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- SECCIÓN MANTENIMIENTO -->
        <div id="mantenimiento" class="section-container">
            <section>
                <h2>Registro de Mantenimiento</h2>
                <div>
                    <h3>Mantenimientos Programados</h3>
                    <ul>
                        <li>2026-03-25: Cambio de filtros - Alberca Principal</li>
                        <li>2026-03-28: Limpieza de bombas - Alberca Familiar</li>
                        <li>2026-04-01: Revisión de sistema de cloración - Todas las albercas</li>
                    </ul>
                </div>
                <div>
                    <h3>Registrar Mantenimiento Realizado</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Fecha:</label>
                            <input type="date" name="fecha_mantenimiento" required>
                        </div>
                        <div>
                            <label>Alberca/Área:</label>
                            <select name="area_mantenimiento">
                                <option value="principal">Alberca Principal</option>
                                <option value="familiar">Alberca Familiar</option>
                                <option value="infantil">Alberca Infantil</option>
                                <option value="vista_mar">Alberca Vista al Mar</option>
                                <option value="deportiva">Alberca Deportiva</option>
                                <option value="equipos">Equipos y Bombas</option>
                            </select>
                        </div>
                        <div>
                            <label>Tipo de mantenimiento:</label>
                            <select name="tipo_mantenimiento">
                                <option value="preventivo">Preventivo</option>
                                <option value="correctivo">Correctivo</option>
                                <option value="emergencia">Emergencia</option>
                            </select>
                        </div>
                        <div>
                            <label>Descripción del trabajo realizado:</label>
                            <textarea name="descripcion_mantenimiento" rows="3" placeholder="Describa el mantenimiento realizado..."></textarea>
                        </div>
                        <div>
                            <label>Técnico responsable:</label>
                            <input type="text" name="tecnico" placeholder="Nombre del técnico">
                        </div>
                        <button type="submit">Registrar Mantenimiento</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- SECCIÓN CONTROL DE ACCESO -->
        <div id="acceso" class="section-container">
            <section>
                <h2>Control de Acceso y Capacidad</h2>
                <div>
                    <h3>Aforo Actual</h3>
                    <ul>
                        <li>Alberca Principal: 45/200 personas (22%)</li>
                        <li>Alberca Familiar: 32/150 personas (21%)</li>
                        <li>Alberca Infantil: 15/80 personas (18%)</li>
                        <li>Alberca Vista al Mar: 12/40 personas (30%)</li>
                        <li>Alberca Deportiva: 8/100 personas (8%)</li>
                    </ul>
                </div>
                <div>
                    <h3>Total de visitantes hoy: 112 personas</h3>
                    <h3>Capacidad máxima total: 570 personas</h3>
                    <h3>Porcentaje de ocupación: 19%</h3>
                </div>
                <div>
                    <h3>Registrar Acceso</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Tipo de registro:</label>
                            <select name="tipo_acceso">
                                <option value="entrada">Entrada de visitante</option>
                                <option value="salida">Salida de visitante</option>
                            </select>
                        </div>
                        <div>
                            <label>Cantidad de personas:</label>
                            <input type="number" name="cantidad" min="1" max="20" value="1">
                        </div>
                        <div>
                            <label>Alberca destino:</label>
                            <select name="alberca_destino">
                                <option value="principal">Alberca Principal</option>
                                <option value="familiar">Alberca Familiar</option>
                                <option value="infantil">Alberca Infantil</option>
                                <option value="vista_mar">Alberca Vista al Mar</option>
                                <option value="deportiva">Alberca Deportiva</option>
                            </select>
                        </div>
                        <button type="submit">Registrar</button>
                    </form>
                </div>
            </section>
        </div>

    </div>


    <script>
        // Navegación entre secciones
        document.querySelectorAll('.nav a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const sectionId = this.getAttribute('data-section');
                
                document.querySelectorAll('.section-container').forEach(section => {
                    section.classList.remove('active');
                });
                
                const activeSection = document.getElementById(sectionId);
                if (activeSection) {
                    activeSection.classList.add('active');
                }
                
                document.querySelectorAll('.nav a').forEach(navLink => {
                    navLink.classList.remove('active');
                });
                this.classList.add('active');
                
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    </script>

</body>
</html>