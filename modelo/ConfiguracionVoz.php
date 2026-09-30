<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Configuración única (una sola fila, id=1) de la voz que usa la pantalla pública de turnos para
 * anunciar cada turno. El nombre de voz guardado es el que expone el navegador vía
 * speechSynthesis.getVoices() en el equipo donde se configuró; si esa voz no existe en el
 * navegador donde se muestra la pantalla, se usa el idioma como respaldo.
 */
class ConfiguracionVoz
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function obtener(): array
    {
        $consulta = $this->db->query('SELECT voz_nombre, idioma, velocidad, tono, volumen FROM configuracion_voz WHERE id = 1');
        $fila = $consulta->fetch();

        if ($fila === false) {
            return ['voz_nombre' => null, 'idioma' => 'es-ES', 'velocidad' => 0.95, 'tono' => 1.0, 'volumen' => 1.0];
        }

        return $fila;
    }

    public function actualizar(?string $vozNombre, string $idioma, float $velocidad, float $tono, float $volumen): bool
    {
        $consulta = $this->db->prepare(
            'INSERT INTO configuracion_voz (id, voz_nombre, idioma, velocidad, tono, volumen)
             VALUES (1, :voz_nombre, :idioma, :velocidad, :tono, :volumen)
             ON DUPLICATE KEY UPDATE
                voz_nombre = VALUES(voz_nombre),
                idioma = VALUES(idioma),
                velocidad = VALUES(velocidad),
                tono = VALUES(tono),
                volumen = VALUES(volumen)'
        );

        return $consulta->execute([
            'voz_nombre' => $vozNombre,
            'idioma' => $idioma,
            'velocidad' => $velocidad,
            'tono' => $tono,
            'volumen' => $volumen,
        ]);
    }
}
