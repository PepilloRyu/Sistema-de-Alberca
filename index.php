<?php
// index.php - Página principal del sistema
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener el usuario actual usando la función usuarioActual()
// Si la función no existe, la creamos manualmente
if (!function_exists('usuarioActual')) {
    function usuarioActual() {
        if (isset($_SESSION['usuario_id'])) {
            return [
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'email' => $_SESSION['usuario_email'],
                'rol_id' => $_SESSION['rol_id'],
                'rol_nombre' => $_SESSION['rol_nombre'] ?? null
            ];
        }
        return null;
    }
}

// Obtener datos del usuario
$user = usuarioActual();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestras Albercas - Parque Acuático</title>

    <!-- Fuentes y Librerías Externas (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <!-- Nuestro CSS personalizado -->
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <!-- HEADER DE USUARIO O ENLACES DE AUTENTICACIÓN -->
    <?php if ($user): ?>
        <!-- Usuario logueado: muestra su nombre y opción de cerrar sesión -->
        <div class="user-header">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?></span>
                <?php 
                // Mostrar badge según el rol
                $rol_nombre = $user['rol_nombre'] ?? '';
                if ($rol_nombre === 'Encargado de alberca'): ?>
                    <span style="background: var(--primary-gold); color: var(--primary-blue); padding: 2px 10px; border-radius: 15px; font-size: 0.8rem;">Encargado</span>
                <?php elseif ($rol_nombre === 'Personal de limpieza'): ?>
                    <span style="background: #2ecc71; color: white; padding: 2px 10px; border-radius: 15px; font-size: 0.8rem;">Limpieza</span>
                <?php elseif ($rol_nombre === 'Técnico de mantenimiento'): ?>
                    <span style="background: #3498db; color: white; padding: 2px 10px; border-radius: 15px; font-size: 0.8rem;">Mantenimiento</span>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <?php 
                // Redirigir al panel según el rol
                $panel_url = '#';
                $rol_id = $user['rol_id'] ?? 0;
                switch ($rol_id) {
                    case 1:
                        $panel_url = 'usuarios/EncargadoDeAlberca/index.php';
                        break;
                    case 2:
                        $panel_url = 'usuarios/PersonalDeLimpieza/index.php';
                        break;
                    case 3:
                        $panel_url = 'usuarios/TecnicoDeMantenimiento/index.php';
                        break;
                }
                ?>
                <a href="<?php echo $panel_url; ?>" style="margin-right: 15px;"><i class="fas fa-tachometer-alt"></i> Mi Panel</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Usuario no logueado: muestra enlaces de inicio de sesión y registro -->
        <div class="user-header" style="justify-content: flex-end;">
            <div class="user-info">
                <a href="login.php" style="margin-right: 15px;"><i class="fas fa-sign-in-alt"></i> Iniciar sesión</a>
                <a href="registro.php"><i class="fas fa-user-plus"></i> Registrarse</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Barra superior con horario -->
    <div class="top-bar">
        <i class="fas fa-calendar-alt"></i> <span>Abierto los 365 días del año</span>
        <i class="fas fa-clock"></i> <span>9:00 a 18:00 hrs</span>
    </div>

    <!-- CARRUSEL PRINCIPAL CON FOTOS -->
    <div class="swiper hero-swiper">
        <div class="swiper-wrapper">

            <!-- Slide 1: Principal -->
            <div class="swiper-slide" style="background-image: url('img/albercaPrincipal.png');">
                <div class="slide-content">
                    <h2>PRINCIPAL</h2>
                    <p>La más grande, divertida y áreas de descanso</p>
                    <!-- Botón redirige al login si no está logueado, o a compra si lo está -->
                    <a href="<?php echo $user ? '#' : 'login.php'; ?>" class="btn-hero">
                        <?php echo $user ? 'COMPRAR BOLETO' : 'INICIA SESIÓN PARA COMPRAR'; ?>
                    </a>
                </div>
            </div>

            <!-- Slide 2: Familiar -->
            <div class="swiper-slide" style="background-image: url('img/albercaFamiliar.png');">
                <div class="slide-content">
                    <h2>FAMILIAR</h2>
                    <p>Espacios amplios para compartir en familia</p>
                    <a href="<?php echo $user ? '#' : 'login.php'; ?>" class="btn-hero">
                        <?php echo $user ? 'CONOCE MÁS' : 'INICIA SESIÓN PARA CONOCER'; ?>
                    </a>
                </div>
            </div>

            <!-- Slide 3: Infantil -->
            <div class="swiper-slide" style="background-image: url('img/albercaInfantil.png');">
                <div class="slide-content">
                    <h2>INFANTIL</h2>
                    <p>Diversión segura para los más pequeños</p>
                    <a href="<?php echo $user ? '#' : 'login.php'; ?>" class="btn-hero">
                        <?php echo $user ? 'RESERVA TU DÍA' : 'INICIA SESIÓN PARA RESERVAR'; ?>
                    </a>
                </div>
            </div>

            <!-- Slide 4: Vista al mar -->
            <div class="swiper-slide" style="background-image: url('img/albercaVistaAlMar.png');">
                <div class="slide-content">
                    <h2>VISTA AL MAR</h2>
                    <p>Relájate con la mejor vista y atardeceres inolvidables</p>
                    <a href="<?php echo $user ? '#' : 'login.php'; ?>" class="btn-hero">
                        <?php echo $user ? 'VER PROMOCIONES' : 'INICIA SESIÓN PARA VER'; ?>
                    </a>
                </div>
            </div>

            <!-- Slide 5: Deportiva -->
            <div class="swiper-slide" style="background-image: url('img/albercaDeportiva.png');">
                <div class="slide-content">
                    <h2>DEPORTIVA</h2>
                    <p>Para nadadores y eventos especiales</p>
                    <a href="<?php echo $user ? '#' : 'login.php'; ?>" class="btn-hero">
                        <?php echo $user ? 'VER PROMOCIONES' : 'INICIA SESIÓN PARA VER'; ?>
                    </a>
                </div>
            </div>
        
        </div>
        <!-- Botones de navegación -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <!-- Paginación -->
        <div class="swiper-pagination"></div>
    </div>

    <!-- COMPRAR LOS BOLETOS AHORA -->
    <div class="horario-bar">
        <i class="fas fa-ticket-alt"></i> 
        <?php if ($user): ?>
            <a href="#" style="color: var(--primary-blue); text-decoration: none;">¡Compra ahora tus boletos en línea!</a>
        <?php else: ?>
            <a href="login.php" style="color: var(--primary-blue); text-decoration: none;">¡Inicia sesión para comprar boletos!</a>
        <?php endif; ?>
        <i class="fas fa-ticket-alt"></i>
    </div>

    <!-- SECCIÓN DE PROMOCIONES (PAQUETES) -->
    <div class="promos-section">
        <h2 class="section-title">Boletos</h2>
        
        <div class="promos-grid">
            <!-- Paquete 1: Todo Pagado -->
            <div class="promo-card">
                <div class="promo-img" style="background-image: url('img/boleto1.png');">
                    <div class="promo-tag">¡OFERTA!</div>
                </div>
                <div class="promo-content">
                    <h3>PAQUETE ESPECIAL</h3>
                    <div class="promo-price">$1000</div>
                    <p>para 4 personas</p>
                    <a href="<?php echo $user ? '#' : 'login.php'; ?>" class="btn-promo">
                        <?php echo $user ? 'Comprar ahora' : 'Inicia sesión para comprar'; ?>
                    </a>
                </div>
            </div>
            
            <!-- Paquete 2: Transporte Incluido -->
            <div class="promo-card">
                <div class="promo-img" style="background-image: url('img/boleto2.png');">
                </div>
                <div class="promo-content">
                    <h3>BOLETO GENERAL</h3>
                    <div class="promo-price">$300</div>
                    <p>Boleto ordinario</p>
                    <a href="<?php echo $user ? '#' : 'login.php'; ?>" class="btn-promo">
                        <?php echo $user ? 'Comprar ahora' : 'Inicia sesión para comprar'; ?>
                    </a>
                </div>
            </div>
           
            <!-- Paquete 3: Boleto Completo -->
            <div class="promo-card">
                <div class="promo-img" style="background-image: url('img/boleto3.png');">
                </div>
                <div class="promo-content">
                    <h3>BOLETO INFANTIL</h3>
                    <div class="promo-price">$200</div>
                    <p>Niños hasta 1.20 mts de estatura</p>
                    <a href="<?php echo $user ? '#' : 'login.php'; ?>" class="btn-promo">
                        <?php echo $user ? 'Comprar ahora' : 'Inicia sesión para comprar'; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- EVENTOS ESPECIALES -->
    <section class="eventos-section" aria-labelledby="eventos-titulo">
        <div class="eventos-container">
            <h2 id="eventos-titulo" class="section-title" style="color: white; text-shadow: 4px 4px 0 #1e3c5c;">
                🎢 EVENTOS ESPECIALES 🎉
            </h2>
            <div class="eventos-grid">
                <!-- Evento Destacado #1 -->
                <article class="evento-card featured">
                    <div class="evento-badge" aria-label="Recién agregado">¡NUEVO!</div>
                    <h3 class="evento-titulo">EVENTO #1</h3>
                    <p class="evento-tagline">Mensaje llamativo del evento</p>
                    <div class="evento-stats">
                        <span><i class="fas fa-tachometer-alt" aria-hidden="true"></i> Característica 1</span>
                        <span><i class="fas fa-arrow-up" aria-hidden="true"></i> Característica 2</span>
                        <span><i class="fas fa-clock" aria-hidden="true"></i> Característica 3</span>
                    </div>
                    <p class="evento-descripcion">
                        Descripción más detallada de lo que hace especial a este evento.
                    </p>
                    <div class="evento-footer">
                        <span class="evento-edad"><i class="fas fa-id-card" aria-hidden="true"></i> +18 años</span>
                        <a href="<?php echo $user ? '#' : 'login.php'; ?>" class="btn-evento">
                            <span><?php echo $user ? 'CONOCE MÁS' : 'INICIA SESIÓN'; ?></span>
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </article>

                <!-- Evento #2 -->
                <article class="evento-card">
                    <h3 class="evento-titulo">EVENTO #2</h3>
                    <p class="evento-tagline">Mensaje llamativo del evento</p>
                    <!-- Usamos la etiqueta <time> para la fecha -->
                    <div class="evento-fecha">
                        <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                        <time datetime="2024-05-01">Todos los fines de semana de Mayo</time>
                    </div>
                    <p class="evento-descripcion">
                        Descripción detallada de este evento regular.
                    </p>
                    <div class="evento-incluye">
                        <span><i class="fas fa-check-circle" aria-hidden="true"></i> Característica 1</span>
                        <span><i class="fas fa-check-circle" aria-hidden="true"></i> Característica 2</span>
                        <span><i class="fas fa-check-circle" aria-hidden="true"></i> Característica 3</span>
                    </div>
                    <a href="<?php echo $user ? '#' : 'login.php'; ?>" class="btn-evento">
                        <span><?php echo $user ? 'VER EVENTOS' : 'INICIA SESIÓN'; ?></span>
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </article>
            </div>
        </div>
    </section>

    <!-- TIPS Y REGLAMENTO -->
    <div class="info-grid">
        <div class="info-card">
            <h3><i class="fas fa-lightbulb"></i> TIPS PARA DISFRUTAR</h3>
            <ul>
                <li><i class="fas fa-check-circle"></i> Establece con tus familiares un punto de reunión definido.</li>
                <li><i class="fas fa-check-circle"></i> Mantén a los niños siempre en compañía de un adulto.</li>
                <li><i class="fas fa-check-circle"></i> El parque es muy seguro, respeta los reglamentos.</li>
                <li><i class="fas fa-check-circle"></i> No nades hasta que haya pasado 1 hora desde tu último alimento.</li>
            </ul>
        </div>
        <div class="info-card">
            <h3><i class="fas fa-shield-alt"></i> REGLAMENTO</h3>
            <ul>
                <li><i class="fas fa-ban"></i> No introducir alimentos ni bebidas.</li>
                <li><i class="fas fa-swimmer"></i> Uso de traje de baño obligatorio.</li>
                <li><i class="fas fa-child"></i> Menores de edad siempre acompañados.</li>
                <li><i class="fas fa-shoe-prints"></i> Andar descalzo en áreas de albercas.</li>
                <li><i class="fas fa-dog"></i> No se permiten mascotas.</li>
            </ul>
        </div>
    </div>

    <!-- Sección de Información Operativa del Parque -->
    <div class="info-operativa">
        <h2 class="section-title">🛟 Información del Parque 🛟</h2>
        <div class="operativa-grid">

            <!-- Capacidad de albercas -->
            <div class="operativa-card">
                <i class="fas fa-water fa-3x" style="color: #ffaa00; margin-bottom: 15px;"></i>
                <h3>Capacidad por alberca</h3>
                <ul class="icon-list">
                    <li><i class="fas fa-swimmer"></i> Principal: 200 personas</li>
                    <li><i class="fas fa-users"></i> Familiar: 150 personas</li>
                    <li><i class="fas fa-child"></i> Infantil: 80 personas</li>
                    <li><i class="fas fa-water"></i> Vista al Mar: 40 personas</li>
                    <li><i class="fas fa-futbol"></i> Deportiva: 100 personas</li>
                </ul>
            </div>

            <!-- Limpieza y Mantenimiento -->
            <div class="operativa-card">
                <i class="fas fa-broom"></i>
                <h3>Limpieza</h3>
                <p><strong>Diaria:</strong> 5:00 AM - 6:00 AM</p>
                <p><strong>Eventual:</strong> Tickets por incidencias (especialmente en área infantil).</p>
                <p>Personal asignado según tamaño de alberca.</p>
            </div>

            <!-- Mobiliario y Accesos -->
            <div class="operativa-card">
                <i class="fas fa-umbrella-beach"></i>
                <h3>Mobiliario</h3>
                <p>Uso libre de camastros, sillas y palapas (sin renta).</p>
                <p>Alberca deportiva reservada para eventos especiales.</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>© 2025 Parque Acuático "Nuestras Albercas" - Todos los derechos reservados</p>
        <p style="margin-top: 10px; font-size: 0.9rem;">
            <i class="fas fa-phone-alt"></i> 55 5555 5555 | 
            <i class="fas fa-envelope"></i> info@albercas.com | 
            <i class="fas fa-map-marker-alt"></i> Ubicación : 
        </p>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>