<!-- Mapa interactivo -->
<div class="mapa-interactivo-container">
    <h3><i class="fas fa-map-marked-alt"></i> Mapa Interactivo del Parque</h3>
    <p class="mapa-instruccion">
        <i class="fas fa-hand-pointer"></i> Haz clic en el mapa o en la lista para ver datos
    </p>

    <div class="mapa-layout">
        <div class="mapa-imagen-wrapper">
            <img src="../../img/mapa-albercas.png" 
                 alt="Mapa del Parque Acuático" 
                 class="mapa-imagen" 
                 usemap="#mapa-parque" 
                 style="width:100%; height:auto;">

            <map name="mapa-parque">
                <area shape="circle" coords="1344,797,252" data-alberca="principal" class="hotspot-area" title="Alberca Principal">
                <area shape="circle" coords="2184,295,98"  data-alberca="pina-colada" class="hotspot-area" title="Piña Colada">
                <area shape="circle" coords="504,384,112"  data-alberca="stage" class="hotspot-area" title="Stage">
                <area shape="circle" coords="2184,650,112" data-alberca="pool-cafe" class="hotspot-area" title="Pool Cafe">
                <area shape="circle" coords="504,827,126"  data-alberca="vestidores" class="hotspot-area" title="Vestidores">
                <area shape="circle" coords="1764,916,105" data-alberca="shack" class="hotspot-area" title="Shack">
                <area shape="circle" coords="1204,1359,196" data-alberca="infantil" class="hotspot-area" title="Alberca Infantil">
            </map>
        </div>

        <div class="mapa-panel-lateral" id="mapaPanelLateral">
            <div class="lista-albercas">
                <div class="lista-titulo"><i class="fas fa-swimming-pool"></i> Nuestras albercas</div>
                <ul class="lista-albercas-ul" id="listaAlbercas">
                    <li data-alberca="principal">🏊 Alberca Principal</li>
                    <li data-alberca="pina-colada">🍹 Piña Colada</li>
                    <li data-alberca="stage">🎭 Stage</li>
                    <li data-alberca="pool-cafe">☕ Pool Cafe</li>
                    <li data-alberca="vestidores">🚿 Vestidores</li>
                    <li data-alberca="shack">🏪 Shack</li>
                    <li data-alberca="infantil">🧸 Alberca Infantil</li>
                </ul>
            </div>

            <div class="panel-vacio" id="panelVacio">
                <div class="panel-vacio-icon">🗺️</div>
                <p>Selecciona un área del mapa o de la lista para ver sus datos</p>
            </div>

            <div class="panel-contenido" id="panelContenido" style="display:none;">
                <button class="panel-cerrar" id="panelCerrar">✕</button>
                <div class="panel-titulo" id="panelTitulo"></div>
                <div class="panel-subtitulo">Última medición registrada</div>
                <div class="panel-metricas">
                    <div class="metrica-card">
                        <div class="metrica-icon">💧</div>
                        <div class="metrica-info">
                            <div class="metrica-label">Cloro</div>
                            <div class="metrica-valor" id="panelCloro">-- ppm</div>
                        </div>
                        <div class="metrica-barra-wrap"><div class="metrica-barra" id="barraCloro"></div></div>
                    </div>
                    <div class="metrica-card">
                        <div class="metrica-icon">🧪</div>
                        <div class="metrica-info">
                            <div class="metrica-label">pH</div>
                            <div class="metrica-valor" id="panelPh">--</div>
                        </div>
                        <div class="metrica-barra-wrap"><div class="metrica-barra" id="barraPh"></div></div>
                    </div>
                    <div class="metrica-card">
                        <div class="metrica-icon">🌡️</div>
                        <div class="metrica-info">
                            <div class="metrica-label">Temperatura</div>
                            <div class="metrica-valor" id="panelTemp">--°C</div>
                        </div>
                        <div class="metrica-barra-wrap"><div class="metrica-barra" id="barraTemp"></div></div>
                    </div>
                </div>
                <div class="panel-estado-wrap"><span class="panel-estado" id="panelEstado">--</span></div>
                <div class="panel-rangos">
                    <div class="rango-titulo">Rangos óptimos</div>
                    <div class="rango-item"><span>💧 Cloro</span><span>1.0 – 3.0 ppm</span></div>
                    <div class="rango-item"><span>🧪 pH</span><span>7.2 – 7.8</span></div>
                    <div class="rango-item"><span>🌡️ Temperatura</span><span>24 – 28°C</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mapa-leyenda">
        <div class="leyenda-item"><div class="leyenda-color optimo"></div><span>Óptimo</span></div>
        <div class="leyenda-item"><div class="leyenda-color atencion"></div><span>Requiere atención</span></div>
        <div class="leyenda-item"><div class="leyenda-color critico"></div><span>Crítico</span></div>
        <div class="leyenda-item"><i class="fas fa-hand-pointer leyenda-icon"></i><span>Haz clic para ver datos</span></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const areas = document.querySelectorAll('.hotspot-area');
    const itemsLista = document.querySelectorAll('#listaAlbercas li');
    const panelLateral = document.getElementById('mapaPanelLateral');
    const panelVacio = document.getElementById('panelVacio');
    const panelContenido = document.getElementById('panelContenido');
    const panelCerrar = document.getElementById('panelCerrar');
    const panelTitulo = document.getElementById('panelTitulo');
    const panelCloro = document.getElementById('panelCloro');
    const panelPh = document.getElementById('panelPh');
    const panelTemp = document.getElementById('panelTemp');
    const panelEstado = document.getElementById('panelEstado');
    const barraCloro = document.getElementById('barraCloro');
    const barraPh = document.getElementById('barraPh');
    const barraTemp = document.getElementById('barraTemp');

    const calidadData = {
        'principal': { cloro_ppm: 1.8, ph: 7.4, temperatura: 26 },
        'pina-colada': { cloro_ppm: 2.1, ph: 7.5, temperatura: 27 },
        'stage': { cloro_ppm: 1.2, ph: 7.3, temperatura: 25 },
        'pool-cafe': { cloro_ppm: 2.5, ph: 7.6, temperatura: 28 },
        'vestidores': { cloro_ppm: 0.8, ph: 7.1, temperatura: 24 },
        'shack': { cloro_ppm: 1.5, ph: 7.2, temperatura: 26 },
        'infantil': { cloro_ppm: 0.5, ph: 6.8, temperatura: 29 }
    };

    const nombresAreas = {
        'principal': '🏊 Alberca Principal',
        'pina-colada': '🍹 Piña Colada',
        'stage': '🎭 Stage',
        'pool-cafe': '☕ Pool Cafe',
        'vestidores': '🚿 Vestidores',
        'shack': '🏪 Shack',
        'infantil': '🧸 Alberca Infantil'
    };

    function getEstado(cloro, ph, temp) {
        if (!cloro || !ph || !temp) return { texto: 'Sin datos', clase: 'atencion' };
        const cloroOk = cloro >= 1.0 && cloro <= 3.0;
        const phOk = ph >= 7.2 && ph <= 7.8;
        const tempOk = temp >= 24 && temp <= 28;
        if (cloroOk && phOk && tempOk) return { texto: 'Óptimo', clase: 'optimo' };
        if (cloro < 1.0 || ph < 7.0 || temp > 30) return { texto: 'Crítico', clase: 'critico' };
        return { texto: 'Requiere atención', clase: 'atencion' };
    }

    function pct(val, min, max) {
        return Math.min(100, Math.max(0, ((val - min) / (max - min)) * 100));
    }

    function abrirPanel(areaKey) {
        const nombre = nombresAreas[areaKey] || 'Área';
        const datos = calidadData[areaKey];
        panelTitulo.textContent = nombre;

        if (datos) {
            const estado = getEstado(datos.cloro_ppm, datos.ph, datos.temperatura);
            const colorMap = { optimo: '#4f9da6', atencion: '#f9a26c', critico: '#e67e22' };
            const color = colorMap[estado.clase];

            panelCloro.textContent = datos.cloro_ppm + ' ppm';
            panelPh.textContent = datos.ph;
            panelTemp.textContent = datos.temperatura + '°C';

            barraCloro.style.width = pct(datos.cloro_ppm, 0, 5) + '%';
            barraPh.style.width = pct(datos.ph, 6.5, 8.5) + '%';
            barraTemp.style.width = pct(datos.temperatura, 18, 35) + '%';
            barraCloro.style.background = color;
            barraPh.style.background = color;
            barraTemp.style.background = color;

            panelEstado.textContent = estado.texto;
            panelEstado.className = 'panel-estado ' + estado.clase;
        } else {
            panelCloro.textContent = '-- ppm';
            panelPh.textContent = '--';
            panelTemp.textContent = '--°C';
            barraCloro.style.width = '0%';
            barraPh.style.width = '0%';
            barraTemp.style.width = '0%';
            panelEstado.textContent = 'Sin monitoreo';
            panelEstado.className = 'panel-estado atencion';
        }

        panelVacio.style.display = 'none';
        panelContenido.style.display = 'block';
        panelLateral.classList.add('activo');

        areas.forEach(a => a.classList.remove('area-seleccionada'));
        const selectedArea = document.querySelector(`.hotspot-area[data-alberca="${areaKey}"]`);
        if (selectedArea) selectedArea.classList.add('area-seleccionada');

        itemsLista.forEach(li => li.classList.remove('activo-lista'));
        const itemActivo = document.querySelector(`#listaAlbercas li[data-alberca="${areaKey}"]`);
        if (itemActivo) itemActivo.classList.add('activo-lista');
    }

    function cerrarPanel() {
        panelContenido.style.display = 'none';
        panelVacio.style.display = 'flex';
        panelLateral.classList.remove('activo');
        areas.forEach(a => a.classList.remove('area-seleccionada'));
        itemsLista.forEach(li => li.classList.remove('activo-lista'));
    }

    areas.forEach(area => {
        area.addEventListener('click', (e) => {
            e.preventDefault();
            abrirPanel(area.getAttribute('data-alberca'));
        });
    });

    itemsLista.forEach(item => {
        item.addEventListener('click', () => {
            const areaKey = item.getAttribute('data-alberca');
            abrirPanel(areaKey);
        });
    });

    panelCerrar.addEventListener('click', cerrarPanel);

    document.addEventListener('click', function(e) {
        const isClickInsideMap = e.target.closest('.mapa-imagen');
        const isClickInsidePanel = e.target.closest('.mapa-panel-lateral');
        if (panelContenido.style.display === 'block' && !isClickInsideMap && !isClickInsidePanel) {
            cerrarPanel();
        }
    });
});
</script>