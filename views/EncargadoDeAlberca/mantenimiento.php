<!-- SECCIÓN MANTENIMIENTO -->
<div id="mantenimiento" class="section-container active">
    <section>
        <h2>Registro de Mantenimiento</h2>
        <div>
            <h3>Mantenimientos Programados</h3>
            <ul>
                <?php foreach (($proximos_mantenimientos ?? []) as $m): ?>
                <li><?php echo $m['fecha_programada']; ?>: <?php echo htmlspecialchars($m['descripcion']); ?> - <?php echo htmlspecialchars($m['alberca']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h3>Registrar Mantenimiento Realizado</h3>
            <form method="POST">
                <input type="hidden" name="action" value="registrar_mantenimiento">
                <div>
                    <label>Alberca:</label>
                    <select name="id_alberca">
                        <option value="">Seleccione (opcional)</option>
                        <?php foreach (($catalogo_albercas ?? []) as $a): ?>
                        <option value="<?php echo $a['id_alberca']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Equipo (opcional):</label>
                    <select name="id_equipo">
                        <option value="">Seleccione un equipo</option>
                        <?php foreach (($catalogo_equipos ?? []) as $e): ?>
                        <option value="<?php echo $e['id_equipo']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Tipo de mantenimiento:</label>
                    <select name="id_tipo_mantenimiento" required>
                        <?php foreach (($catalogo_tipos_mantenimiento ?? []) as $tm): ?>
                        <option value="<?php echo $tm['id_tipo_mantenimiento']; ?>"><?php echo htmlspecialchars($tm['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Fecha programada:</label>
                    <input type="date" name="fecha_programada" required>
                </div>
                <div>
                    <label>Descripción del trabajo realizado:</label>
                    <textarea name="descripcion" rows="3" required></textarea>
                </div>
                <div>
                    <label>Técnico responsable:</label>
                    <select name="id_tecnico" required>
                        <?php foreach (($catalogo_tecnicos ?? []) as $t): ?>
                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Registrar Mantenimiento</button>
            </form>
        </div>
    </section>
</div>