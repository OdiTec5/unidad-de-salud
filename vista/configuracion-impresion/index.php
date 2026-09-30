<?php $tituloPagina = 'Impresión de recibos'; require __DIR__ . '/../parciales/encabezado.php'; ?>

    <div class="tarjeta">
        <div class="cabecera-modulo">
            <h1>Impresión de recibos</h1>
        </div>
        <p class="texto-atenuado">
            Configura el ancho del rollo de papel de la impresora de calor donde se imprime el recibo del turno
            (58 mm o 80 mm son los tamaños más comunes). Este ajuste se usa en la página de "Solicitar turno".
        </p>

        <?php if (!empty($exito)): ?>
            <p class="mensaje-exito"><?= htmlspecialchars($exito) ?></p>
        <?php endif; ?>

        <div class="config-impresion-grid">
            <form method="POST" action="index.php?ruta=configuracion-impresion" class="form-2col" id="form-configuracion-impresion">
                <div class="campo campo-ancho">
                    <label for="impresion-ancho">Ancho del papel</label>
                    <select id="impresion-ancho" name="ancho_papel">
                        <option value="58mm" <?= $configuracionImpresion['ancho_papel'] === '58mm' ? 'selected' : '' ?>>58 mm</option>
                        <option value="80mm" <?= $configuracionImpresion['ancho_papel'] === '80mm' ? 'selected' : '' ?>>80 mm</option>
                    </select>
                </div>

                <label class="campo-checkbox campo-checkbox-administrador">
                    <input type="checkbox" name="auto_imprimir" value="1" <?= $configuracionImpresion['auto_imprimir'] ? 'checked' : '' ?>>
                    Imprimir automáticamente al generar el turno
                </label>

                <button type="submit" class="boton-enviar">Guardar</button>
            </form>

            <div class="config-impresion-previsualizacion">
                <p class="texto-atenuado" style="margin-bottom: 0.75rem;">Vista previa</p>
                <div class="recibo-preview" id="recibo-preview">
                    <p class="recibo-marca">Unidad de Salud</p>
                    <p class="recibo-etiqueta">Su turno es</p>
                    <p class="recibo-numero">A-005</p>
                    <p class="recibo-categoria">Consulta general</p>
                    <p class="recibo-fecha">16/09/2026 10:32</p>
                    <p class="recibo-ayuda">Espere a ser llamado en pantalla</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var selectAncho = document.getElementById('impresion-ancho');
            var preview = document.getElementById('recibo-preview');

            function actualizarPreview() {
                preview.style.width = selectAncho.value === '58mm' ? '58mm' : '80mm';
            }

            selectAncho.addEventListener('change', actualizarPreview);
            actualizarPreview();
        });
    </script>

<?php require __DIR__ . '/../parciales/pie.php'; ?>
