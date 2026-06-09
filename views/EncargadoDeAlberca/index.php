<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Encargado - Nuestras Albercas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
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
            <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
        </div>
    </div>

    <!-- Mensajes de notificación -->
    <?php if (isset($mensaje) && $mensaje): ?>
    <div class="notification <?php echo $tipo_mensaje ?? ''; ?>">
        <i class="fas fa-<?php echo $tipo_mensaje == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
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
        <a href="index.php?section=inicio" class="<?php echo (!isset($_GET['section']) || $_GET['section'] == 'inicio') ? 'active' : ''; ?>">
            <i class="fas fa-home"></i><span>INICIO</span>
        </a>
        <a href="index.php?section=mapa" class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'mapa') ? 'active' : ''; ?>">
            <i class="fas fa-map"></i><span>Mapa</span>
        </a>
        <a href="index.php?section=horarios" class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'horarios') ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i><span>Horarios</span>
        </a>
        <a href="index.php?section=calidad-agua" class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'calidad-agua') ? 'active' : ''; ?>">
            <i class="fas fa-water"></i><span>Calidad del Agua</span>
        </a>
        <a href="index.php?section=personal" class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'personal') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i><span>Personal</span>
        </a>
        <a href="index.php?section=reportes" class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'reportes') ? 'active' : ''; ?>">
            <i class="fas fa-file-alt"></i><span>Reportes</span>
        </a>
        <a href="index.php?section=mantenimiento" class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'mantenimiento') ? 'active' : ''; ?>">
            <i class="fas fa-tools"></i><span>Mantenimiento</span>
        </a>
        <a href="index.php?section=acceso" class="<?php echo (isset($_GET['section']) && $_GET['section'] == 'acceso') ? 'active' : ''; ?>">
            <i class="fas fa-door-open"></i><span>Control de Acceso</span>
        </a>
    </div>

    <!-- Contenido principal -->
    <div class="content" id="mainContent">
        
        <?php
        // Determinar qué sección mostrar
        $section = $_GET['section'] ?? 'inicio';
        
        switch ($section) {
            case 'inicio':
                include 'inicio.php';
                break;
            case 'mapa':
                include 'mapa.php';
                break;
            case 'horarios':
                include 'horarios.php';
                break;
            case 'calidad-agua':
                include 'calidad-agua.php';
                break;
            case 'personal':
                include 'personal.php';
                break;
            case 'reportes':
                include 'reportes.php';
                break;
            case 'mantenimiento':
                include 'mantenimiento.php';
                break;
            case 'acceso':
                include 'control-acceso.php';
                break;
            default:
                include 'inicio.php';
                break;
        }
        ?>

    </div>

    <!-- Footer -->
    <div class="footer">
        <p>© 2026 Nuestras Albercas - Sistema de Gestión para Encargados</p>
        <p>Última actualización: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
        // MENÚ HAMBURGUESA
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const nav = document.querySelector('.nav');
        const content = document.getElementById('mainContent');
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

        if (hamburgerBtn) {
            hamburgerBtn.addEventListener('click', toggleNavbar);
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (dashboardGrid && !dashboardGrid.classList.contains('grid-expanded')) {
                document.documentElement.style.setProperty('--grid-cols', '20');
            }
        });
    </script>
    
    <script>
    // Inicializar gráfica de dona (solo si existe en la página)
    if (document.getElementById('donaChart')) {
        document.addEventListener('DOMContentLoaded', function() {
            const operativas = <?php echo json_encode($operativas ?? 0); ?>;
            const completas = <?php echo json_encode($completas ?? 0); ?>;
            const mantenimiento = <?php echo json_encode($mantenimiento ?? 0); ?>;
            
            const operativasCountEl = document.getElementById('operativasCount');
            const completasCountEl = document.getElementById('completasCount');
            const mantenimientoCountEl = document.getElementById('mantenimientoCount');
            
            if (operativasCountEl) operativasCountEl.textContent = operativas;
            if (completasCountEl) completasCountEl.textContent = completas;
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
                                        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                        return `${label}: ${value} alberca(s) (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    }
    </script>
    <script>
    // ==========================================
    // SISTEMA DE CIERRE DE SESIÓN POR INACTIVIDAD
    // ==========================================
    let inactivityTimer;

    function resetTimer() {
        clearTimeout(inactivityTimer);
        // 900,000 milisegundos = 15 minutos
        inactivityTimer = setTimeout(expulsarUsuario, 900000); 
    }

    function expulsarUsuario() {
        // Redirigir al archivo que destruye la sesión (ajusta la ruta según donde estés)
        window.location.href = '../../logout.php'; 
    }

    // Reiniciar el reloj cada vez que el usuario haga algo en la pantalla
    window.onload = resetTimer;
    document.onmousemove = resetTimer; // Si mueve el mouse
    document.onkeypress = resetTimer;  // Si teclea algo
    document.onclick = resetTimer;     // Si da clic
    document.onscroll = resetTimer;    // Si baja la pantalla
</script>
</body>
</html>