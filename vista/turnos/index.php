<?php
$tituloPagina = 'Turnos';
$etiquetaCategoria = static function (array $turno): string {
    return $turno['categoria_nombre'] . (!empty($turno['subcategoria_nombre']) ? ' · ' . $turno['subcategoria_nombre'] : '');
};
require __DIR__ . '/../parciales/encabezado.php';
?>

    <div class="tarjeta turno-actual-panel" style="--color-categoria: <?= htmlspecialchars($actual['categoria_color'] ?? '#0071e3') ?>;">
        <div class="cabecera-modulo">
            <h1>Turno actual</h1>
        </div>

        <?php if ($sinAlcanceAsignado): ?>
        <p class="mensaje-error">No tienes categorías ni subcategorías asignadas. Pídele a un administrador que te asigne alguna en Usuarios.</p>
        <?php elseif ($actual !== null): ?>
        <div class="turno-actual-info">
            <span class="turno-actual-numero"><?= htmlspecialchars($actual['numero_turno']) ?></span>
            <span class="turno-actual-categoria"><?= htmlspecialchars($etiquetaCategoria($actual)) ?></span>
            <?php if (!empty($actual['ventanilla_nombre'])): ?>
            <span class="turno-actual-ventanilla">Diríjase a <?= htmlspecialchars($actual['ventanilla_nombre']) ?></span>
            <?php endif; ?>
            <span class="turno-actual-nombre"><?= htmlspecialchars($actual['beneficiario_nombre']) ?></span>
        </div>
        <?php else: ?>
        <p class="texto-atenuado">No hay turnos en espera.</p>
        <?php endif; ?>

        <?php if (!$sinAlcanceAsignado): ?>
        <div class="turno-acciones">
            <?php if ($esAdministrador): ?>
            <form method="POST" action="index.php?ruta=turnos">
                <input type="hidden" name="accion" value="retroceder">
                <button type="submit" class="boton-turno boton-turno-retroceder">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Retroceder
                </button>
            </form>
            <?php endif; ?>
            <form method="POST" action="index.php?ruta=turnos">
                <input type="hidden" name="accion" value="avanzar">
                <button type="submit" class="boton-turno boton-turno-avanzar" <?= $actual === null ? 'disabled' : '' ?>>
                    Avanzar
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($proximos)): ?>
    <div class="tarjeta" style="margin-top: 1.5rem;">
        <h2>Próximos en espera</h2>
        <div class="turno-proximos-lista">
            <?php foreach ($proximos as $proximo): ?>
            <div class="turno-proximo-item" style="--color-categoria: <?= htmlspecialchars($proximo['categoria_color']) ?>;">
                <span class="turno-proximo-numero"><?= htmlspecialchars($proximo['numero_turno']) ?></span>
                <span class="turno-proximo-categoria"><?= htmlspecialchars($etiquetaCategoria($proximo)) ?></span>
                <span class="turno-proximo-nombre"><?= htmlspecialchars($proximo['beneficiario_nombre']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

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
                <tbody>
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

<?php require __DIR__ . '/../parciales/pie.php'; ?>
