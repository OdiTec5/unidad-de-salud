<?php $tituloPagina = 'Voz de anuncios'; require __DIR__ . '/../parciales/encabezado.php'; ?>

    <div class="tarjeta">
        <div class="cabecera-modulo">
            <h1>Voz de anuncios</h1>
        </div>
        <p class="texto-atenuado">
            Elige con qué voz la pantalla de turnos anuncia cada turno. La lista de voces depende del navegador
            donde abras esta página; si la pantalla de turnos se muestra en un equipo distinto y esa voz no existe
            ahí, se usará el idioma configurado como respaldo.
        </p>

        <?php if (!empty($error)): ?>
            <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <p class="mensaje-exito"><?= htmlspecialchars($exito) ?></p>
        <?php endif; ?>

        <p id="voz-aviso-no-soportado" class="mensaje-error" style="display: none;">
            Este navegador no soporta síntesis de voz (Web Speech API). Puedes guardar el idioma de todas formas.
        </p>

        <form method="POST" action="index.php?ruta=configuracion-voz" class="form-2col" id="form-configuracion-voz">
            <div class="campo campo-ancho">
                <label for="voz-select">Voz</label>
                <select id="voz-select" name="voz_nombre">
                    <option value="">Voz por defecto del navegador</option>
                </select>
            </div>

            <div class="campo">
                <label for="voz-idioma">Idioma de respaldo</label>
                <select id="voz-idioma" name="idioma">
                    <option value="es-ES">Español (España)</option>
                    <option value="es-MX">Español (México)</option>
                    <option value="es-CO">Español (Colombia)</option>
                    <option value="es-US">Español (Estados Unidos)</option>
                </select>
            </div>

            <div class="campo">
                <label for="voz-velocidad">Velocidad (<span id="voz-velocidad-valor"><?= htmlspecialchars((string) $configuracionVoz['velocidad']) ?></span>)</label>
                <input type="range" id="voz-velocidad" name="velocidad" min="0.5" max="2" step="0.05" value="<?= htmlspecialchars((string) $configuracionVoz['velocidad']) ?>">
            </div>

            <div class="campo">
                <label for="voz-tono">Tono (<span id="voz-tono-valor"><?= htmlspecialchars((string) $configuracionVoz['tono']) ?></span>)</label>
                <input type="range" id="voz-tono" name="tono" min="0" max="2" step="0.05" value="<?= htmlspecialchars((string) $configuracionVoz['tono']) ?>">
            </div>

            <div class="campo">
                <label for="voz-volumen">Volumen (<span id="voz-volumen-valor"><?= htmlspecialchars((string) $configuracionVoz['volumen']) ?></span>)</label>
                <input type="range" id="voz-volumen" name="volumen" min="0" max="1" step="0.05" value="<?= htmlspecialchars((string) $configuracionVoz['volumen']) ?>">
            </div>

            <div class="campo campo-ancho" style="flex-direction: row; gap: 0.75rem; align-items: center;">
                <button type="button" id="voz-boton-probar" class="boton-turno boton-turno-retroceder" style="width: auto;">🔊 Probar voz</button>
                <button type="submit" class="boton-enviar">Guardar</button>
            </div>
        </form>
    </div>

    <script>
        var vozGuardada = <?= json_encode($configuracionVoz['voz_nombre']) ?>;

        document.addEventListener('DOMContentLoaded', function () {
            var selectVoz = document.getElementById('voz-select');
            var selectIdioma = document.getElementById('voz-idioma');
            var avisoNoSoportado = document.getElementById('voz-aviso-no-soportado');
            var rangoVelocidad = document.getElementById('voz-velocidad');
            var rangoTono = document.getElementById('voz-tono');
            var rangoVolumen = document.getElementById('voz-volumen');
            var botonProbar = document.getElementById('voz-boton-probar');

            selectIdioma.value = <?= json_encode($configuracionVoz['idioma']) ?>;

            ['velocidad', 'tono', 'volumen'].forEach(function (campo) {
                var rango = document.getElementById('voz-' + campo);
                var etiqueta = document.getElementById('voz-' + campo + '-valor');
                rango.addEventListener('input', function () {
                    etiqueta.textContent = rango.value;
                });
            });

            if (!('speechSynthesis' in window)) {
                avisoNoSoportado.style.display = 'block';
                if (botonProbar) {
                    botonProbar.disabled = true;
                }
                return;
            }

            function cargarVoces() {
                var voces = window.speechSynthesis.getVoices();
                selectVoz.innerHTML = '<option value="">Voz por defecto del navegador</option>';

                voces.forEach(function (voz) {
                    var opcion = document.createElement('option');
                    opcion.value = voz.name;
                    opcion.textContent = voz.name + ' (' + voz.lang + ')';
                    if (voz.name === vozGuardada) {
                        opcion.selected = true;
                    }
                    selectVoz.appendChild(opcion);
                });
            }

            cargarVoces();
            window.speechSynthesis.addEventListener('voiceschanged', cargarVoces);

            botonProbar.addEventListener('click', function () {
                var voces = window.speechSynthesis.getVoices();
                var vozElegida = voces.find(function (v) { return v.name === selectVoz.value; });

                var utterance = new SpeechSynthesisUtterance('Turno A, número 5. Por favor, diríjase a Ventanilla 2.');
                utterance.lang = selectIdioma.value;
                utterance.rate = parseFloat(rangoVelocidad.value);
                utterance.pitch = parseFloat(rangoTono.value);
                utterance.volume = parseFloat(rangoVolumen.value);

                if (vozElegida) {
                    utterance.voice = vozElegida;
                }

                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(utterance);
            });
        });
    </script>

<?php require __DIR__ . '/../parciales/pie.php'; ?>
