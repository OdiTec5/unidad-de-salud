<?php

require_once __DIR__ . '/../config/database.php';

class CategoriaTurnero
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function obtenerTodos(): array
    {
        $consulta = $this->db->query(
            "SELECT c.id, c.nombre, c.prefijo, c.color, c.ventanilla_id, c.creado_en, v.nombre AS ventanilla_nombre
             FROM categorias_turnero c
             LEFT JOIN ventanillas v ON v.id = c.ventanilla_id
             ORDER BY c.nombre"
        );

        return $consulta->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $consulta = $this->db->prepare(
            "SELECT c.id, c.nombre, c.prefijo, c.color, c.ventanilla_id, c.creado_en, v.nombre AS ventanilla_nombre
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

    public function crear(string $nombre, string $prefijo, string $color, ?int $ventanillaId): bool
    {
        $consulta = $this->db->prepare(
            'INSERT INTO categorias_turnero (nombre, prefijo, color, ventanilla_id) VALUES (:nombre, :prefijo, :color, :ventanilla_id)'
        );

        return $consulta->execute([
            'nombre' => $nombre,
            'prefijo' => $prefijo,
            'color' => $color,
            'ventanilla_id' => $ventanillaId,
        ]);
    }

    public function actualizar(int $id, string $nombre, string $prefijo, string $color, ?int $ventanillaId): bool
    {
        $consulta = $this->db->prepare(
            'UPDATE categorias_turnero SET nombre = :nombre, prefijo = :prefijo, color = :color, ventanilla_id = :ventanilla_id WHERE id = :id'
        );

        return $consulta->execute([
            'id' => $id,
            'nombre' => $nombre,
            'prefijo' => $prefijo,
            'color' => $color,
            'ventanilla_id' => $ventanillaId,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $consulta = $this->db->prepare('DELETE FROM categorias_turnero WHERE id = :id');

        return $consulta->execute(['id' => $id]);
    }
}
