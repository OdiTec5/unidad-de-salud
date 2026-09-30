<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Consultas de reporte sobre turnos ya cerrados (o en curso) de un día puntual. Separado de Turno
 * porque ese modelo resuelve la mecánica de la fila en vivo (CURDATE(), avanzar/retroceder); este
 * solo lee y resume, siempre para una fecha explícita.
 */
class ReporteAtencion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function obtenerResumenGeneral(string $fecha): array
    {
        $consulta = $this->db->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN estado = 'atendido' THEN 1 ELSE 0 END) AS atendidos,
                SUM(CASE WHEN estado = 'espera' THEN 1 ELSE 0 END) AS en_espera,
                AVG(CASE WHEN estado = 'atendido' THEN TIMESTAMPDIFF(MINUTE, creado_en, atendido_en) END) AS tiempo_promedio_minutos
             FROM turnos
             WHERE DATE(creado_en) = :fecha"
        );
        $consulta->execute(['fecha' => $fecha]);
        $fila = $consulta->fetch();

        return [
            'total' => (int) ($fila['total'] ?? 0),
            'atendidos' => (int) ($fila['atendidos'] ?? 0),
            'en_espera' => (int) ($fila['en_espera'] ?? 0),
            'tiempo_promedio_minutos' => $fila['tiempo_promedio_minutos'] !== null ? round((float) $fila['tiempo_promedio_minutos'], 1) : null,
        ];
    }

    public function obtenerResumenPorCategoria(string $fecha): array
    {
        $consulta = $this->db->prepare(
            "SELECT
                c.id, c.nombre, c.prefijo, c.color,
                SUM(CASE WHEN t.estado = 'atendido' AND DATE(t.creado_en) = :fecha1 THEN 1 ELSE 0 END) AS atendidos,
                SUM(CASE WHEN t.estado = 'espera' AND DATE(t.creado_en) = :fecha2 THEN 1 ELSE 0 END) AS en_espera,
                SUM(CASE WHEN DATE(t.creado_en) = :fecha3 THEN 1 ELSE 0 END) AS total,
                AVG(CASE WHEN t.estado = 'atendido' AND DATE(t.creado_en) = :fecha4 THEN TIMESTAMPDIFF(MINUTE, t.creado_en, t.atendido_en) END) AS tiempo_promedio_minutos
             FROM categorias_turnero c
             LEFT JOIN turnos t ON t.categoria_id = c.id
             GROUP BY c.id, c.nombre, c.prefijo, c.color
             ORDER BY c.nombre"
        );
        $consulta->execute(['fecha1' => $fecha, 'fecha2' => $fecha, 'fecha3' => $fecha, 'fecha4' => $fecha]);

        return array_map(static function (array $fila): array {
            $fila['tiempo_promedio_minutos'] = $fila['tiempo_promedio_minutos'] !== null
                ? round((float) $fila['tiempo_promedio_minutos'], 1)
                : null;

            return $fila;
        }, $consulta->fetchAll());
    }

    public function obtenerResumenPorVentanilla(string $fecha): array
    {
        $consulta = $this->db->prepare(
            "SELECT v.id, v.nombre,
                    COUNT(t.id) AS atendidos,
                    AVG(TIMESTAMPDIFF(MINUTE, t.creado_en, t.atendido_en)) AS tiempo_promedio_minutos
             FROM ventanillas v
             JOIN categorias_turnero c ON c.ventanilla_id = v.id
             JOIN turnos t ON t.categoria_id = c.id AND t.estado = 'atendido' AND DATE(t.creado_en) = :fecha
             GROUP BY v.id, v.nombre
             ORDER BY v.nombre"
        );
        $consulta->execute(['fecha' => $fecha]);

        return array_map(static function (array $fila): array {
            $fila['tiempo_promedio_minutos'] = $fila['tiempo_promedio_minutos'] !== null
                ? round((float) $fila['tiempo_promedio_minutos'], 1)
                : null;

            return $fila;
        }, $consulta->fetchAll());
    }

    public function obtenerDetalleAtendidos(string $fecha): array
    {
        $consulta = $this->db->prepare(
            "SELECT t.numero_turno, c.nombre AS categoria_nombre, s.nombre AS subcategoria_nombre,
                    v.nombre AS ventanilla_nombre,
                    CONCAT_WS(' ', b.primer_nombre, b.segundo_nombre, b.primer_apellido, b.segundo_apellido) AS beneficiario_nombre,
                    t.creado_en, t.atendido_en,
                    TIMESTAMPDIFF(MINUTE, t.creado_en, t.atendido_en) AS espera_minutos
             FROM turnos t
             JOIN categorias_turnero c ON c.id = t.categoria_id
             JOIN beneficiarios b ON b.id = t.beneficiario_id
             LEFT JOIN subcategorias_turnero s ON s.id = t.subcategoria_id
             LEFT JOIN ventanillas v ON v.id = c.ventanilla_id
             WHERE t.estado = 'atendido' AND DATE(t.creado_en) = :fecha
             ORDER BY t.atendido_en DESC"
        );
        $consulta->execute(['fecha' => $fecha]);

        return $consulta->fetchAll();
    }
}
