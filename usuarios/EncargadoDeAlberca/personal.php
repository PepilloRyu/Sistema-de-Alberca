        <!-- ═══════════════════════════════════════════════════════════
             SECCIÓN PERSONAL
        ═══════════════════════════════════════════════════════════ -->
        <div id="personal" class="section-container">
            <section>
                <h2>Personal de Limpieza</h2>
                <div>
                    <h3>Personal Asignado Hoy</h3>
                    <ul>
                        <?php foreach (($personal_asignado ?? []) as $p): ?>
                        <li>
                            <?php echo htmlspecialchars($p['nombre']); ?> -
                            <?php echo htmlspecialchars($p['turno']); ?>
                            (<?php echo date('g:i A', strtotime($p['hora_inicio'])); ?> -
                             <?php echo date('g:i A', strtotime($p['hora_fin'])); ?>) -
                            Área: <?php echo htmlspecialchars($p['area']); ?>
                        </li>
                        <?php endforeach; ?>
                        <?php if (empty($personal_asignado ?? [])): ?>
                        <li>No hay personal asignado hoy.</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div>
                    <h3>Asignar Personal</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="asignar_personal">
                        <div>
                            <label>Personal de limpieza:</label>
                            <select name="id_personal" required>
                                <?php foreach (($catalogo_personal_limpieza ?? []) as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Turno:</label>
                            <select name="id_turno" required>
                                <?php foreach (($catalogo_turnos ?? []) as $t): ?>
                                <option value="<?php echo $t['id_turno']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Área asignada:</label>
                            <select name="id_area" required>
                                <?php foreach (($catalogo_areas ?? []) as $a): ?>
                                <option value="<?php echo $a['id_area']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit">Asignar</button>
                    </form>
                </div>
            </section>
        </div>