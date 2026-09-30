<?php
$tituloPagina = $tituloPagina ?? 'Unidad de Salud';
$nombreActual = $_SESSION['usuario_nombre'] ?? '';
$correoActual = $_SESSION['usuario_correo'] ?? '';
$inicialAvatar = $nombreActual !== '' ? mb_strtoupper(mb_substr($nombreActual, 0, 1)) : '?';
$versionCss = @filemtime(__DIR__ . '/../../assets/css/estilo.css') ?: time();
$versionJs = @filemtime(__DIR__ . '/../../assets/js/app.js') ?: time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($tituloPagina) ?> · Unidad de Salud</title>
    <link rel="stylesheet" href="assets/css/estilo.css?v=<?= $versionCss ?>">
</head>
<body>
    <div class="layout">
        <?php require __DIR__ . '/sidebar.php'; ?>
        <div class="barra-lateral-fondo" id="barra-lateral-fondo"></div>

        <div class="contenido-principal">
            <header class="encabezado">
                <div class="titulo-pagina-grupo">
                    <button type="button" id="boton-hamburguesa" class="boton-hamburguesa" aria-label="Mostrar u ocultar menú">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <h1 class="titulo-pagina"><?= htmlspecialchars($tituloPagina) ?></h1>
                </div>

                <div class="grupo-acciones-encabezado">
                    <div class="menu-usuario">
                        <button type="button" id="menu-usuario-boton" class="menu-usuario-boton">
                            <span class="avatar-usuario"><?= htmlspecialchars($inicialAvatar) ?></span>
                            <span class="nombre-usuario"><?= htmlspecialchars($nombreActual) ?></span>
                            <span class="flecha-menu">▾</span>
                        </button>
                        <div id="menu-usuario-dropdown" class="menu-usuario-dropdown">
                            <div class="menu-usuario-detalle">
                                <span class="detalle-usuario"><?= htmlspecialchars($correoActual) ?></span>
                            </div>
                            <a href="index.php?ruta=logout">Cerrar sesión</a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="area-contenido">
