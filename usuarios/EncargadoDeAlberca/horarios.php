        <!-- ═══════════════════════════════════════════════════════════
             SECCIÓN HORARIOS
        ═══════════════════════════════════════════════════════════ -->
        <div id="horarios" class="section-container">
            <section>
                <h2>Control de Horarios</h2>
                <div>
                    <h3>Horario Actual</h3>
                    <p>Apertura: <?php echo date('g:i A', strtotime($horario_actual['apertura'] ?? '09:00:00')); ?></p>
                    <p>Cierre: <?php echo date('g:i A', strtotime($horario_actual['cierre'] ?? '18:00:00')); ?></p>
                </div>
                <div>
                    <h3>Modificar Horario</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="actualizar_horario">
                        <div>
                            <label>Hora de apertura:</label>
                            <input type="time" name="apertura" value="<?php echo substr($horario_actual['apertura'] ?? '09:00:00', 0, 5); ?>" required>
                        </div>
                        <div>
                            <label>Hora de cierre:</label>
                            <input type="time" name="cierre" value="<?php echo substr($horario_actual['cierre'] ?? '18:00:00', 0, 5); ?>" required>
                        </div>
                        <div>
                            <label>Fecha especial (opcional):</label>
                            <input type="date" name="fecha_especial">
                        </div>
                        <button type="submit">Actualizar Horario</button>
                    </form>
                </div>
            </section>
        </div>