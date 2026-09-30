<?php
$tituloPagina = 'Turnos';
$etiquetaCategoria = static function (array $turno): string {
    return $turno['categoria_nombre'] . (!empty($turno['subcategoria_nombre']) ? ' · ' . $turno['subcategoria_nombre'] : '');
};
require __DIR__ . '/../parciales/encabezado.php';
?>

    <div id="turno-toast" class="turno-toast" hidden></div>

    <div class="tarjeta turno-actual-panel" id="turno-actual-panel" style="--color-categoria: <?= htmlspecialchars($actual['categoria_color'] ?? '#0071e3') ?>;">
        <div class="cabecera-modulo">
            <h1>Turno actual</h1>
        </div>

        <p class="mensaje-error" id="turno-sin-alcance" <?= $sinAlcanceAsignado ? '' : 'hidden' ?>>
            No tienes categorías ni subcategorías asignadas. Pídele a un administrador que te asigne alguna en Usuarios.
        </p>

        <div class="turno-actual-info" id="turno-actual-info" <?= (!$sinAlcanceAsignado && $actual !== null) ? '' : 'hidden' ?>>
            <span class="turno-actual-numero" id="turno-actual-numero"><?= htmlspecialchars($actual['numero_turno'] ?? '') ?></span>
            <span class="turno-actual-categoria" id="turno-actual-categoria"><?= htmlspecialchars($actual !== null ? $etiquetaCategoria($actual) : '') ?></span>
            <span class="turno-actual-ventanilla" id="turno-actual-ventanilla" <?= !empty($actual['ventanilla_nombre']) ? '' : 'hidden' ?>>Diríjase a <?= htmlspecialchars($actual['ventanilla_nombre'] ?? '') ?></span>
            <span class="turno-actual-nombre" id="turno-actual-nombre"><?= htmlspecialchars($actual['beneficiario_nombre'] ?? '') ?></span>
        </div>

        <p class="texto-atenuado" id="turno-actual-vacio" <?= (!$sinAlcanceAsignado && $actual === null) ? '' : 'hidden' ?>>No hay turnos en espera.</p>

        <div class="turno-acciones" id="turno-acciones" <?= $sinAlcanceAsignado ? 'hidden' : '' ?>>
            <button type="button" id="boton-retroceder" class="boton-turno boton-turno-retroceder" title="Atajo: flecha izquierda" <?= $esAdministrador ? '' : 'hidden' ?>>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Retroceder
            </button>
            <button type="button" id="boton-avanzar" class="boton-turno boton-turno-avanzar" title="Atajo: flecha derecha" <?= $actual === null ? 'disabled' : '' ?>>
                Avanzar
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
        </div>
        <button type="button" id="boton-abrir-transferir" class="boton-turno boton-turno-transferir" <?= (!$sinAlcanceAsignado && $siguiente !== null) ? '' : 'hidden' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 1l4 4-4 4"></path><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><path d="M7 23l-4-4 4-4"></path><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
            Pasar el siguiente a un compañero
        </button>
    </div>

    <div class="tarjeta" style="margin-top: 1.5rem;" id="turno-proximos-tarjeta" <?= !empty($proximos) ? '' : 'hidden' ?>>
        <h2>Próximos en espera</h2>
        <div class="turno-proximos-lista" id="turno-proximos-lista">
            <?php foreach ($proximos as $proximo): ?>
            <div class="turno-proximo-item" style="--color-categoria: <?= htmlspecialchars($proximo['categoria_color']) ?>;">
                <span class="turno-proximo-numero"><?= htmlspecialchars($proximo['numero_turno']) ?></span>
                <span class="turno-proximo-categoria"><?= htmlspecialchars($etiquetaCategoria($proximo)) ?></span>
                <span class="turno-proximo-nombre"><?= htmlspecialchars($proximo['beneficiario_nombre']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="tarjeta" style="margin-top: 1.5rem;">
        <h2>Histórico por categoría (hoy)</h2>
        <div class="tabla-scroll">
            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th></th>
                        <th>Categoría</th>
                        <th>Atendidos hoy</th>
                        <th>En espera</th>
                        <th>Total hoy</th>
                    </tr>
                </thead>
                <tbody id="turno-historico-cuerpo">
                    <?php foreach ($resumen as $fila): ?>
                    <tr>
                        <td><span class="muestra-color" style="background: <?= htmlspecialchars($fila['color']) ?>;"></span></td>
                        <td><span class="etiqueta-prefijo"><?= htmlspecialchars($fila['prefijo']) ?></span> <?= htmlspecialchars($fila['nombre']) ?></td>
                        <td><?= (int) $fila['atendidos_hoy'] ?></td>
                        <td><?= (int) $fila['en_espera_hoy'] ?></td>
                        <td><?= (int) $fila['total_hoy'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($resumen)): ?>
                    <tr>
                        <td colspan="5">No hay categorías registradas.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-transferir-turno" class="modal-fondo">
        <div class="modal-caja">
            <div class="modal-cabecera">
                <h2>Pasar el siguiente turno a un compañero</h2>
                <button type="button" id="boton-cerrar-modal-transferir" class="modal-cerrar" aria-label="Cerrar">&times;</button>
            </div>

            <p class="texto-atenuado">
                El turno actual sigue contigo. El <strong>siguiente en tu fila</strong>,
                <strong id="transferir-numero-turno"></strong>, pasará a la fila de la categoría que elijas,
                con un número nuevo, para que lo atienda quien la maneje.
            </p>

            <form id="form-transferir" method="POST" action="index.php?ruta=turnos">
                <input type="hidden" name="accion" value="transferir">
                <input type="hidden" name="turno_id" id="transferir-turno-id" value="">

                <div class="campo">
                    <label for="transferir-categoria-destino">Categoría destino</label>
                    <select id="transferir-categoria-destino" name="categoria_destino_id" required></select>
                </div>

                <button type="submit" class="boton-enviar" id="boton-confirmar-transferir">Transferir</button>
            </form>
        </div>
    </div>

    <script id="datos-categorias-turnos" type="application/json"><?= json_encode($categorias, JSON_UNESCAPED_UNICODE) ?></script>
    <script id="datos-turno-actual-inicial" type="application/json"><?= json_encode($actual, JSON_UNESCAPED_UNICODE) ?></script>
    <script id="datos-turno-siguiente-inicial" type="application/json"><?= json_encode($siguiente, JSON_UNESCAPED_UNICODE) ?></script>

    <script>
        (function () {
            var categorias = JSON.parse(document.getElementById('datos-categorias-turnos').textContent || '[]');
            var actualId = null;
            var ultimoSiguienteCompleto = null;

            var panel = document.getElementById('turno-actual-panel');
            var elSinAlcance = document.getElementById('turno-sin-alcance');
            var elInfo = document.getElementById('turno-actual-info');
            var elVacio = document.getElementById('turno-actual-vacio');
            var elNumero = document.getElementById('turno-actual-numero');
            var elCategoria = document.getElementById('turno-actual-categoria');
            var elVentanilla = document.getElementById('turno-actual-ventanilla');
            var elNombre = document.getElementById('turno-actual-nombre');
            var elAcciones = document.getElementById('turno-acciones');
            var botonRetroceder = document.getElementById('boton-retroceder');
            var botonAvanzar = document.getElementById('boton-avanzar');
            var botonAbrirTransferir = document.getElementById('boton-abrir-transferir');
            var elProximosTarjeta = document.getElementById('turno-proximos-tarjeta');
            var elProximosLista = document.getElementById('turno-proximos-lista');
            var elHistoricoCuerpo = document.getElementById('turno-historico-cuerpo');
            var toast = document.getElementById('turno-toast');

            var modalTransferir = document.getElementById('modal-transferir-turno');
            var selectDestino = document.getElementById('transferir-categoria-destino');
            var campoTurnoId = document.getElementById('transferir-turno-id');
            var etiquetaNumeroTransferir = document.getElementById('transferir-numero-turno');

            function etiquetaCategoria(turno) {
                return turno.subcategoria_nombre
                    ? turno.categoria_nombre + ' · ' + turno.subcategoria_nombre
                    : turno.categoria_nombre;
            }

            function mostrarToast(mensaje, ok) {
                if (!mensaje) {
                    return;
                }
                toast.textContent = mensaje;
                toast.className = 'turno-toast turno-toast-visible ' + (ok ? 'turno-toast-exito' : 'turno-toast-error');
                toast.hidden = false;
                clearTimeout(toast._temporizador);
                toast._temporizador = setTimeout(function () {
                    toast.classList.remove('turno-toast-visible');
                    setTimeout(function () { toast.hidden = true; }, 250);
                }, 3200);
            }

            function pulsar(elemento) {
                elemento.classList.remove('turno-pulso');
                // Forzar reflow para poder reiniciar la animación aunque sea el mismo valor.
                void elemento.offsetWidth;
                elemento.classList.add('turno-pulso');
            }

            function renderizarActual(actual, sinAlcanceAsignado) {
                elSinAlcance.hidden = !sinAlcanceAsignado;
                elAcciones.hidden = sinAlcanceAsignado;

                if (sinAlcanceAsignado) {
                    elInfo.hidden = true;
                    elVacio.hidden = true;
                    return;
                }

                if (actual) {
                    var esNuevo = actual.id !== actualId;
                    actualId = actual.id;

                    elInfo.hidden = false;
                    elVacio.hidden = true;
                    elNumero.textContent = actual.numero_turno;
                    elCategoria.textContent = etiquetaCategoria(actual);
                    elNombre.textContent = actual.beneficiario_nombre;
                    panel.style.setProperty('--color-categoria', actual.categoria_color);

                    if (actual.ventanilla_nombre) {
                        elVentanilla.textContent = 'Diríjase a ' + actual.ventanilla_nombre;
                        elVentanilla.hidden = false;
                    } else {
                        elVentanilla.hidden = true;
                    }

                    botonAvanzar.disabled = false;

                    if (esNuevo) {
                        pulsar(panel);
                    }
                } else {
                    actualId = null;
                    elInfo.hidden = true;
                    elVacio.hidden = false;
                    botonAvanzar.disabled = true;
                    panel.style.removeProperty('--color-categoria');
                }
            }

            function renderizarProximos(proximos) {
                elProximosLista.innerHTML = '';
                elProximosTarjeta.hidden = !proximos || proximos.length === 0;

                (proximos || []).forEach(function (turno) {
                    var item = document.createElement('div');
                    item.className = 'turno-proximo-item';
                    item.style.setProperty('--color-categoria', turno.categoria_color);

                    var numero = document.createElement('span');
                    numero.className = 'turno-proximo-numero';
                    numero.textContent = turno.numero_turno;

                    var categoria = document.createElement('span');
                    categoria.className = 'turno-proximo-categoria';
                    categoria.textContent = etiquetaCategoria(turno);

                    var nombre = document.createElement('span');
                    nombre.className = 'turno-proximo-nombre';
                    nombre.textContent = turno.beneficiario_nombre;

                    item.appendChild(numero);
                    item.appendChild(categoria);
                    item.appendChild(nombre);
                    elProximosLista.appendChild(item);
                });
            }

            function renderizarHistorico(resumen) {
                elHistoricoCuerpo.innerHTML = '';

                if (!resumen || resumen.length === 0) {
                    var filaVacia = document.createElement('tr');
                    var celdaVacia = document.createElement('td');
                    celdaVacia.colSpan = 5;
                    celdaVacia.textContent = 'No hay categorías registradas.';
                    filaVacia.appendChild(celdaVacia);
                    elHistoricoCuerpo.appendChild(filaVacia);
                    return;
                }

                resumen.forEach(function (fila) {
                    var tr = document.createElement('tr');

                    var tdColor = document.createElement('td');
                    var muestra = document.createElement('span');
                    muestra.className = 'muestra-color';
                    muestra.style.background = fila.color;
                    tdColor.appendChild(muestra);

                    var tdNombre = document.createElement('td');
                    var etiqueta = document.createElement('span');
                    etiqueta.className = 'etiqueta-prefijo';
                    etiqueta.textContent = fila.prefijo;
                    tdNombre.appendChild(etiqueta);
                    tdNombre.appendChild(document.createTextNode(' ' + fila.nombre));

                    var tdAtendidos = document.createElement('td');
                    tdAtendidos.textContent = fila.atendidos_hoy;

                    var tdEspera = document.createElement('td');
                    tdEspera.textContent = fila.en_espera_hoy;

                    var tdTotal = document.createElement('td');
                    tdTotal.textContent = fila.total_hoy;

                    tr.appendChild(tdColor);
                    tr.appendChild(tdNombre);
                    tr.appendChild(tdAtendidos);
                    tr.appendChild(tdEspera);
                    tr.appendChild(tdTotal);
                    elHistoricoCuerpo.appendChild(tr);
                });
            }

            function actualizarSelectTransferir(turno) {
                selectDestino.innerHTML = '';

                categorias.forEach(function (categoria) {
                    if (turno && parseInt(categoria.id, 10) === parseInt(turno.categoria_id, 10)) {
                        return;
                    }
                    var opcion = document.createElement('option');
                    opcion.value = categoria.id;
                    opcion.textContent = categoria.nombre;
                    selectDestino.appendChild(opcion);
                });
            }

            function actualizarEstado(datos) {
                renderizarActual(datos.actual, datos.sinAlcanceAsignado);
                renderizarProximos(datos.proximos);
                renderizarHistorico(datos.resumen);

                // Se guarda el "siguiente" para que el botón sepa si hay a quién transferir, pero
                // el modal en sí NO se sincroniza aquí a propósito: si el sondeo cada 5s tocara su
                // turno_id mientras está abierto, se podría transferir un turno distinto al que la
                // persona ve en pantalla. Su turno objetivo se fija solo al abrirlo.
                ultimoSiguienteCompleto = datos.siguiente || null;
                botonAbrirTransferir.hidden = datos.sinAlcanceAsignado || !ultimoSiguienteCompleto;
            }

            function solicitarEstado() {
                fetch('index.php?ruta=turnos', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (respuesta) { return respuesta.json(); })
                    .then(function (datos) { actualizarEstado(datos); })
                    .catch(function () { /* se reintenta en el siguiente sondeo */ });
            }

            function enviarAccion(parametros, botonADesactivar) {
                if (botonADesactivar) {
                    botonADesactivar.disabled = true;
                }

                return fetch('index.php?ruta=turnos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: parametros.toString(),
                })
                    .then(function (respuesta) { return respuesta.json(); })
                    .then(function (datos) {
                        mostrarToast(datos.mensaje, datos.ok);
                        actualizarEstado(datos);
                        return datos;
                    })
                    .catch(function () {
                        mostrarToast('No se pudo conectar con el servidor. Intenta de nuevo.', false);
                    })
                    .finally(function () {
                        if (botonADesactivar) {
                            botonADesactivar.disabled = false;
                        }
                    });
            }

            botonAvanzar.addEventListener('click', function () {
                var parametros = new URLSearchParams();
                parametros.set('accion', 'avanzar');
                enviarAccion(parametros, botonAvanzar);
            });

            botonRetroceder.addEventListener('click', function () {
                var parametros = new URLSearchParams();
                parametros.set('accion', 'retroceder');
                enviarAccion(parametros, botonRetroceder);
            });

            botonAbrirTransferir.addEventListener('click', function () {
                if (!ultimoSiguienteCompleto) {
                    return;
                }

                // Se congela aquí el turno objetivo (id, número y categoría) con lo que se ve en
                // pantalla en este momento; si la cola cambia mientras el modal está abierto, el
                // servidor lo vuelve a validar al confirmar y rechaza la transferencia si ya no
                // coincide, en vez de transferir silenciosamente otro turno.
                campoTurnoId.value = ultimoSiguienteCompleto.id;
                etiquetaNumeroTransferir.textContent = ultimoSiguienteCompleto.numero_turno;
                actualizarSelectTransferir(ultimoSiguienteCompleto);
                modalTransferir.classList.add('abierto');
            });

            document.getElementById('boton-cerrar-modal-transferir').addEventListener('click', function () {
                modalTransferir.classList.remove('abierto');
            });

            modalTransferir.addEventListener('click', function (evento) {
                if (evento.target === modalTransferir) {
                    modalTransferir.classList.remove('abierto');
                }
            });

            document.addEventListener('keydown', function (evento) {
                if (evento.key === 'Escape') {
                    modalTransferir.classList.remove('abierto');
                }
            });

            document.getElementById('form-transferir').addEventListener('submit', function (evento) {
                evento.preventDefault();
                var boton = document.getElementById('boton-confirmar-transferir');
                var parametros = new URLSearchParams(new FormData(evento.target));

                enviarAccion(parametros, boton).then(function (datos) {
                    if (datos && datos.ok) {
                        modalTransferir.classList.remove('abierto');
                    }
                });
            });

            // Flechas del teclado como atajo: → Avanzar, ← Retroceder. Se ignoran si el foco está
            // en un campo de formulario (para no interferir con el select del modal) o si el modal
            // de transferir está abierto.
            document.addEventListener('keydown', function (evento) {
                var enCampoDeFormulario = ['INPUT', 'TEXTAREA', 'SELECT'].indexOf(document.activeElement.tagName) !== -1;

                if (enCampoDeFormulario || modalTransferir.classList.contains('abierto')) {
                    return;
                }

                if (evento.key === 'ArrowRight' && !elAcciones.hidden && !botonAvanzar.disabled) {
                    evento.preventDefault();
                    botonAvanzar.click();
                } else if (evento.key === 'ArrowLeft' && !elAcciones.hidden && !botonRetroceder.hidden && !botonRetroceder.disabled) {
                    evento.preventDefault();
                    botonRetroceder.click();
                }
            });

            // Estado inicial ya vino renderizado por PHP; solo sincronizamos las variables de JS
            // para que el primer sondeo no dispare la animación de "turno nuevo" sin motivo, y para
            // que "Pasar el siguiente a un compañero" tenga de dónde partir si se abre antes del
            // primer sondeo.
            var inicial = JSON.parse(document.getElementById('datos-turno-actual-inicial').textContent || 'null');
            actualId = inicial ? inicial.id : null;
            ultimoSiguienteCompleto = JSON.parse(document.getElementById('datos-turno-siguiente-inicial').textContent || 'null');

            setInterval(solicitarEstado, 5000);
        })();
    </script>

<?php require __DIR__ . '/../parciales/pie.php'; ?>
