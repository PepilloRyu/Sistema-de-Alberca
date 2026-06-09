@import url('calidad-agua.css');
<!-- SECCIÓN CALIDAD DEL AGUA CON MAPA INTERACTIVO -->
<div id="calidad-agua" class="section-container active">
    <section>
        <h2>💧 Calidad del Agua</h2>
        

        
        <!-- Tabla de calidad del agua -->
        <div class="calidad-tabla">
            <h3>📊 Parámetros de Calidad por Área</h3>
            <div class="tabla-container">
                <table class="calidad-table">
                    <thead>
                        <tr><th>Área</th><th>Cloro (ppm)</th><th>pH</th><th>Temperatura (°C)</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach (($ultima_calidad ?? []) as $c): 
                            $estado_cloro = ($c['cloro_ppm'] >= 1.0 && $c['cloro_ppm'] <= 3.0) ? 'good' : 'warning';
                            $estado_ph = ($c['ph'] >= 7.2 && $c['ph'] <= 7.8) ? 'good' : 'warning';
                            $estado_temp = ($c['temperatura'] >= 24 && $c['temperatura'] <= 28) ? 'good' : 'warning';
                            $estado_general = ($estado_cloro == 'good' && $estado_ph == 'good' && $estado_temp == 'good') ? 'Óptimo' : 'Revisar';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($c['alberca']); ?></strong></td>
                            <td class="calidad-<?php echo $estado_cloro; ?>"><?php echo $c['cloro_ppm']; ?></td>
                            <td class="calidad-<?php echo $estado_ph; ?>"><?php echo $c['ph']; ?></td>
                            <td class="calidad-<?php echo $estado_temp; ?>"><?php echo $c['temperatura']; ?>°C</td>
                            <td><span class="estado-badge <?php echo $estado_general == 'Óptimo' ? 'optimo' : 'revisar'; ?>"><?php echo $estado_general; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Formulario -->
        <div class="calidad-formulario">
            <h3>📝 Registrar Nuevos Parámetros</h3>
            <form method="POST" class="form-calidad">
                <input type="hidden" name="action" value="registrar_calidad">
                <div class="form-row">
                    <div class="form-group">
                        <label>Seleccionar alberca:</label>
                        <select name="alberca_id" required>
                            <?php foreach (($catalogo_albercas ?? []) as $a): ?>
                            <option value="<?php echo $a['id_alberca']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
                            <?php endforeach; ?>
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

