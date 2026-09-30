<?php $tituloPagina = 'Reporte de atención'; require __DIR__ . '/../parciales/encabezado.php'; ?>

    <div class="tarjeta">
        <div class="cabecera-modulo">
            <h1>Reporte de atención</h1>
        </div>

        <form method="GET" action="index.php" class="form-agregar">
            <input type="hidden" name="ruta" value="reporte-atencion">
            <div class="campo">
                <label for="reporte-fecha">Fecha</label>
                <input type="date" id="reporte-fecha" name="fecha" value="<?= htmlspecialchars($fecha) ?>" max="<?= htmlspecialchars(date('Y-m-d')) ?>" onchange="this.form.submit()">
            </div>
        </form>

        <div class="reporte-resumen-grid">
            <div class="reporte-tarjeta-stat">
                <span class="reporte-stat-valor"><?= (int) $resumenGeneral['total'] ?></span>
                <span class="reporte-stat-etiqueta">Turnos generados</span>
            </div>
            <div class="reporte-tarjeta-stat">
                <span class="reporte-stat-valor"><?= (int) $resumenGeneral['atendidos'] ?></span>
                <span class="reporte-stat-etiqueta">Atendidos</span>
            </div>
            <div class="reporte-tarjeta-stat">
                <span class="reporte-stat-valor"><?= (int) $resumenGeneral['en_espera'] ?></span>
                <span class="reporte-stat-etiqueta">En espera</span>
            </div>
            <div class="reporte-tarjeta-stat">
                <span class="reporte-stat-valor"><?= $resumenGeneral['tiempo_promedio_minutos'] !== null ? htmlspecialchars((string) $resumenGeneral['tiempo_promedio_minutos']) . ' min' : '—' ?></span>
                <span class="reporte-stat-etiqueta">Tiempo promedio de atención</span>
            </div>
        </div>
    </div>

    <div class="tarjeta" style="margin-top: 1.5rem;">
        <h2>Por categoría</h2>
        <div class="tabla-scroll">
            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th></th>
                        <th>Categoría</th>
                        <th>Atendidos</th>
                        <th>En espera</th>
                        <th>Total</th>
                        <th>Tiempo promedio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resumenPorCategoria as $fila): ?>
                    <tr>
                        <td><span class="muestra-color" style="background: <?= htmlspecialchars($fila['color']) ?>;"></span></td>
                        <td><span class="etiqueta-prefijo"><?= htmlspecialchars($fila['prefijo']) ?></span> <?= htmlspecialchars($fila['nombre']) ?></td>
                        <td><?= (int) $fila['atendidos'] ?></td>
                        <td><?= (int) $fila['en_espera'] ?></td>
                        <td><?= (int) $fila['total'] ?></td>
                        <td><?= $fila['tiempo_promedio_minutos'] !== null ? htmlspecialchars((string) $fila['tiempo_promedio_minutos']) . ' min' : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($resumenPorCategoria)): ?>
                    <tr>
                        <td colspan="6">No hay categorías registradas.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="tarjeta" style="margin-top: 1.5rem;">
        <h2>Por ventanilla</h2>
        <div class="tabla-scroll">
            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th>Ventanilla</th>
                        <th>Atendidos</th>
                        <th>Tiempo promedio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resumenPorVentanilla as $fila): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['nombre']) ?></td>
                        <td><?= (int) $fila['atendidos'] ?></td>
                        <td><?= $fila['tiempo_promedio_minutos'] !== null ? htmlspecialchars((string) $fila['tiempo_promedio_minutos']) . ' min' : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($resumenPorVentanilla)): ?>
                    <tr>
                        <td colspan="3">Ninguna ventanilla atendió turnos ese día.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="tarjeta" style="margin-top: 1.5rem;">
        <h2>Detalle de turnos atendidos</h2>
        <div class="tabla-scroll">
            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th>Turno</th>
                        <th>Categoría</th>
                        <th>Ventanilla</th>
                        <th>Beneficiario</th>
                        <th>Solicitado</th>
                        <th>Atendido</th>
                        <th>Espera</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalle as $fila): ?>
                    <tr>
                        <td><span class="etiqueta-prefijo"><?= htmlspecialchars($fila['numero_turno']) ?></span></td>
                        <td><?= htmlspecialchars($fila['categoria_nombre']) ?><?= !empty($fila['subcategoria_nombre']) ? ' · ' . htmlspecialchars($fila['subcategoria_nombre']) : '' ?></td>
                        <td><?= $fila['ventanilla_nombre'] !== null ? htmlspecialchars($fila['ventanilla_nombre']) : '<span class="texto-atenuado">—</span>' ?></td>
                        <td><?= htmlspecialchars($fila['beneficiario_nombre']) ?></td>
                        <td><?= htmlspecialchars(substr($fila['creado_en'], 11, 5)) ?></td>
                        <td><?= htmlspecialchars(substr($fila['atendido_en'], 11, 5)) ?></td>
                        <td><?= (int) $fila['espera_minutos'] ?> min</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($detalle)): ?>
                    <tr>
                        <td colspan="7">No hay turnos atendidos ese día.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require __DIR__ . '/../parciales/pie.php'; ?>
