        <!-- ═══════════════════════════════════════════════════════════
             SECCIÓN CONTROL DE ACCESO
        ═══════════════════════════════════════════════════════════ -->
        <div id="acceso" class="section-container">
            <section>
                <h2>Control de Acceso y Capacidad</h2>
                <div>
                    <h3>Aforo Actual</h3>
                    <ul>
                        <?php foreach (($albercas ?? []) as $a):
                            $porcentaje = $a['capacidad_maxima'] > 0
                                ? round(($a['aforo_actual'] / $a['capacidad_maxima']) * 100)
                                : 0;
                        ?>
                        <li>
                            <?php echo htmlspecialchars($a['nombre']); ?>:
                            <?php echo $a['aforo_actual']; ?>/<?php echo $a['capacidad_maxima']; ?> personas
                            (<?php echo $porcentaje; ?>%)
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h3>Total de visitantes hoy: <?php echo $visitantes_hoy ?? 0; ?> personas</h3>
                    <h3>Capacidad máxima total: <?php echo $capacidad_total ?? 570; ?> personas</h3>
                    <h3>Porcentaje de ocupación: <?php echo $porcentaje_ocupacion ?? 0; ?>%</h3>
                </div>
                <div>
                    <h3>Registrar Acceso</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="registrar_acceso">
                        <div>
                            <label>Tipo de registro:</label>
                            <select name="tipo_acceso" required>
                                <option value="entrada">Entrada de visitante</option>
                                <option value="salida">Salida de visitante</option>
                            </select>
                        </div>
                        <div>
                            <label>Cantidad de personas:</label>
                            <input type="number" name="cantidad" min="1" max="20" value="1" required>
                        </div>
                        <div>
                            <label>Alberca destino:</label>
                            <select name="id_alberca" required>
                                <?php foreach (($albercas ?? []) as $a): ?>
                                <option value="<?php echo $a['id_alberca']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit">Registrar</button>
                    </form>
                </div>
            </section>
        </div>