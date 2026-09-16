<?php require __DIR__ . '/../parciales/encabezado.php'; ?>

<div class="tarjeta">
    <h2>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? '') ?></h2>
    <p class="texto-atenuado">Iniciaste sesión correctamente. Este es el punto de partida del sistema.</p>
</div>

<div class="tarjeta" style="margin-top: 1.5rem;">
    <h2>Turnero</h2>
    <p class="texto-atenuado">Páginas públicas, sin necesidad de iniciar sesión.</p>
    <div class="box-items-config" style="margin-top: 1rem;">
        <a href="index.php?ruta=turno-solicitar" target="_blank" rel="noopener" class="tarjeta-modulo">
            <div class="tarjeta-modulo-cabecera">
                <h2>Solicitar turno</h2>
                <svg class="tarjeta-modulo-flecha" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
            <p class="texto-atenuado">Kiosco para que los beneficiarios pidan su turno.</p>
        </a>
        <a href="index.php?ruta=turno-pantalla" target="_blank" rel="noopener" class="tarjeta-modulo">
            <div class="tarjeta-modulo-cabecera">
                <h2>Pantalla de turnos</h2>
                <svg class="tarjeta-modulo-flecha" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
            <p class="texto-atenuado">Pantalla para sala de espera: turno actual y próximos.</p>
        </a>
    </div>
</div>

<?php require __DIR__ . '/../parciales/pie.php'; ?>
