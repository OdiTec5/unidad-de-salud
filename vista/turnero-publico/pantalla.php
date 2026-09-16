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
        <div class="pantalla-reloj" id="pantalla-reloj">--:--:--</div>
    </header>

    <main class="pantalla-cuerpo">
        <section class="pantalla-actual" id="pantalla-actual">
            <span class="pantalla-actual-etiqueta">Turno actual</span>
            <span class="pantalla-actual-numero" id="pantalla-actual-numero">—</span>
            <span class="pantalla-actual-categoria" id="pantalla-actual-categoria">Sin turnos en espera</span>
            <span class="pantalla-actual-ventanilla" id="pantalla-actual-ventanilla" hidden></span>
            <span class="pantalla-actual-nombre" id="pantalla-actual-nombre"></span>
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

        function actualizarReloj() {
            var elemento = document.getElementById('pantalla-reloj');
            var ahora = new Date();
            var horas = String(ahora.getHours()).padStart(2, '0');
            var minutos = String(ahora.getMinutes()).padStart(2, '0');
            var segundos = String(ahora.getSeconds()).padStart(2, '0');
            elemento.textContent = horas + ':' + minutos + ':' + segundos;
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
                    var numeroActual = document.getElementById('pantalla-actual-numero');
                    var categoriaActual = document.getElementById('pantalla-actual-categoria');
                    var ventanillaActual = document.getElementById('pantalla-actual-ventanilla');
                    var nombreActual = document.getElementById('pantalla-actual-nombre');
                    var seccionActual = document.getElementById('pantalla-actual');
                    var lista = document.getElementById('pantalla-proximos-lista');

                    if (datos.actual) {
                        numeroActual.textContent = datos.actual.numero_turno;
                        categoriaActual.textContent = etiquetaCategoria(datos.actual);
                        nombreActual.textContent = datos.actual.beneficiario_nombre;
                        seccionActual.style.setProperty('--color-categoria', datos.actual.categoria_color);

                        if (datos.actual.ventanilla_nombre) {
                            ventanillaActual.textContent = 'Diríjase a ' + datos.actual.ventanilla_nombre;
                            ventanillaActual.hidden = false;
                        } else {
                            ventanillaActual.textContent = '';
                            ventanillaActual.hidden = true;
                        }
                    } else {
                        numeroActual.textContent = '—';
                        categoriaActual.textContent = 'Sin turnos en espera';
                        nombreActual.textContent = '';
                        ventanillaActual.textContent = '';
                        ventanillaActual.hidden = true;
                        seccionActual.style.removeProperty('--color-categoria');
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
