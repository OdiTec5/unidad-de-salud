<?php
$rutaActual = $_GET['ruta'] ?? 'dashboard';
?>
<aside class="barra-lateral">
    <a href="index.php?ruta=dashboard" class="marca">Unidad de Salud</a>
    <nav class="menu-lateral">
        <p class="grupo-menu">Resumen</p>
        <a href="index.php?ruta=dashboard" class="<?= $rutaActual === 'dashboard' ? 'activo' : '' ?>">Inicio</a>

        <p class="grupo-menu">Administración</p>
        <a href="index.php?ruta=turnos" class="<?= $rutaActual === 'turnos' ? 'activo' : '' ?>">Turnos</a>
        <a href="index.php?ruta=configuraciones" class="<?= in_array($rutaActual, ['configuraciones', 'usuarios', 'categorias-turnero', 'beneficiarios', 'ventanillas'], true) ? 'activo' : '' ?>">Configuraciones</a>
    </nav>
</aside>
