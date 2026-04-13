<!-- Mapa interactivo -->
<div class="mapa-interactivo-container">
    <h3><i class="fas fa-map-marked-alt"></i> Mapa Interactivo del Parque</h3>
    <p class="mapa-instruccion">
        <i class="fas fa-hand-pointer"></i> Haz clic en cualquier área para ver información de calidad del agua
    </p>

    <div class="mapa-layout">

        <!-- Imagen del mapa -->
        <div class="mapa-imagen-wrapper">
            <img src="../../img/mapa-albercas.png" alt="Mapa del Parque Acuático" class="mapa-imagen" usemap="#mapa-parque" style="width:100%; height:auto;">

            <map name="mapa-parque">
                <!-- Coordenadas de círculo: centro X, centro Y, radio -->
                <!-- Alberca Principal - centro aproximado -->
                <area shape="circle" 
                      coords="100,100,100" 
                      data-alberca="principal" 
                      class="hotspot-area" 
                      title="Alberca Principal" 
                      alt="Alberca Principal">
                
                <!-- Pina Colada -->
                <area shape="circle" 
                      coords="1260,250,90" 
                      data-alberca="pina-colada" 
                      class="hotspot-area" 
                      title="Pina Colada" 
                      alt="Pina Colada">
                
                <!-- Pool Cafe -->
                <area shape="circle" 
                      coords="2160,250,90" 
                      data-alberca="pool-cafe" 
                      class="hotspot-area" 
                      title="Pool Cafe" 
                      alt="Pool Cafe">
                
                <!-- Vestidores -->
                <area shape="circle" 
                      coords="390,730,70" 
                      data-alberca="vestidores" 
                      class="hotspot-area" 
                      title="Vestidores" 
                      alt="Vestidores">
                
                <!-- Shack -->
                <area shape="circle" 
                      coords="1220,730,80" 
                      data-alberca="shack" 
                      class="hotspot-area" 
                      title="Shack" 
                      alt="Shack">
                
                <!-- Alberca Infantil -->
                <area shape="circle" 
                      coords="450,1280,100" 
                      data-alberca="infantil" 
                      class="hotspot-area" 
                      title="Alberca Infantil" 
                      alt="Alberca Infantil">
            </map>
        </div>

        <!-- Panel lateral de información -->
        <div class="mapa-panel-lateral" id="mapaPanelLateral">

            <!-- Estado vacío -->
            <div class="panel-vacio" id="panelVacio">
                <div class="panel-vacio-icon">🗺️</div>
                <p>Selecciona un área del mapa para ver sus datos de calidad del agua</p>
            </div>

            <!-- Contenido al seleccionar un área -->
            <div class="panel-contenido" id="panelContenido" style="display:none;">
                <button class="panel-cerrar" id="panelCerrar" title="Cerrar">✕</button>

                <div class="panel-titulo" id="panelTitulo"></div>
                <div class="panel-subtitulo">Última medición registrada</div>

                <div class="panel-metricas">
                    <div class="metrica-card">
                        <div class="metrica-icon">💧</div>
                        <div class="metrica-info">
                            <div class="metrica-label">Cloro</div>
                            <div class="metrica-valor" id="panelCloro">-- ppm</div>
                        </div>
                        <div class="metrica-barra-wrap">
                            <div class="metrica-barra" id="barraCloro"></div>
                        </div>
                    </div>

                    <div class="metrica-card">
                        <div class="metrica-icon">🧪</div>
                        <div class="metrica-info">
                            <div class="metrica-label">pH</div>
                            <div class="metrica-valor" id="panelPh">--</div>
                        </div>
                        <div class="metrica-barra-wrap">
                            <div class="metrica-barra" id="barraPh"></div>
                        </div>
                    </div>

                    <div class="metrica-card">
                        <div class="metrica-icon">🌡️</div>
                        <div class="metrica-info">
                            <div class="metrica-label">Temperatura</div>
                            <div class="metrica-valor" id="panelTemp">--°C</div>
                        </div>
                        <div class="metrica-barra-wrap">
                            <div class="metrica-barra" id="barraTemp"></div>
                        </div>
                    </div>
                </div>

                <div class="panel-estado-wrap">
                    <span class="panel-estado" id="panelEstado">--</span>
                </div>

                <div class="panel-rangos">
                    <div class="rango-titulo">Rangos óptimos</div>
                    <div class="rango-item"><span>💧 Cloro</span><span>1.0 – 3.0 ppm</span></div>
                    <div class="rango-item"><span>🧪 pH</span><span>7.2 – 7.8</span></div>
                    <div class="rango-item"><span>🌡️ Temperatura</span><span>24 – 28°C</span></div>
                </div>
            </div>

        </div><!-- /.mapa-panel-lateral -->

    </div><!-- /.mapa-layout -->

    <!-- Leyenda -->
    <div class="mapa-leyenda">
        <div class="leyenda-item"><div class="leyenda-color optimo"></div><span>Óptimo</span></div>
        <div class="leyenda-item"><div class="leyenda-color atencion"></div><span>Requiere atención</span></div>
        <div class="leyenda-item"><div class="leyenda-color critico"></div><span>Crítico</span></div>
        <div class="leyenda-item"><i class="fas fa-hand-pointer leyenda-icon"></i><span>Haz clic para ver datos</span></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const areas          = document.querySelectorAll('.hotspot-area');
    const panelLateral   = document.getElementById('mapaPanelLateral');
    const panelVacio     = document.getElementById('panelVacio');
    const panelContenido = document.getElementById('panelContenido');
    const panelCerrar    = document.getElementById('panelCerrar');
    const panelTitulo    = document.getElementById('panelTitulo');
    const panelCloro     = document.getElementById('panelCloro');
    const panelPh        = document.getElementById('panelPh');
    const panelTemp      = document.getElementById('panelTemp');
    const panelEstado    = document.getElementById('panelEstado');
    const barraCloro     = document.getElementById('barraCloro');
    const barraPh        = document.getElementById('barraPh');
    const barraTemp      = document.getElementById('barraTemp');

    // ── Datos desde PHP ───────────────────────────────────────────────────────
    const calidadData = <?php
        $calidadMap = [];
        foreach ($ultima_calidad ?? [] as $c) {
            if (strpos($c['alberca'], 'Principal') !== false) $calidadMap['principal']   = $c;
            if (strpos($c['alberca'], 'Pina')      !== false) $calidadMap['pina-colada'] = $c;
            if (strpos($c['alberca'], 'Infantil')  !== false) $calidadMap['infantil']    = $c;
            if (strpos($c['alberca'], 'Cafe')      !== false) $calidadMap['pool-cafe']   = $c;
            if (strpos($c['alberca'], 'Shack')     !== false) $calidadMap['shack']       = $c;
            if (strpos($c['alberca'], 'Vestidor')  !== false) $calidadMap['vestidores']  = $c;
        }
        echo json_encode($calidadMap);
    ?>;

    const nombresAreas = {
        'principal'  : '🏊 Alberca Principal',
        'pina-colada': '🍹 Pina Colada',
        'vestidores' : '🚿 Vestidores',
        'shack'      : '🏪 Shack',
        'pool-cafe'  : '☕ Pool Cafe',
        'infantil'   : '🧸 Alberca Infantil'
    };

    // ── Helpers ───────────────────────────────────────────────────────────────
    function getEstado(cloro, ph, temp) {
        if (!cloro || !ph || !temp) return { texto: 'Sin datos', clase: 'atencion' };
        const cloroOk = cloro >= 1.0 && cloro <= 3.0;
        const phOk    = ph    >= 7.2 && ph    <= 7.8;
        const tempOk  = temp  >= 24  && temp  <= 28;
        if (cloroOk && phOk && tempOk)            return { texto: 'Óptimo',            clase: 'optimo'  };
        if (cloro < 1.0 || ph < 7.0 || temp > 30) return { texto: 'Crítico',           clase: 'critico' };
        return                                            { texto: 'Requiere atención', clase: 'atencion' };
    }

    function pct(val, min, max) {
        return Math.min(100, Math.max(0, ((val - min) / (max - min)) * 100));
    }

    // ── Abrir panel ───────────────────────────────────────────────────────────
    function abrirPanel(areaKey) {
        const nombre = nombresAreas[areaKey] || 'Área';
        const datos  = calidadData[areaKey];

        panelTitulo.textContent = nombre;

        if (datos) {
            const estado = getEstado(datos.cloro_ppm, datos.ph, datos.temperatura);
            const colorMap = { optimo: '#4f9da6', atencion: '#f9a26c', critico: '#e67e22' };
            const color    = colorMap[estado.clase];

            panelCloro.textContent = datos.cloro_ppm + ' ppm';
            panelPh.textContent    = datos.ph;
            panelTemp.textContent  = datos.temperatura + '°C';

            // Barras de progreso
            barraCloro.style.width      = pct(datos.cloro_ppm,  0,    5)   + '%';
            barraPh.style.width         = pct(datos.ph,         6.5,  8.5) + '%';
            barraTemp.style.width       = pct(datos.temperatura, 18,  35)  + '%';
            barraCloro.style.background = color;
            barraPh.style.background    = color;
            barraTemp.style.background  = color;

            panelEstado.textContent = estado.texto;
            panelEstado.className   = 'panel-estado ' + estado.clase;
        } else {
            panelCloro.textContent = '-- ppm';
            panelPh.textContent    = '--';
            panelTemp.textContent  = '--°C';
            barraCloro.style.width = '0%';
            barraPh.style.width    = '0%';
            barraTemp.style.width  = '0%';
            panelEstado.textContent = 'Sin monitoreo';
            panelEstado.className   = 'panel-estado atencion';
        }

        panelVacio.style.display     = 'none';
        panelContenido.style.display = 'block';
        panelLateral.classList.add('activo');

        // Resaltar área seleccionada
        areas.forEach(a => a.classList.remove('area-seleccionada'));
        const selectedArea = document.querySelector(`[data-alberca="${areaKey}"]`);
        if (selectedArea) selectedArea.classList.add('area-seleccionada');
    }

    // ── Cerrar panel ──────────────────────────────────────────────────────────
    function cerrarPanel() {
        panelContenido.style.display = 'none';
        panelVacio.style.display     = 'flex';
        panelLateral.classList.remove('activo');
        areas.forEach(a => a.classList.remove('area-seleccionada'));
    }

    // ── Eventos ───────────────────────────────────────────────────────────────
    areas.forEach(area => {
        area.addEventListener('click', (e) => {
            e.preventDefault();
            abrirPanel(area.getAttribute('data-alberca'));
        });
    });

    if (panelCerrar) {
        panelCerrar.addEventListener('click', cerrarPanel);
    }

    // Cerrar panel al hacer clic fuera (opcional)
    document.addEventListener('click', function(e) {
        const isClickInsideMap = e.target.closest('.mapa-imagen');
        const isClickInsidePanel = e.target.closest('.mapa-panel-lateral');
        
        if (panelContenido.style.display === 'block' && !isClickInsideMap && !isClickInsidePanel) {
            cerrarPanel();
        }
    });
});
</script>