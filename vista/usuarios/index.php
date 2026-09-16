<?php $tituloPagina = 'Usuarios'; require __DIR__ . '/../parciales/encabezado.php'; ?>

    <div class="tarjeta">
        <div class="cabecera-modulo">
            <h1>Usuarios</h1>
            <button type="button" id="boton-abrir-modal-usuario" class="boton-agregar">+ Agregar usuario</button>
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
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Ventanilla</th>
                        <th>Creado</th>
                        <th>Último acceso</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td>
                            <?php if ((int) $usuario['es_administrador'] === 1): ?>
                            <span class="estrella-administrador" title="Administrador">★</span>
                            <?php endif; ?>
                            <?= htmlspecialchars($usuario['nombre']) ?>
                        </td>
                        <td><?= htmlspecialchars($usuario['correo']) ?></td>
                        <td><?= $usuario['ventanilla_nombre'] !== null ? htmlspecialchars($usuario['ventanilla_nombre']) : '<span class="texto-atenuado">—</span>' ?></td>
                        <td><?= htmlspecialchars($usuario['creado_en']) ?></td>
                        <td><?= $usuario['ultimo_acceso'] !== null ? htmlspecialchars($usuario['ultimo_acceso']) : '<span class="texto-atenuado">Nunca</span>' ?></td>
                        <td class="celda-acciones">
                            <div class="acciones-fila">
                                <button
                                    type="button"
                                    class="boton-accion boton-accion-editar boton-editar-usuario"
                                    data-id="<?= (int) $usuario['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($usuario['nombre']) ?>"
                                    data-correo="<?= htmlspecialchars($usuario['correo']) ?>"
                                    data-es-administrador="<?= (int) $usuario['es_administrador'] ?>"
                                    data-ventanilla-id="<?= (int) ($usuario['ventanilla_id'] ?? 0) ?>"
                                >Editar</button>
                                <?php if ((int) $usuario['id'] !== (int) $_SESSION['usuario_id']): ?>
                                <form method="POST" action="index.php?ruta=usuarios" class="form-eliminar-usuario">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                    <button type="submit" class="boton-accion boton-accion-eliminar">Eliminar</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="6">No hay usuarios registrados.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    $renderizarAlcance = static function (string $prefijo) use ($categorias, $subcategoriasPorCategoria): void {
        foreach ($categorias as $categoria) {
            $subcategoriasCategoria = $subcategoriasPorCategoria[(int) $categoria['id']] ?? [];
            ?>
            <div class="alcance-categoria">
                <label class="campo-checkbox">
                    <input type="checkbox" name="categorias[]" value="<?= (int) $categoria['id'] ?>" id="<?= $prefijo ?>-categoria-<?= (int) $categoria['id'] ?>">
                    <?= htmlspecialchars($categoria['nombre']) ?>
                    <span class="texto-atenuado">(toda la categoría)</span>
                </label>
                <?php if (!empty($subcategoriasCategoria)): ?>
                <div class="alcance-subcategorias">
                    <?php foreach ($subcategoriasCategoria as $subcategoria): ?>
                    <label class="campo-checkbox">
                        <input type="checkbox" name="subcategorias[]" value="<?= (int) $subcategoria['id'] ?>" id="<?= $prefijo ?>-subcategoria-<?= (int) $subcategoria['id'] ?>">
                        <?= htmlspecialchars($subcategoria['nombre']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php
        }
    };
    ?>

    <div id="modal-agregar-usuario" class="modal-fondo">
        <div class="modal-caja modal-caja-media">
            <div class="modal-cabecera">
                <h2>Agregar usuario</h2>
                <button type="button" id="boton-cerrar-modal-usuario" class="modal-cerrar" aria-label="Cerrar">&times;</button>
            </div>

            <form method="POST" action="index.php?ruta=usuarios">
                <input type="hidden" name="accion" value="crear">

                <div class="campo">
                    <label for="usuario-nombre">Nombre completo *</label>
                    <input type="text" id="usuario-nombre" name="nombre" placeholder="Nombre completo" required>
                </div>

                <div class="campo">
                    <label for="usuario-correo">Correo electrónico *</label>
                    <input type="email" id="usuario-correo" name="correo" placeholder="nombre@correo.com" required>
                </div>

                <div class="campo">
                    <label for="usuario-password">Contraseña *</label>
                    <input type="password" id="usuario-password" name="password" placeholder="Mínimo 6 caracteres" required>
                </div>

                <label class="campo-checkbox campo-checkbox-administrador">
                    <input type="checkbox" name="es_administrador" value="1" id="usuario-es-administrador">
                    Es administrador (acceso total al sistema)
                </label>

                <div id="usuario-seccion-alcance">
                    <div class="campo">
                        <label for="usuario-ventanilla">Ventanilla asignada</label>
                        <select id="usuario-ventanilla" name="ventanilla_id">
                            <option value="">Sin ventanilla asignada</option>
                            <?php foreach ($ventanillas as $ventanilla): ?>
                            <option value="<?= (int) $ventanilla['id'] ?>"><?= htmlspecialchars($ventanilla['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label>Categorías / subcategorías que puede avanzar</label>
                        <div class="alcance-lista">
                            <?php $renderizarAlcance('agregar'); ?>
                            <?php if (empty($categorias)): ?>
                            <p class="texto-atenuado">Todavía no hay categorías de turnero creadas.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <button type="submit" class="boton-enviar">Agregar</button>
            </form>
        </div>
    </div>

    <div id="modal-editar-usuario" class="modal-fondo">
        <div class="modal-caja modal-caja-media">
            <div class="modal-cabecera">
                <h2>Editar usuario</h2>
                <button type="button" id="boton-cerrar-modal-editar-usuario" class="modal-cerrar" aria-label="Cerrar">&times;</button>
            </div>

            <form method="POST" action="index.php?ruta=usuarios">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="id" id="editar-usuario-id" value="">

                <div class="campo">
                    <label for="editar-usuario-nombre">Nombre completo *</label>
                    <input type="text" id="editar-usuario-nombre" name="nombre" required>
                </div>

                <div class="campo">
                    <label for="editar-usuario-correo">Correo electrónico *</label>
                    <input type="email" id="editar-usuario-correo" name="correo" required>
                </div>

                <div class="campo">
                    <label for="editar-usuario-password">Nueva contraseña</label>
                    <input type="password" id="editar-usuario-password" name="password" placeholder="Dejar en blanco para no cambiarla">
                </div>

                <label class="campo-checkbox campo-checkbox-administrador">
                    <input type="checkbox" name="es_administrador" value="1" id="editar-usuario-es-administrador">
                    Es administrador (acceso total al sistema)
                </label>

                <div id="editar-usuario-seccion-alcance">
                    <div class="campo">
                        <label for="editar-usuario-ventanilla">Ventanilla asignada</label>
                        <select id="editar-usuario-ventanilla" name="ventanilla_id">
                            <option value="">Sin ventanilla asignada</option>
                            <?php foreach ($ventanillas as $ventanilla): ?>
                            <option value="<?= (int) $ventanilla['id'] ?>"><?= htmlspecialchars($ventanilla['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label>Categorías / subcategorías que puede avanzar</label>
                        <div class="alcance-lista">
                            <?php $renderizarAlcance('editar'); ?>
                            <?php if (empty($categorias)): ?>
                            <p class="texto-atenuado">Todavía no hay categorías de turnero creadas.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <button type="submit" class="boton-enviar">Guardar cambios</button>
            </form>
        </div>
    </div>

    <script id="datos-alcance-categorias-usuario" type="application/json"><?= json_encode($categoriasPorUsuario, JSON_UNESCAPED_UNICODE) ?></script>
    <script id="datos-alcance-subcategorias-usuario" type="application/json"><?= json_encode($subcategoriasPorUsuario, JSON_UNESCAPED_UNICODE) ?></script>

<?php require __DIR__ . '/../parciales/pie.php'; ?>
