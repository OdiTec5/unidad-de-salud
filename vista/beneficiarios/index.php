<?php $tituloPagina = 'Beneficiarios'; require __DIR__ . '/../parciales/encabezado.php'; ?>

    <div class="tarjeta">
        <div class="cabecera-modulo">
            <h1>Beneficiarios</h1>
            <button type="button" id="boton-abrir-modal-beneficiario" class="boton-agregar">+ Agregar beneficiario</button>
        </div>

        <?php if (!empty($error)): ?>
            <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <p class="mensaje-exito"><?= htmlspecialchars($exito) ?></p>
        <?php endif; ?>

        <div class="tabla-scroll">
            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th>T. ident.</th>
                        <th>Documento</th>
                        <th>Pri. apellido</th>
                        <th>Seg. apellido</th>
                        <th>Pri. nombre</th>
                        <th>Seg. nombre</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($beneficiarios as $beneficiario): ?>
                    <tr>
                        <td><span class="etiqueta-prefijo"><?= htmlspecialchars($beneficiario['tipo_identificacion']) ?></span></td>
                        <td><?= htmlspecialchars($beneficiario['documento']) ?></td>
                        <td><?= htmlspecialchars($beneficiario['primer_apellido']) ?></td>
                        <td><?= htmlspecialchars($beneficiario['segundo_apellido'] ?? '') ?></td>
                        <td><?= htmlspecialchars($beneficiario['primer_nombre']) ?></td>
                        <td><?= htmlspecialchars($beneficiario['segundo_nombre'] ?? '') ?></td>
                        <td class="celda-acciones">
                            <div class="acciones-fila">
                                <form method="POST" action="index.php?ruta=beneficiarios" class="form-toggle">
                                    <input type="hidden" name="accion" value="alternar_bloqueo">
                                    <input type="hidden" name="id" value="<?= (int) $beneficiario['id'] ?>">
                                    <button
                                        type="submit"
                                        class="interruptor<?= (int) $beneficiario['bloqueado'] === 1 ? ' interruptor-bloqueado' : '' ?>"
                                        aria-label="Bloquear o desbloquear"
                                        title="<?= (int) $beneficiario['bloqueado'] === 1 ? 'Bloqueado (clic para desbloquear)' : 'Desbloqueado (clic para bloquear)' ?>"
                                    >
                                        <span class="interruptor-perilla"></span>
                                    </button>
                                    <span class="interruptor-etiqueta"><?= (int) $beneficiario['bloqueado'] === 1 ? 'Bloqueado' : 'Desbloqueado' ?></span>
                                </form>
                                <button
                                    type="button"
                                    class="boton-accion boton-accion-editar boton-editar-beneficiario"
                                    data-id="<?= (int) $beneficiario['id'] ?>"
                                    data-tipo="<?= htmlspecialchars($beneficiario['tipo_identificacion']) ?>"
                                    data-documento="<?= htmlspecialchars($beneficiario['documento']) ?>"
                                    data-primer-apellido="<?= htmlspecialchars($beneficiario['primer_apellido']) ?>"
                                    data-segundo-apellido="<?= htmlspecialchars($beneficiario['segundo_apellido'] ?? '') ?>"
                                    data-primer-nombre="<?= htmlspecialchars($beneficiario['primer_nombre']) ?>"
                                    data-segundo-nombre="<?= htmlspecialchars($beneficiario['segundo_nombre'] ?? '') ?>"
                                >Editar</button>
                                <form method="POST" action="index.php?ruta=beneficiarios" class="form-eliminar-beneficiario">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int) $beneficiario['id'] ?>">
                                    <button type="submit" class="boton-accion boton-accion-eliminar">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($beneficiarios)): ?>
                    <tr>
                        <td colspan="7">No hay beneficiarios registrados.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-agregar-beneficiario" class="modal-fondo">
        <div class="modal-caja modal-caja-media">
            <div class="modal-cabecera">
                <h2>Agregar beneficiario</h2>
                <button type="button" id="boton-cerrar-modal-beneficiario" class="modal-cerrar" aria-label="Cerrar">&times;</button>
            </div>

            <form method="POST" action="index.php?ruta=beneficiarios" class="form-2col">
                <input type="hidden" name="accion" value="crear">

                <div class="campo">
                    <label for="beneficiario-tipo">Tipo de identificación *</label>
                    <select id="beneficiario-tipo" name="tipo_identificacion" required>
                        <option value="">Selecciona un tipo</option>
                        <?php foreach ($tipos as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label for="beneficiario-documento">Documento *</label>
                    <input type="text" id="beneficiario-documento" name="documento" required>
                </div>

                <div class="campo">
                    <label for="beneficiario-primer-apellido">Primer apellido *</label>
                    <input type="text" id="beneficiario-primer-apellido" name="primer_apellido" required>
                </div>

                <div class="campo">
                    <label for="beneficiario-segundo-apellido">Segundo apellido</label>
                    <input type="text" id="beneficiario-segundo-apellido" name="segundo_apellido">
                </div>

                <div class="campo">
                    <label for="beneficiario-primer-nombre">Primer nombre *</label>
                    <input type="text" id="beneficiario-primer-nombre" name="primer_nombre" required>
                </div>

                <div class="campo">
                    <label for="beneficiario-segundo-nombre">Segundo nombre</label>
                    <input type="text" id="beneficiario-segundo-nombre" name="segundo_nombre">
                </div>

                <button type="submit" class="boton-enviar">Agregar</button>
            </form>
        </div>
    </div>

    <div id="modal-editar-beneficiario" class="modal-fondo">
        <div class="modal-caja modal-caja-media">
            <div class="modal-cabecera">
                <h2>Editar beneficiario</h2>
                <button type="button" id="boton-cerrar-modal-editar-beneficiario" class="modal-cerrar" aria-label="Cerrar">&times;</button>
            </div>

            <form method="POST" action="index.php?ruta=beneficiarios" class="form-2col">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="id" id="editar-beneficiario-id" value="">

                <div class="campo">
                    <label for="editar-beneficiario-tipo">Tipo de identificación *</label>
                    <select id="editar-beneficiario-tipo" name="tipo_identificacion" required>
                        <option value="">Selecciona un tipo</option>
                        <?php foreach ($tipos as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label for="editar-beneficiario-documento">Documento *</label>
                    <input type="text" id="editar-beneficiario-documento" name="documento" required>
                </div>

                <div class="campo">
                    <label for="editar-beneficiario-primer-apellido">Primer apellido *</label>
                    <input type="text" id="editar-beneficiario-primer-apellido" name="primer_apellido" required>
                </div>

                <div class="campo">
                    <label for="editar-beneficiario-segundo-apellido">Segundo apellido</label>
                    <input type="text" id="editar-beneficiario-segundo-apellido" name="segundo_apellido">
                </div>

                <div class="campo">
                    <label for="editar-beneficiario-primer-nombre">Primer nombre *</label>
                    <input type="text" id="editar-beneficiario-primer-nombre" name="primer_nombre" required>
                </div>

                <div class="campo">
                    <label for="editar-beneficiario-segundo-nombre">Segundo nombre</label>
                    <input type="text" id="editar-beneficiario-segundo-nombre" name="segundo_nombre">
                </div>

                <button type="submit" class="boton-enviar">Guardar cambios</button>
            </form>
        </div>
    </div>

<?php require __DIR__ . '/../parciales/pie.php'; ?>
