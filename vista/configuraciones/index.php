<?php
$tituloPagina = 'Configuraciones';
$flechaModulo = '<svg class="tarjeta-modulo-flecha" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
require __DIR__ . '/../parciales/encabezado.php';
?>

    <div class="grid-modulos">
        <section class="grupo-configuraciones">
            <h2 class="grupo-configuraciones-titulo grupo-usuarios">Usuarios y responsabilidades</h2>
            <div class="box-items-config">
                <a href="index.php?ruta=usuarios" class="tarjeta-modulo">
                    <div class="tarjeta-modulo-cabecera">
                        <h2>Usuarios</h2>
                        <?= $flechaModulo ?>
                    </div>
                    <p class="texto-atenuado">Gestiona las cuentas que pueden iniciar sesión en el sistema.</p>
                </a>
                <a href="index.php?ruta=beneficiarios" class="tarjeta-modulo">
                    <div class="tarjeta-modulo-cabecera">
                        <h2>Beneficiarios</h2>
                        <?= $flechaModulo ?>
                    </div>
                    <p class="texto-atenuado">Gestiona el registro de beneficiarios (documento, nombres y apellidos).</p>
                </a>
            </div>
        </section>

        <section class="grupo-configuraciones">
            <h2 class="grupo-configuraciones-titulo grupo-turnero">Turnero</h2>
            <div class="box-items-config">
                <a href="index.php?ruta=categorias-turnero" class="tarjeta-modulo">
                    <div class="tarjeta-modulo-cabecera">
                        <h2>Categorías turnero</h2>
                        <?= $flechaModulo ?>
                    </div>
                    <p class="texto-atenuado">Gestiona las categorías que generan los turnos (nombre, prefijo y color).</p>
                </a>
                <a href="index.php?ruta=ventanillas" class="tarjeta-modulo">
                    <div class="tarjeta-modulo-cabecera">
                        <h2>Ventanillas</h2>
                        <?= $flechaModulo ?>
                    </div>
                    <p class="texto-atenuado">Gestiona los puntos de atención donde se llaman los turnos.</p>
                </a>
            </div>
        </section>
    </div>

<?php require __DIR__ . '/../parciales/pie.php'; ?>
