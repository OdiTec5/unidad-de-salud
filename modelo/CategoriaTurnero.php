<?php

require_once __DIR__ . '/../config/database.php';

class CategoriaTurnero
{
    /** Lunes = 1 ... domingo = 7, como devuelve DateTime::format('N'). */
    public const DIAS_SEMANA = [
        1 => 'Lun',
        2 => 'Mar',
        3 => 'Mié',
        4 => 'Jue',
        5 => 'Vie',
        6 => 'Sáb',
        7 => 'Dom',
    ];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function obtenerTodos(): array
    {
        $consulta = $this->db->query(
            "SELECT c.id, c.nombre, c.prefijo, c.color, c.ventanilla_id, c.hora_inicio, c.hora_fin, c.dias_semana, c.creado_en, v.nombre AS ventanilla_nombre
             FROM categorias_turnero c
             LEFT JOIN ventanillas v ON v.id = c.ventanilla_id
             ORDER BY c.nombre"
        );

        return $consulta->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $consulta = $this->db->prepare(
            "SELECT c.id, c.nombre, c.prefijo, c.color, c.ventanilla_id, c.hora_inicio, c.hora_fin, c.dias_semana, c.creado_en, v.nombre AS ventanilla_nombre
             FROM categorias_turnero c
             LEFT JOIN ventanillas v ON v.id = c.ventanilla_id
             WHERE c.id = :id"
        );
        $consulta->execute(['id' => $id]);
        $fila = $consulta->fetch();

        return $fila !== false ? $fila : null;
    }

    public function existeNombre(string $nombre, ?int $ignorarId = null): bool
    {
        $consulta = $ignorarId !== null
            ? $this->db->prepare('SELECT id FROM categorias_turnero WHERE nombre = :nombre AND id != :id LIMIT 1')
            : $this->db->prepare('SELECT id FROM categorias_turnero WHERE nombre = :nombre LIMIT 1');

        $parametros = ['nombre' => $nombre];
        if ($ignorarId !== null) {
            $parametros['id'] = $ignorarId;
        }

        $consulta->execute($parametros);

        return $consulta->fetch() !== false;
    }

    public function existePrefijo(string $prefijo, ?int $ignorarId = null): bool
    {
        $consulta = $ignorarId !== null
            ? $this->db->prepare('SELECT id FROM categorias_turnero WHERE prefijo = :prefijo AND id != :id LIMIT 1')
            : $this->db->prepare('SELECT id FROM categorias_turnero WHERE prefijo = :prefijo LIMIT 1');

        $parametros = ['prefijo' => $prefijo];
        if ($ignorarId !== null) {
            $parametros['id'] = $ignorarId;
        }

        $consulta->execute($parametros);

        return $consulta->fetch() !== false;
    }

    public function crear(
        string $nombre,
        string $prefijo,
        string $color,
        ?int $ventanillaId,
        ?string $horaInicio,
        ?string $horaFin,
        ?string $diasSemana
    ): bool {
        $consulta = $this->db->prepare(
            'INSERT INTO categorias_turnero (nombre, prefijo, color, ventanilla_id, hora_inicio, hora_fin, dias_semana)
             VALUES (:nombre, :prefijo, :color, :ventanilla_id, :hora_inicio, :hora_fin, :dias_semana)'
        );

        return $consulta->execute([
            'nombre' => $nombre,
            'prefijo' => $prefijo,
            'color' => $color,
            'ventanilla_id' => $ventanillaId,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'dias_semana' => $diasSemana,
        ]);
    }

    public function actualizar(
        int $id,
        string $nombre,
        string $prefijo,
        string $color,
        ?int $ventanillaId,
        ?string $horaInicio,
        ?string $horaFin,
        ?string $diasSemana
    ): bool {
        $consulta = $this->db->prepare(
            'UPDATE categorias_turnero
             SET nombre = :nombre, prefijo = :prefijo, color = :color, ventanilla_id = :ventanilla_id,
                 hora_inicio = :hora_inicio, hora_fin = :hora_fin, dias_semana = :dias_semana
             WHERE id = :id'
        );

        return $consulta->execute([
            'id' => $id,
            'nombre' => $nombre,
            'prefijo' => $prefijo,
            'color' => $color,
            'ventanilla_id' => $ventanillaId,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'dias_semana' => $diasSemana,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $consulta = $this->db->prepare('DELETE FROM categorias_turnero WHERE id = :id');

        return $consulta->execute(['id' => $id]);
    }

    /**
     * La hora y el día (formato ISO: 1=lunes...7=domingo) "ahora", según el reloj de MySQL — no el
     * de PHP. Todo el resto de la app (numeración de turnos, CURDATE() en las colas, reportes) ya
     * confía en el reloj de la base de datos; si usáramos new DateTime() aquí, un date.timezone de
     * PHP mal configurado (ej. Europe/Berlin en vez de la zona real del servidor) rompería la
     * comparación de horario sin que nada más en la app se diera cuenta.
     */
    public function obtenerMomentoActual(): array
    {
        $fila = $this->db->query('SELECT CURTIME() AS hora, DAYOFWEEK(CURDATE()) AS dia_mysql')->fetch();

        // DAYOFWEEK de MySQL: 1=domingo...7=sábado. Lo pasamos a ISO (1=lunes...7=domingo).
        $diaMysql = (int) $fila['dia_mysql'];
        $diaIso = $diaMysql === 1 ? 7 : $diaMysql - 1;

        return ['hora' => $fila['hora'], 'diaIso' => $diaIso];
    }

    /**
     * Si la categoría está dentro de su horario de atención en el momento dado. Sin
     * hora_inicio/hora_fin ni dias_semana configurados, se considera siempre disponible
     * (comportamiento por defecto, igual que antes de que existiera esta restricción).
     */
    public static function estaEnHorario(array $categoria, string $horaActual, int $diaIsoActual): bool
    {
        if (!empty($categoria['dias_semana'])) {
            $diasPermitidos = array_map('intval', explode(',', (string) $categoria['dias_semana']));

            if (!in_array($diaIsoActual, $diasPermitidos, true)) {
                return false;
            }
        }

        if (!empty($categoria['hora_inicio']) && !empty($categoria['hora_fin'])) {
            return $horaActual >= $categoria['hora_inicio'] && $horaActual <= $categoria['hora_fin'];
        }

        return true;
    }

    /**
     * Texto legible del horario configurado (ej. "08:00 - 17:00 · Lun-Vie"), o null si la
     * categoría no tiene restricción de horario.
     */
    public static function describirHorario(array $categoria): ?string
    {
        $tieneHoras = !empty($categoria['hora_inicio']) && !empty($categoria['hora_fin']);
        $tieneDias = !empty($categoria['dias_semana']);

        if (!$tieneHoras && !$tieneDias) {
            return null;
        }

        $partes = [];

        if ($tieneHoras) {
            $partes[] = substr($categoria['hora_inicio'], 0, 5) . ' - ' . substr($categoria['hora_fin'], 0, 5);
        }

        if ($tieneDias) {
            $dias = array_map('intval', explode(',', (string) $categoria['dias_semana']));
            $nombresDias = array_map(static fn (int $dia): string => self::DIAS_SEMANA[$dia] ?? '', $dias);
            $partes[] = implode(', ', array_filter($nombresDias));
        }

        return implode(' · ', $partes);
    }
}
