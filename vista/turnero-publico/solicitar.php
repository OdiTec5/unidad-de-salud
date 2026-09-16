<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Solicitar turno · Unidad de Salud</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body class="pagina-turnero-solicitar">
    <div class="turnero-tarjeta">
        <div class="turnero-marca">
            <span class="turnero-marca-icono" aria-hidden="true">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            </span>
            <div>
                <p class="turnero-marca-titulo">Unidad de Salud</p>
                <p class="turnero-marca-subtitulo">Solicitud de turno</p>
            </div>
        </div>

        <?php if ($paso === 'documento'): ?>
            <h1 class="turnero-paso-titulo">Ingresa tu documento</h1>
            <p class="turnero-paso-texto">Verificaremos que estés registrado como beneficiario para asignarte un turno.</p>

            <?php if (!empty($error)): ?>
                <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" action="index.php?ruta=turno-solicitar" class="turnero-form">
                <input type="hidden" name="accion" value="validar_documento">

                <div class="campo">
                    <label for="tipo_identificacion">Tipo de documento</label>
                    <select id="tipo_identificacion" name="tipo_identificacion" required autofocus>
                        <option value="">Selecciona un tipo</option>
                        <option value="CC">Cédula de ciudadanía</option>
                        <option value="TI">Tarjeta de identidad</option>
                        <option value="RC">Registro civil</option>
                        <option value="CE">Cédula de extranjería</option>
                        <option value="PA">Pasaporte</option>
                        <option value="PEP">Permiso especial de permanencia</option>
                        <option value="PT">Permiso por protección temporal</option>
                    </select>
                </div>

                <div class="campo">
                    <label for="documento">Número de documento</label>
                    <input type="text" id="documento" name="documento" inputmode="numeric" placeholder="Ej. 1002003" required>
                </div>

                <button type="submit" class="turnero-boton-primario">Continuar</button>
            </form>

        <?php elseif ($paso === 'categoria'): ?>
            <h1 class="turnero-paso-titulo">¡Hola, <?= htmlspecialchars($beneficiario['primer_nombre']) ?>!</h1>
            <p class="turnero-paso-texto">Selecciona el servicio para el que necesitas turno.</p>

            <?php if (!empty($error)): ?>
                <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <div class="turnero-categorias">
                <?php foreach ($categorias as $categoria): ?>
                <form method="POST" action="index.php?ruta=turno-solicitar" class="turnero-categoria-form">
                    <input type="hidden" name="accion" value="elegir_categoria">
                    <input type="hidden" name="tipo_identificacion" value="<?= htmlspecialchars($beneficiario['tipo_identificacion']) ?>">
                    <input type="hidden" name="documento" value="<?= htmlspecialchars($beneficiario['documento']) ?>">
                    <input type="hidden" name="categoria_id" value="<?= (int) $categoria['id'] ?>">
                    <button type="submit" class="turnero-categoria-boton" style="--color-categoria: <?= htmlspecialchars($categoria['color']) ?>;">
                        <span class="turnero-categoria-prefijo"><?= htmlspecialchars($categoria['prefijo']) ?></span>
                        <span class="turnero-categoria-nombre"><?= htmlspecialchars($categoria['nombre']) ?></span>
                    </button>
                </form>
                <?php endforeach; ?>

                <?php if (empty($categorias)): ?>
                <p class="texto-atenuado">Por ahora no hay categorías de turno disponibles. Acércate al mostrador.</p>
                <?php endif; ?>
            </div>

            <p class="enlace-secundario"><a href="index.php?ruta=turno-solicitar">Usar otro documento</a></p>

        <?php elseif ($paso === 'subcategoria'): ?>
            <h1 class="turnero-paso-titulo"><?= htmlspecialchars($categoriaSeleccionada['nombre']) ?></h1>
            <p class="turnero-paso-texto">Selecciona la opción que mejor describe tu solicitud.</p>

            <?php if (!empty($error)): ?>
                <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <div class="turnero-categorias">
                <?php foreach ($subcategorias as $subcategoria): ?>
                <form method="POST" action="index.php?ruta=turno-solicitar" class="turnero-categoria-form">
                    <input type="hidden" name="accion" value="confirmar_turno">
                    <input type="hidden" name="tipo_identificacion" value="<?= htmlspecialchars($beneficiario['tipo_identificacion']) ?>">
                    <input type="hidden" name="documento" value="<?= htmlspecialchars($beneficiario['documento']) ?>">
                    <input type="hidden" name="categoria_id" value="<?= (int) $categoriaSeleccionada['id'] ?>">
                    <input type="hidden" name="subcategoria_id" value="<?= (int) $subcategoria['id'] ?>">
                    <button type="submit" class="turnero-categoria-boton" style="--color-categoria: <?= htmlspecialchars($categoriaSeleccionada['color']) ?>;">
                        <span class="turnero-categoria-nombre"><?= htmlspecialchars($subcategoria['nombre']) ?></span>
                    </button>
                </form>
                <?php endforeach; ?>
            </div>

            <form method="POST" action="index.php?ruta=turno-solicitar">
                <input type="hidden" name="accion" value="validar_documento">
                <input type="hidden" name="tipo_identificacion" value="<?= htmlspecialchars($beneficiario['tipo_identificacion']) ?>">
                <input type="hidden" name="documento" value="<?= htmlspecialchars($beneficiario['documento']) ?>">
                <p class="enlace-secundario"><button type="submit" class="enlace-boton">Elegir otra categoría</button></p>
            </form>

        <?php else: ?>
            <div class="turnero-confirmacion" style="--color-categoria: <?= htmlspecialchars($turnoCreado['categoria_color']) ?>;">
                <p class="turnero-confirmacion-etiqueta">Tu turno es</p>
                <p class="turnero-confirmacion-numero"><?= htmlspecialchars($turnoCreado['numero_turno']) ?></p>
                <p class="turnero-confirmacion-categoria">
                    <?= htmlspecialchars($turnoCreado['categoria_nombre']) ?><?= !empty($turnoCreado['subcategoria_nombre']) ? ' · ' . htmlspecialchars($turnoCreado['subcategoria_nombre']) : '' ?>
                </p>
            </div>
            <p class="turnero-paso-texto turnero-confirmacion-ayuda">Atento(a) a la pantalla de turnos. Te llamaremos cuando sea tu momento.</p>

            <a href="index.php?ruta=turno-solicitar" class="turnero-boton-primario turnero-boton-enlace">Solicitar otro turno</a>

            <script>
                setTimeout(function () {
                    window.location.href = 'index.php?ruta=turno-solicitar';
                }, 5000);
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
