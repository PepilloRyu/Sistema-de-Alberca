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