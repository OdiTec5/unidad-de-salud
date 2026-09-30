<?php $tituloPagina = 'Categorías turnero'; require __DIR__ . '/../parciales/encabezado.php'; ?>

    <div class="tarjeta">
        <div class="cabecera-modulo">
            <h1>Categorías turnero</h1>
        </div>
        <p class="texto-atenuado">Cada categoría genera turnos con su prefijo (ej. Consulta general → A-001, A-002...).</p>

        <?php if (!empty($error)): ?>
            <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <p class="mensaje-exito"><?= htmlspecialchars($exito) ?></p>
        <?php endif; ?>

        <form method="POST" action="index.php?ruta=categorias-turnero" class="form-agregar">
            <input type="text" name="nombre" placeholder="Nombre de la categoría" required>
            <input type="text" name="prefijo" placeholder="Prefijo (ej. A)" maxlength="5" required style="max-width: 140px;">
            <select name="ventanilla_id" style="max-width: 220px;">
                <option value="">Sin ventanilla asignada</option>
                <?php foreach ($ventanillas as $ventanilla): ?>
                <option value="<?= (int) $ventanilla['id'] ?>"><?= htmlspecialchars($ventanilla['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="color" name="color" value="#0071e3" class="campo-color" title="Color de la categoría">
            <button type="submit">Agregar</button>
        </form>

        <div class="tabla-scroll">
            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nombre</th>
                        <th>Prefijo</th>
                        <th>Subcategorías</th>
                        <th>Ventanilla</th>
                        <th>Horario</th>
                        <th>Creado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $categoria): ?>
                    <?php $subcategoriasCategoria = $subcategoriasPorCategoria[(int) $categoria['id']] ?? []; ?>
                    <?php $horarioDescrito = CategoriaTurnero::describirHorario($categoria); ?>
                    <tr>
                        <td><span class="muestra-color" style="background: <?= htmlspecialchars($categoria['color']) ?>;"></span></td>
                        <td><?= htmlspecialchars($categoria['nombre']) ?></td>
                        <td><span class="etiqueta-prefijo"><?= htmlspecialchars($categoria['prefijo']) ?></span></td>
                        <td><?= count($subcategoriasCategoria) ?></td>
                        <td><?= $categoria['ventanilla_nombre'] !== null ? htmlspecialchars($categoria['ventanilla_nombre']) : '<span class="texto-atenuado">Sin asignar</span>' ?></td>
                        <td><?= $horarioDescrito !== null ? htmlspecialchars($horarioDescrito) : '<span class="texto-atenuado">Sin restricción</span>' ?></td>
                        <td><?= htmlspecialchars($categoria['creado_en']) ?></td>
                        <td class="celda-acciones">
                            <div class="acciones-fila">
                                <button
                                    type="button"
                                    class="boton-accion boton-accion-ver boton-subcategorias-turnero"
                                    data-id="<?= (int) $categoria['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($categoria['nombre']) ?>"
                                >Subcategorías</button>
                                <button
                                    type="button"
                                    class="boton-accion boton-accion-editar boton-editar-categoria-turnero"
                                    data-id="<?= (int) $categoria['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($categoria['nombre']) ?>"
                                    data-prefijo="<?= htmlspecialchars($categoria['prefijo']) ?>"
                                    data-color="<?= htmlspecialchars($categoria['color']) ?>"
                                    data-ventanilla-id="<?= (int) ($categoria['ventanilla_id'] ?? 0) ?>"
                                    data-hora-inicio="<?= htmlspecialchars($categoria['hora_inicio'] !== null ? substr($categoria['hora_inicio'], 0, 5) : '') ?>"
                                    data-hora-fin="<?= htmlspecialchars($categoria['hora_fin'] !== null ? substr($categoria['hora_fin'], 0, 5) : '') ?>"
                                    data-dias-semana="<?= htmlspecialchars($categoria['dias_semana'] ?? '') ?>"
                                >Editar</button>
                                <form method="POST" action="index.php?ruta=categorias-turnero" class="form-eliminar-categoria-turnero">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int) $categoria['id'] ?>">
                                    <button type="submit" class="boton-accion boton-accion-eliminar">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categorias)): ?>
                    <tr>
                        <td colspan="8">No hay categorías registradas.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-editar-categoria-turnero" class="modal-fondo">
        <div class="modal-caja modal-caja-media">
            <div class="modal-cabecera">
                <h2>Editar categoría</h2>
                <button type="button" id="boton-cerrar-modal-editar-categoria-turnero" class="modal-cerrar" aria-label="Cerrar">&times;</button>
            </div>

            <form method="POST" action="index.php?ruta=categorias-turnero" class="form-2col">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="id" id="editar-categoria-turnero-id" value="">

                <div class="campo">
                    <label for="editar-categoria-turnero-nombre">Nombre *</label>
                    <input type="text" id="editar-categoria-turnero-nombre" name="nombre" required>
                </div>

                <div class="campo">
                    <label for="editar-categoria-turnero-prefijo">Prefijo *</label>
                    <input type="text" id="editar-categoria-turnero-prefijo" name="prefijo" maxlength="5" required>
                </div>

                <div class="campo">
                    <label for="editar-categoria-turnero-color">Color</label>
                    <input type="color" id="editar-categoria-turnero-color" name="color" class="campo-color">
                </div>

                <div class="campo">
                    <label for="editar-categoria-turnero-ventanilla">Ventanilla</label>
                    <select id="editar-categoria-turnero-ventanilla" name="ventanilla_id">
                        <option value="">Sin ventanilla asignada</option>
                        <?php foreach ($ventanillas as $ventanilla): ?>
                        <option value="<?= (int) $ventanilla['id'] ?>"><?= htmlspecialchars($ventanilla['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label for="editar-categoria-turnero-hora-inicio">Horario desde</label>
                    <input type="time" id="editar-categoria-turnero-hora-inicio" name="hora_inicio">
                </div>

                <div class="campo">
                    <label for="editar-categoria-turnero-hora-fin">Horario hasta</label>
                    <input type="time" id="editar-categoria-turnero-hora-fin" name="hora_fin">
                </div>

                <div class="campo campo-ancho">
                    <label>Días de atención</label>
                    <div class="grupo-checkbox-dias">
                        <?php foreach (CategoriaTurnero::DIAS_SEMANA as $numeroDia => $etiquetaDia): ?>
                        <label class="campo-checkbox">
                            <input type="checkbox" name="dias[]" value="<?= $numeroDia ?>" class="editar-categoria-turnero-dia">
                            <?= htmlspecialchars($etiquetaDia) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="texto-atenuado" style="margin: 0.4rem 0 0; font-size: 0.8rem;">
                        Si no marcas ninguna hora ni día, la categoría queda disponible siempre.
                    </p>
                </div>

                <button type="submit" class="boton-enviar">Guardar cambios</button>
            </form>
        </div>
    </div>

    <div id="modal-subcategorias-turnero" class="modal-fondo">
        <div class="modal-caja">
            <div class="modal-cabecera">
                <h2>Subcategorías de <span id="subcategorias-turnero-nombre-categoria"></span></h2>
                <button type="button" id="boton-cerrar-modal-subcategorias-turnero" class="modal-cerrar" aria-label="Cerrar">&times;</button>
            </div>

            <div id="subcategorias-turnero-lista" class="lista-subcategorias"></div>

            <form method="POST" action="index.php?ruta=categorias-turnero" class="form-agregar" style="margin-top: 1.25rem;">
                <input type="hidden" name="accion" value="crear_subcategoria">
                <input type="hidden" name="categoria_id" id="subcategorias-turnero-categoria-id" value="">
                <input type="text" name="nombre_subcategoria" placeholder="Nombre de la subcategoría" required>
                <button type="submit">Agregar</button>
            </form>
        </div>
    </div>

    <script id="datos-subcategorias-turnero" type="application/json"><?= json_encode($subcategoriasPorCategoria, JSON_UNESCAPED_UNICODE) ?></script>

<?php require __DIR__ . '/../parciales/pie.php'; ?>
