<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Limpieza - Nuestras Albercas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/PersonalDeLimpieza/index.css">
</head>
<body>

    <!-- Header con información del usuario -->
    <div class="header">
        <div class="user-info">
            <h1>Panel de Personal de Limpieza</h1>
            <p>Bienvenido, [Nombre del Personal]</p>
        </div>
        <div class="logout">
            <a href="../../logout.php">Cerrar sesión</a>
        </div>
    </div>

    <!-- Navegación principal -->
    <div class="nav">
        <a href="#" data-section="inicio" class="active">🏠 INICIO</a>
        <a href="#" data-section="limpieza-alberca">🧹 Limpieza de Alberca</a>
        <a href="#" data-section="filtros">🔧 Limpieza de Filtros</a>
        <a href="#" data-section="basura">🗑️ Recolección de Basura</a>
        <a href="#" data-section="banios">🚽 Limpieza de Baños</a>
        <a href="#" data-section="superficie">💧 Limpieza de Superficie</a>
        <a href="#" data-section="reportes">📋 Reportes</a>
        <a href="#" data-section="checklist">✅ Checklist Diario</a>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        
        <!-- SECCIÓN INICIO - DASHBOARD PRINCIPAL -->
        <div id="inicio" class="section-container active">
            <div class="dashboard-grid">

                <!-- Fila superior: bienvenida + estadísticas -->
                <div class="welcome-stats">
                    <div class="welcome">
                        <h2>Bienvenido, Personal de Limpieza</h2>
                        <p>Panel de control de tareas de limpieza</p>
                        <div class="date-time">
                            <?php echo date('l, d \d\e F \d\e Y'); ?>
                            <span class="time"><?php echo date('h:i A'); ?></span>
                        </div>
                    </div>
                    <div class="stats-mini">
                        <div class="stat-mini">
                            <span class="stat-mini-icon">✅</span>
                            <div>
                                <span class="stat-mini-number">4/6</span>
                                <span class="stat-mini-label">Tareas completadas</span>
                            </div>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-icon">⚠️</span>
                            <div>
                                <span class="stat-mini-number">2</span>
                                <span class="stat-mini-label">Pendientes</span>
                            </div>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-icon">⏰</span>
                            <div>
                                <span class="stat-mini-number">3h</span>
                                <span class="stat-mini-label">Turno restante</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checklist rápido -->
                <div class="card compact">
                    <h3>✅ Checklist de Hoy</h3>
                    <div class="checklist-quick">
                        <div class="check-item completed">
                            <span class="check-icon">✓</span>
                            <span>Limpieza de Alberca Principal</span>
                        </div>
                        <div class="check-item completed">
                            <span class="check-icon">✓</span>
                            <span>Recolección de basura AM</span>
                        </div>
                        <div class="check-item">
                            <span class="check-icon">○</span>
                            <span>Limpieza de filtros</span>
                        </div>
                        <div class="check-item">
                            <span class="check-icon">○</span>
                            <span>Limpieza de baños</span>
                        </div>
                        <div class="check-item completed">
                            <span class="check-icon">✓</span>
                            <span>Limpieza de superficie</span>
                        </div>
                    </div>
                    <button class="btn-view-all">Ver checklist completo →</button>
                </div>

                <!-- Áreas asignadas -->
                <div class="card compact">
                    <h3>📍 Áreas Asignadas Hoy</h3>
                    <div class="areas-list">
                        <div class="area-item">
                            <span class="area-icon">🏊</span>
                            <div>
                                <strong>Alberca Principal</strong>
                                <p>Turno Matutino (8:00 - 14:00)</p>
                            </div>
                            <span class="status active">Activo</span>
                        </div>
                        <div class="area-item">
                            <span class="area-icon">🚽</span>
                            <div>
                                <strong>Baños Área Norte</strong>
                                <p>Limpieza cada 2 horas</p>
                            </div>
                            <span class="status pending">Pendiente</span>
                        </div>
                        <div class="area-item">
                            <span class="area-icon">🗑️</span>
                            <div>
                                <strong>Áreas Comunes</strong>
                                <p>Recolección de basura</p>
                            </div>
                            <span class="status active">En proceso</span>
                        </div>
                    </div>
                </div>

                <!-- Últimos reportes -->
                <div class="card compact">
                    <h3>📋 Últimos Reportes</h3>
                    <ul class="reports-list">
                        <li>
                            <span class="report-date">10:30 AM</span>
                            <span>Filtros limpiados - Alberca Principal</span>
                            <span class="badge-success">Completado</span>
                        </li>
                        <li>
                            <span class="report-date">09:15 AM</span>
                            <span>Recolección de basura completada</span>
                            <span class="badge-success">Completado</span>
                        </li>
                        <li>
                            <span class="report-date">08:00 AM</span>
                            <span>Inicio de turno</span>
                            <span class="badge-info">Registrado</span>
                        </li>
                    </ul>
                </div>

                <!-- Alertas/Notificaciones -->
                <div class="card compact alert-card">
                    <h3>⚠️ Notificaciones</h3>
                    <div class="alert-item">
                        <span class="alert-icon">🔔</span>
                        <div class="alert-content">
                            <strong>Limpieza de filtros programada</strong>
                            <p>Alberca Familiar - Realizar antes de las 12:00 PM</p>
                        </div>
                    </div>
                    <div class="alert-item warning">
                        <span class="alert-icon">⚠️</span>
                        <div class="alert-content">
                            <strong>Alta afluencia de visitantes</strong>
                            <p>Mayor frecuencia de limpieza de baños requerida</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- SECCIÓN LIMPIEZA DE ALBERCA -->
        <div id="limpieza-alberca" class="section-container">
            <section>
                <h2>🧹 Limpieza de Alberca</h2>
                <div>
                    <h3>Áreas de Alberca</h3>
                    <div class="pool-areas">
                        <div class="area-card">
                            <h4>Alberca Principal</h4>
                            <p>Última limpieza: 08:30 AM</p>
                            <div class="status-badge">Pendiente</div>
                            <button class="btn-action">Registrar limpieza</button>
                        </div>
                        <div class="area-card">
                            <h4>Alberca Familiar</h4>
                            <p>Última limpieza: 09:00 AM</p>
                            <div class="status-badge completed">Completada</div>
                            <button class="btn-action disabled" disabled>Completado</button>
                        </div>
                        <div class="area-card">
                            <h4>Alberca Infantil</h4>
                            <p>Última limpieza: -</p>
                            <div class="status-badge">Pendiente</div>
                            <button class="btn-action">Registrar limpieza</button>
                        </div>
                        <div class="area-card">
                            <h4>Alberca Vista al Mar</h4>
                            <p>Última limpieza: 07:45 AM</p>
                            <div class="status-badge completed">Completada</div>
                            <button class="btn-action disabled" disabled>Completado</button>
                        </div>
                        <div class="area-card">
                            <h4>Alberca Deportiva</h4>
                            <p>Última limpieza: -</p>
                            <div class="status-badge">Pendiente</div>
                            <button class="btn-action">Registrar limpieza</button>
                        </div>
                    </div>
                </div>
                <div>
                    <h3>Registrar Limpieza Realizada</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Alberca:</label>
                            <select name="alberca">
                                <option value="principal">Alberca Principal</option>
                                <option value="familiar">Alberca Familiar</option>
                                <option value="infantil">Alberca Infantil</option>
                                <option value="vista_mar">Alberca Vista al Mar</option>
                                <option value="deportiva">Alberca Deportiva</option>
                            </select>
                        </div>
                        <div>
                            <label>Tipo de limpieza:</label>
                            <select name="tipo_limpieza">
                                <option value="fondo">Limpieza de fondo</option>
                                <option value="paredes">Limpieza de paredes</option>
                                <option value="escaleras">Limpieza de escaleras</option>
                                <option value="general">Limpieza general</option>
                            </select>
                        </div>
                        <div>
                            <label>Observaciones:</label>
                            <textarea name="observaciones" rows="2" placeholder="Observaciones de la limpieza..."></textarea>
                        </div>
                        <div>
                            <label>Productos utilizados:</label>
                            <input type="text" name="productos" placeholder="Productos de limpieza utilizados">
                        </div>
                        <button type="submit">Registrar Limpieza</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- SECCIÓN LIMPIEZA DE FILTROS -->
        <div id="filtros" class="section-container">
            <section>
                <h2>🔧 Limpieza de Filtros o Rejillas</h2>
                <div>
                    <h3>Estado de Filtros</h3>
                    <div class="filters-status">
                        <div class="filter-item">
                            <span>Filtros Alberca Principal</span>
                            <span class="status-badge warning">Requiere limpieza</span>
                            <span>Última limpieza: 15/03/2026</span>
                        </div>
                        <div class="filter-item">
                            <span>Filtros Alberca Familiar</span>
                            <span class="status-badge success">Óptimo</span>
                            <span>Última limpieza: 18/03/2026</span>
                        </div>
                        <div class="filter-item">
                            <span>Filtros Alberca Infantil</span>
                            <span class="status-badge warning">Requiere limpieza</span>
                            <span>Última limpieza: 10/03/2026</span>
                        </div>
                        <div class="filter-item">
                            <span>Rejillas de desagüe</span>
                            <span class="status-badge danger">Obstruidas</span>
                            <span>Revisión urgente</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3>Registrar Mantenimiento de Filtros</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Equipo/Filtro:</label>
                            <select name="filtro">
                                <option value="principal">Filtros - Alberca Principal</option>
                                <option value="familiar">Filtros - Alberca Familiar</option>
                                <option value="infantil">Filtros - Alberca Infantil</option>
                                <option value="vista_mar">Filtros - Vista al Mar</option>
                                <option value="deportiva">Filtros - Alberca Deportiva</option>
                                <option value="rejillas">Rejillas de desagüe</option>
                            </select>
                        </div>
                        <div>
                            <label>Tipo de limpieza:</label>
                            <select name="tipo_filtro">
                                <option value="preventiva">Limpieza preventiva</option>
                                <option value="correctiva">Limpieza correctiva</option>
                                <option value="emergencia">Emergencia</option>
                            </select>
                        </div>
                        <div>
                            <label>Estado antes de limpieza:</label>
                            <textarea name="estado_antes" rows="2" placeholder="Describa el estado del filtro..."></textarea>
                        </div>
                        <div>
                            <label>Estado después de limpieza:</label>
                            <textarea name="estado_despues" rows="2" placeholder="Describa el resultado..."></textarea>
                        </div>
                        <button type="submit">Registrar Limpieza</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- SECCIÓN RECOLECCIÓN DE BASURA -->
        <div id="basura" class="section-container">
            <section>
                <h2>🗑️ Recolección de Basura</h2>
                <div>
                    <h3>Puntos de Recolección</h3>
                    <div class="trash-points">
                        <div class="trash-item">
                            <span>🗑️ Área de Albercas</span>
                            <span>Estado: <span class="warning">Medio lleno</span></span>
                            <button class="btn-small">Registrar recolección</button>
                        </div>
                        <div class="trash-item">
                            <span>🗑️ Área de Palapas</span>
                            <span>Estado: <span class="danger">Lleno</span></span>
                            <button class="btn-small urgent">Recoger ahora</button>
                        </div>
                        <div class="trash-item">
                            <span>🗑️ Área de Estacionamiento</span>
                            <span>Estado: <span class="success">Vacío</span></span>
                            <button class="btn-small">Registrar recolección</button>
                        </div>
                        <div class="trash-item">
                            <span>🗑️ Área de Vestidores</span>
                            <span>Estado: <span class="warning">Medio lleno</span></span>
                            <button class="btn-small">Registrar recolección</button>
                        </div>
                    </div>
                </div>
                <div>
                    <h3>Registrar Recolección</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Punto de recolección:</label>
                            <select name="punto">
                                <option value="albercas">Área de Albercas</option>
                                <option value="palapas">Área de Palapas</option>
                                <option value="estacionamiento">Estacionamiento</option>
                                <option value="vestidores">Vestidores</option>
                                <option value="areas_comunes">Áreas Comunes</option>
                            </select>
                        </div>
                        <div>
                            <label>Cantidad de bolsas recolectadas:</label>
                            <input type="number" name="bolsas" min="1" placeholder="Número de bolsas">
                        </div>
                        <div>
                            <label>Tipo de residuo:</label>
                            <select name="tipo_residuo">
                                <option value="general">General</option>
                                <option value="reciclable">Reciclable</option>
                                <option value="organico">Orgánico</option>
                                <option value="peligroso">Peligroso</option>
                            </select>
                        </div>
                        <div>
                            <label>Observaciones:</label>
                            <textarea name="obs_basura" rows="2" placeholder="Observaciones..."></textarea>
                        </div>
                        <button type="submit">Registrar Recolección</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- SECCIÓN LIMPIEZA DE BAÑOS -->
        <div id="banios" class="section-container">
            <section>
                <h2>🚽 Limpieza de Baños y Áreas Cercanas</h2>
                <div>
                    <h3>Estado de Baños</h3>
                    <div class="bathrooms-status">
                        <div class="bathroom-item">
                            <h4>Baños Zona Norte</h4>
                            <p>Última limpieza: 09:30 AM</p>
                            <div class="status-badge success">Limpio</div>
                            <button class="btn-small">Registrar limpieza</button>
                        </div>
                        <div class="bathroom-item">
                            <h4>Baños Zona Sur</h4>
                            <p>Última limpieza: 08:45 AM</p>
                            <div class="status-badge warning">Requiere atención</div>
                            <button class="btn-small urgent">Limpiar ahora</button>
                        </div>
                        <div class="bathroom-item">
                            <h4>Baños Área de Albercas</h4>
                            <p>Última limpieza: 10:00 AM</p>
                            <div class="status-badge success">Limpio</div>
                            <button class="btn-small">Registrar limpieza</button>
                        </div>
                        <div class="bathroom-item">
                            <h4>Vestidores</h4>
                            <p>Última limpieza: 09:15 AM</p>
                            <div class="status-badge warning">Revisión necesaria</div>
                            <button class="btn-small">Registrar limpieza</button>
                        </div>
                    </div>
                </div>
                <div>
                    <h3>Registrar Limpieza de Baños</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Área:</label>
                            <select name="area_banios">
                                <option value="norte">Baños Zona Norte</option>
                                <option value="sur">Baños Zona Sur</option>
                                <option value="albercas">Baños Área Albercas</option>
                                <option value="vestidores">Vestidores</option>
                            </select>
                        </div>
                        <div>
                            <label>Insumos utilizados:</label>
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="insumos[]" value="cloro"> Cloro</label>
                                <label><input type="checkbox" name="insumos[]" value="jabon"> Jabón</label>
                                <label><input type="checkbox" name="insumos[]" value="papel"> Papel higiénico</label>
                                <label><input type="checkbox" name="insumos[]" value="toallas"> Toallas de papel</label>
                                <label><input type="checkbox" name="insumos[]" value="desinfectante"> Desinfectante</label>
                            </div>
                        </div>
                        <div>
                            <label>Estado final:</label>
                            <select name="estado_final">
                                <option value="limpio">Limpio y desinfectado</option>
                                <option value="requiere_atencion">Requiere atención adicional</option>
                                <option value="falta_insumos">Faltan insumos</option>
                            </select>
                        </div>
                        <div>
                            <label>Observaciones:</label>
                            <textarea name="obs_banios" rows="2" placeholder="Observaciones..."></textarea>
                        </div>
                        <button type="submit">Registrar Limpieza</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- SECCIÓN LIMPIEZA DE SUPERFICIE DEL AGUA -->
        <div id="superficie" class="section-container">
            <section>
                <h2>💧 Limpieza de Superficie del Agua</h2>
                <div>
                    <h3>Estado de Superficie</h3>
                    <div class="surface-status">
                        <div class="surface-item">
                            <span>Alberca Principal</span>
                            <div class="surface-indicator clean">Limpia</div>
                            <span>Último skimmer: 08:30 AM</span>
                        </div>
                        <div class="surface-item">
                            <span>Alberca Familiar</span>
                            <div class="surface-indicator clean">Limpia</div>
                            <span>Último skimmer: 09:00 AM</span>
                        </div>
                        <div class="surface-item">
                            <span>Alberca Infantil</span>
                            <div class="surface-indicator warning">Hojas en superficie</div>
                            <span>Requiere atención</span>
                        </div>
                        <div class="surface-item">
                            <span>Alberca Vista al Mar</span>
                            <div class="surface-indicator clean">Limpia</div>
                            <span>Último skimmer: 08:45 AM</span>
                        </div>
                        <div class="surface-item">
                            <span>Alberca Deportiva</span>
                            <div class="surface-indicator clean">Limpia</div>
                            <span>Último skimmer: 09:15 AM</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3>Registrar Limpieza de Superficie</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Alberca:</label>
                            <select name="alberca_superficie">
                                <option value="principal">Alberca Principal</option>
                                <option value="familiar">Alberca Familiar</option>
                                <option value="infantil">Alberca Infantil</option>
                                <option value="vista_mar">Alberca Vista al Mar</option>
                                <option value="deportiva">Alberca Deportiva</option>
                            </select>
                        </div>
                        <div>
                            <label>Método utilizado:</label>
                            <select name="metodo">
                                <option value="skimmer">Skimmer manual</option>
                                <option value="aspirador">Aspirador de superficie</option>
                                <option value="robot">Robot limpiador</option>
                                <option value="red">Red recolectora</option>
                            </select>
                        </div>
                        <div>
                            <label>Residuos recolectados:</label>
                            <select name="residuos">
                                <option value="ninguno">Ninguno visible</option>
                                <option value="poco">Pocos residuos</option>
                                <option value="moderado">Moderada cantidad</option>
                                <option value="excesivo">Excesiva cantidad</option>
                            </select>
                        </div>
                        <div>
                            <label>Tipo de residuos:</label>
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="tipo_residuos[]" value="hojas"> Hojas</label>
                                <label><input type="checkbox" name="tipo_residuos[]" value="insectos"> Insectos</label>
                                <label><input type="checkbox" name="tipo_residuos[]" value="basura"> Basura</label>
                                <label><input type="checkbox" name="tipo_residuos[]" value="aceite"> Aceite/grasa</label>
                            </div>
                        </div>
                        <button type="submit">Registrar Limpieza</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- SECCIÓN REPORTES -->
        <div id="reportes" class="section-container">
            <section>
                <h2>📋 Reportes de Limpieza</h2>
                <div>
                    <h3>Reportes Recientes</h3>
                    <div class="recent-reports">
                        <div class="report-item">
                            <span class="report-date">2026-03-20 10:30 AM</span>
                            <span>Limpieza de filtros - Alberca Principal</span>
                            <span class="badge-success">Completado</span>
                        </div>
                        <div class="report-item">
                            <span class="report-date">2026-03-20 09:00 AM</span>
                            <span>Recolección de basura - Área Palapas</span>
                            <span class="badge-success">Completado</span>
                        </div>
                        <div class="report-item">
                            <span class="report-date">2026-03-20 08:30 AM</span>
                            <span>Limpieza de superficie - Alberca Principal</span>
                            <span class="badge-success">Completado</span>
                        </div>
                        <div class="report-item">
                            <span class="report-date">2026-03-19 05:00 PM</span>
                            <span>Limpieza general de baños</span>
                            <span class="badge-success">Completado</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3>Reportar Incidencia / Problema</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Tipo de incidencia:</label>
                            <select name="tipo_incidencia_limp">
                                <option value="equipo">Fallo en equipo de limpieza</option>
                                <option value="insumos">Falta de insumos</option>
                                <option value="infraestructura">Problema en infraestructura</option>
                                <option value="seguridad">Problema de seguridad</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div>
                            <label>Área afectada:</label>
                            <input type="text" name="area_afectada" placeholder="Especifique el área">
                        </div>
                        <div>
                            <label>Descripción detallada:</label>
                            <textarea name="desc_incidencia" rows="3" placeholder="Describa la incidencia..."></textarea>
                        </div>
                        <div>
                            <label>Prioridad:</label>
                            <select name="prioridad_limp">
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

        <!-- SECCIÓN CHECKLIST DIARIO -->
        <div id="checklist" class="section-container">
            <section>
                <h2>✅ Checklist Diario de Limpieza</h2>
                <div>
                    <h3>Tareas del Día: <?php echo date('d/m/Y'); ?></h3>
                    <div class="checklist-daily">
                        <div class="checklist-group">
                            <h4>🌅 Turno Matutino (8:00 - 14:00)</h4>
                            <label class="checklist-item">
                                <input type="checkbox"> Limpieza de fondo de alberca principal
                            </label>
                            <label class="checklist-item">
                                <input type="checkbox"> Recolección de basura en áreas comunes
                            </label>
                            <label class="checklist-item">
                                <input type="checkbox"> Limpieza de baños zona norte
                            </label>
                            <label class="checklist-item">
                                <input type="checkbox"> Limpieza de superficie con skimmer
                            </label>
                            <label class="checklist-item">
                                <input type="checkbox"> Revisión y limpieza de filtros
                            </label>
                        </div>
                        <div class="checklist-group">
                            <h4>🌞 Turno Vespertino (14:00 - 20:00)</h4>
                            <label class="checklist-item">
                                <input type="checkbox"> Limpieza de fondo alberca familiar
                            </label>
                            <label class="checklist-item">
                                <input type="checkbox"> Recolección de basura vespertina
                            </label>
                            <label class="checklist-item">
                                <input type="checkbox"> Limpieza de baños zona sur
                            </label>
                            <label class="checklist-item">
                                <input type="checkbox"> Limpieza de superficie post-cierre
                            </label>
                            <label class="checklist-item">
                                <input type="checkbox"> Revisión final de áreas
                            </label>
                        </div>
                    </div>
                </div>
                <div>
                    <h3>Registrar Checklist Final</h3>
                    <form action="#" method="POST">
                        <div>
                            <label>Turno:</label>
                            <select name="turno_checklist">
                                <option value="matutino">Matutino (8:00 - 14:00)</option>
                                <option value="vespertino">Vespertino (14:00 - 20:00)</option>
                            </select>
                        </div>
                        <div>
                            <label>Tareas completadas:</label>
                            <input type="number" name="completadas" placeholder="Número de tareas completadas">
                        </div>
                        <div>
                            <label>Tareas pendientes:</label>
                            <input type="text" name="pendientes" placeholder="Tareas pendientes (si aplica)">
                        </div>
                        <div>
                            <label>Observaciones generales:</label>
                            <textarea name="observaciones_generales" rows="2" placeholder="Observaciones del turno..."></textarea>
                        </div>
                        <div>
                            <label>¿Se necesita apoyo adicional?</label>
                            <select name="apoyo">
                                <option value="no">No</option>
                                <option value="si">Sí, requiere apoyo</option>
                            </select>
                        </div>
                        <button type="submit">Guardar Checklist</button>
                    </form>
                </div>
            </section>
        </div>

    </div>

    <!-- Footer -->
    <div class="footer">
        <p>© 2026 Nuestras Albercas - Sistema de Gestión de Limpieza</p>
        <p>Última actualización: <?php echo date('d/m/Y H:i:s'); ?></p>
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

        // Funcionalidad para checklist (simulación)
        document.querySelectorAll('.checklist-item input').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const item = this.closest('.checklist-item');
                if (this.checked) {
                    item.style.opacity = '0.7';
                } else {
                    item.style.opacity = '1';
                }
            });
        });
    </script>

</body>
</html>