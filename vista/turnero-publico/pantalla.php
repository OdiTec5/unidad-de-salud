<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Turnero · Unidad de Salud</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body class="pagina-turnero-pantalla">
    <header class="pantalla-header">
        <div class="pantalla-marca">
            <span class="pantalla-marca-icono" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            </span>
            Unidad de Salud
        </div>
        <div class="pantalla-header-derecha">
            <button type="button" id="pantalla-boton-sonido" class="pantalla-boton-sonido">🔊 Voz activada</button>
            <div class="pantalla-reloj" id="pantalla-reloj">--:--:--</div>
        </div>
    </header>

    <main class="pantalla-cuerpo">
        <section class="pantalla-atenciones-seccion">
            <h2 class="pantalla-proximos-titulo">Ahora atendiendo</h2>
            <div class="pantalla-atenciones" id="pantalla-atenciones">
                <p class="pantalla-proximos-vacio">Todavía no se ha llamado ningún turno hoy.</p>
            </div>
        </section>

        <section class="pantalla-proximos">
            <h2 class="pantalla-proximos-titulo">Próximos turnos</h2>
            <div class="pantalla-proximos-lista" id="pantalla-proximos-lista">
                <p class="pantalla-proximos-vacio">No hay más turnos en espera.</p>
            </div>
        </section>
    </main>

    <script>
        function etiquetaCategoria(turno) {
            return turno.subcategoria_nombre
                ? turno.categoria_nombre + ' · ' + turno.subcategoria_nombre
                : turno.categoria_nombre;
        }

        var vozActivada = true;
        var vozDesbloqueada = false;
        var ultimosAnunciadosPorVentanilla = {};
        var configVoz = <?= json_encode($configuracionVoz) ?>;

        function frasePorTurno(turno) {
            var partes = String(turno.numero_turno || '').split('-');
            var letra = partes[0] || '';
            var numero = partes.length > 1 ? parseInt(partes[1], 10) : null;
            var numeroHablado = letra + (numero !== null && !isNaN(numero) ? ', número ' + numero : '');

            var frase = 'Turno ' + numeroHablado + '. ' + (turno.beneficiario_nombre || '') + '.';

            if (turno.ventanilla_nombre) {
                frase += ' Por favor, diríjase a ' + turno.ventanilla_nombre + '.';
            } else {
                frase += ' Por favor, acérquese al módulo de atención.';
            }

            return frase;
        }

        function anunciarTurno(turno) {
            if (!vozActivada || !('speechSynthesis' in window) || !turno) {
                return;
            }

            var utterance = new SpeechSynthesisUtterance(frasePorTurno(turno));
            utterance.lang = configVoz.idioma || 'es-ES';
            utterance.rate = parseFloat(configVoz.velocidad) || 0.95;
            utterance.pitch = parseFloat(configVoz.tono) || 1;
            utterance.volume = parseFloat(configVoz.volumen) || 1;

            if (configVoz.voz_nombre) {
                var vozElegida = window.speechSynthesis.getVoices().find(function (v) {
                    return v.name === configVoz.voz_nombre;
                });
                if (vozElegida) {
                    utterance.voice = vozElegida;
                }
            }

            // No se cancela lo que esté sonando: si dos ventanillas llaman casi al mismo tiempo,
            // ambos anuncios deben escucharse en fila, no que el segundo corte al primero.
            window.speechSynthesis.speak(utterance);
        }

        function desbloquearVoz() {
            if (vozDesbloqueada || !('speechSynthesis' in window)) {
                return;
            }

            vozDesbloqueada = true;

            // Utterance casi silenciosa: en navegadores que exigen un gesto del usuario antes de
            // permitir audio, esto "destraba" la voz para que los anuncios automáticos posteriores
            // sí suenen, sin que el operador tenga que activar nada a propósito.
            var arranque = new SpeechSynthesisUtterance(' ');
            arranque.volume = 0;
            window.speechSynthesis.speak(arranque);
        }

        document.addEventListener('DOMContentLoaded', function () {
            var botonSonido = document.getElementById('pantalla-boton-sonido');

            desbloquearVoz();

            ['click', 'touchstart', 'keydown'].forEach(function (evento) {
                document.addEventListener(evento, desbloquearVoz, { once: true });
            });

            if (!botonSonido) {
                return;
            }

            botonSonido.addEventListener('click', function () {
                vozActivada = !vozActivada;
                botonSonido.textContent = vozActivada ? '🔊 Voz activada' : '🔇 Voz silenciada';

                if (vozActivada && 'speechSynthesis' in window) {
                    var prueba = new SpeechSynthesisUtterance('Anuncios de turnos activados.');
                    prueba.lang = configVoz.idioma || 'es-ES';
                    prueba.rate = parseFloat(configVoz.velocidad) || 0.95;
                    prueba.pitch = parseFloat(configVoz.tono) || 1;
                    prueba.volume = parseFloat(configVoz.volumen) || 1;
                    window.speechSynthesis.speak(prueba);
                } else if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                }
            });
        });

        function actualizarReloj() {
            var elemento = document.getElementById('pantalla-reloj');
            var ahora = new Date();
            var horas = String(ahora.getHours()).padStart(2, '0');
            var minutos = String(ahora.getMinutes()).padStart(2, '0');
            var segundos = String(ahora.getSeconds()).padStart(2, '0');
            elemento.textContent = horas + ':' + minutos + ':' + segundos;
        }

        function crearTarjetaAtencion(atencion) {
            var tarjeta = document.createElement('div');
            tarjeta.className = 'pantalla-atencion-tarjeta';
            tarjeta.style.setProperty('--color-categoria', atencion.categoria_color);

            var ventanilla = document.createElement('span');
            ventanilla.className = 'pantalla-atencion-ventanilla';
            ventanilla.textContent = atencion.ventanilla_nombre;

            var numero = document.createElement('span');
            numero.className = 'pantalla-atencion-numero';
            numero.textContent = atencion.numero_turno;

            var categoria = document.createElement('span');
            categoria.className = 'pantalla-atencion-categoria';
            categoria.textContent = etiquetaCategoria(atencion);

            var nombre = document.createElement('span');
            nombre.className = 'pantalla-atencion-nombre';
            nombre.textContent = atencion.beneficiario_nombre;

            tarjeta.appendChild(ventanilla);
            tarjeta.appendChild(numero);
            tarjeta.appendChild(categoria);
            tarjeta.appendChild(nombre);

            return tarjeta;
        }

        function crearTarjetaProximo(turno) {
            var tarjeta = document.createElement('div');
            tarjeta.className = 'pantalla-proximo-tarjeta';
            tarjeta.style.setProperty('--color-categoria', turno.categoria_color);

            var numero = document.createElement('span');
            numero.className = 'pantalla-proximo-numero';
            numero.textContent = turno.numero_turno;

            var categoria = document.createElement('span');
            categoria.className = 'pantalla-proximo-categoria';
            categoria.textContent = etiquetaCategoria(turno);

            var nombre = document.createElement('span');
            nombre.className = 'pantalla-proximo-nombre';
            nombre.textContent = turno.beneficiario_nombre;

            tarjeta.appendChild(numero);
            tarjeta.appendChild(categoria);
            tarjeta.appendChild(nombre);

            return tarjeta;
        }

        function actualizarPantalla() {
            fetch('index.php?ruta=turno-pantalla-datos')
                .then(function (respuesta) { return respuesta.json(); })
                .then(function (datos) {
                    var contenedorAtenciones = document.getElementById('pantalla-atenciones');
                    var lista = document.getElementById('pantalla-proximos-lista');

                    contenedorAtenciones.innerHTML = '';

                    if (datos.atenciones && datos.atenciones.length > 0) {
                        datos.atenciones.forEach(function (atencion) {
                            contenedorAtenciones.appendChild(crearTarjetaAtencion(atencion));

                            var ventanillaId = atencion.ventanilla_id;
                            if (ultimosAnunciadosPorVentanilla[ventanillaId] !== atencion.id) {
                                ultimosAnunciadosPorVentanilla[ventanillaId] = atencion.id;
                                anunciarTurno(atencion);
                            }
                        });
                    } else {
                        var vacioAtencion = document.createElement('p');
                        vacioAtencion.className = 'pantalla-proximos-vacio';
                        vacioAtencion.textContent = 'Todavía no se ha llamado ningún turno hoy.';
                        contenedorAtenciones.appendChild(vacioAtencion);
                    }

                    lista.innerHTML = '';

                    if (datos.proximos && datos.proximos.length > 0) {
                        datos.proximos.forEach(function (turno) {
                            lista.appendChild(crearTarjetaProximo(turno));
                        });
                    } else {
                        var vacio = document.createElement('p');
                        vacio.className = 'pantalla-proximos-vacio';
                        vacio.textContent = 'No hay más turnos en espera.';
                        lista.appendChild(vacio);
                    }
                })
                .catch(function () {
                    /* Se reintenta en el siguiente ciclo de sondeo; no se interrumpe la pantalla. */
                });
        }

        actualizarReloj();
        actualizarPantalla();
        setInterval(actualizarReloj, 1000);
        setInterval(actualizarPantalla, 4000);
    </script>
</body>
</html>
