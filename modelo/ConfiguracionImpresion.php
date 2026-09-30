<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Configuración única (una sola fila, id=1) del recibo de turno que se imprime en la impresora de
 * calor: ancho del papel del rollo y si se imprime automáticamente al generar el turno o solo con
 * el botón manual.
 */
class ConfiguracionImpresion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function obtener(): array
    {
        $consulta = $this->db->query('SELECT ancho_papel, auto_imprimir FROM configuracion_impresion WHERE id = 1');
        $fila = $consulta->fetch();

        if ($fila === false) {
            return ['ancho_papel' => '80mm', 'auto_imprimir' => true];
        }

        $fila['auto_imprimir'] = (int) $fila['auto_imprimir'] === 1;

        return $fila;
    }

    public function actualizar(string $anchoPapel, bool $autoImprimir): bool
    {
        $anchoPapel = in_array($anchoPapel, ['58mm', '80mm'], true) ? $anchoPapel : '80mm';

        $consulta = $this->db->prepare(
            'INSERT INTO configuracion_impresion (id, ancho_papel, auto_imprimir)
             VALUES (1, :ancho_papel, :auto_imprimir)
             ON DUPLICATE KEY UPDATE ancho_papel = VALUES(ancho_papel), auto_imprimir = VALUES(auto_imprimir)'
        );

        return $consulta->execute([
            'ancho_papel' => $anchoPapel,
            'auto_imprimir' => $autoImprimir ? 1 : 0,
        ]);
    }
}
