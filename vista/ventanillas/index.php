<?php $tituloPagina = 'Ventanillas'; require __DIR__ . '/../parciales/encabezado.php'; ?>

    <div class="tarjeta">
        <div class="cabecera-modulo">
            <h1>Ventanillas</h1>
        </div>
        <p class="texto-atenuado">Puntos de atención donde se llaman los turnos.</p>

        <?php if (!empty($error)): ?>
            <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <p class="mensaje-exito"><?= htmlspecialchars($exito) ?></p>
        <?php endif; ?>

        <form method="POST" action="index.php?ruta=ventanillas" class="form-agregar">
            <input type="text" name="nombre" placeholder="Nombre de la ventanilla (ej. Ventanilla 1)" required>
            <button type="submit">Agregar</button>
        </form>

        <div class="tabla-scroll">
            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Creada</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventanillas as $ventanilla): ?>
                    <tr>
                        <td><?= htmlspecialchars($ventanilla['nombre']) ?></td>
                        <td>
                            <form method="POST" action="index.php?ruta=ventanillas" class="form-toggle">
                                <input type="hidden" name="accion" value="alternar_activa">
                                <input type="hidden" name="id" value="<?= (int) $ventanilla['id'] ?>">
                                <button
                                    type="submit"
                                    class="interruptor<?= (int) $ventanilla['activa'] === 1 ? ' interruptor-activo' : '' ?>"
                                    aria-label="Activar o desactivar"
                                    title="<?= (int) $ventanilla['activa'] === 1 ? 'Activa (clic para desactivar)' : 'Inactiva (clic para activar)' ?>"
                                >
                                    <span class="interruptor-perilla"></span>
                                </button>
                                <span class="interruptor-etiqueta"><?= (int) $ventanilla['activa'] === 1 ? 'Activa' : 'Inactiva' ?></span>
                            </form>
                        </td>
                        <td><?= htmlspecialchars($ventanilla['creado_en']) ?></td>
                        <td class="celda-acciones">
                            <div class="acciones-fila">
                                <button
                                    type="button"
                                    class="boton-accion boton-accion-editar boton-editar-ventanilla"
                                    data-id="<?= (int) $ventanilla['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($ventanilla['nombre']) ?>"
                                >Editar</button>
                                <form method="POST" action="index.php?ruta=ventanillas" class="form-eliminar-ventanilla">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int) $ventanilla['id'] ?>">
                                    <button type="submit" class="boton-accion boton-accion-eliminar">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($ventanillas)): ?>
                    <tr>
                        <td colspan="4">No hay ventanillas registradas.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-editar-ventanilla" class="modal-fondo">
        <div class="modal-caja">
            <div class="modal-cabecera">
                <h2>Editar ventanilla</h2>
                <button type="button" id="boton-cerrar-modal-editar-ventanilla" class="modal-cerrar" aria-label="Cerrar">&times;</button>
            </div>

            <form method="POST" action="index.php?ruta=ventanillas">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="id" id="editar-ventanilla-id" value="">

                <div class="campo">
                    <label for="editar-ventanilla-nombre">Nombre *</label>
                    <input type="text" id="editar-ventanilla-nombre" name="nombre" required>
                </div>

                <button type="submit" class="boton-enviar">Guardar cambios</button>
            </form>
        </div>
    </div>

<?php require __DIR__ . '/../parciales/pie.php'; ?>
