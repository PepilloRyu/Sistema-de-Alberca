<!-- SECCIÓN REPORTES -->
<div id="reportes" class="section-container active">
    <section>
        <h2>Reportes e Incidencias</h2>
        <div>
            <h3>Reportes Recientes</h3>
            <ul>
                <?php foreach (($reportes_recientes ?? []) as $r): ?>
                <li><?php echo date('Y-m-d', strtotime($r['fecha_reporte'])); ?>: <?php echo htmlspecialchars($r['descripcion']); ?> - <?php echo htmlspecialchars($r['estado']); ?></li>
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