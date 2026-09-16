<?php

require_once __DIR__ . '/../config/database.php';

class SubcategoriaTurnero
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function obtenerPorCategoria(int $categoriaId): array
    {
        $consulta = $this->db->prepare(
            'SELECT id, categoria_id, nombre, creado_en FROM subcategorias_turnero WHERE categoria_id = :categoria_id ORDER BY nombre'
        );
        $consulta->execute(['categoria_id' => $categoriaId]);

        return $consulta->fetchAll();
    }

    /**
     * Todas las subcategorías agrupadas por categoria_id, para pintar la sección de administración
     * sin tener que hacer una consulta por cada categoría.
     */
    public function obtenerAgrupadasPorCategoria(): array
    {
        $consulta = $this->db->query('SELECT id, categoria_id, nombre FROM subcategorias_turnero ORDER BY nombre');

        $agrupadas = [];
        foreach ($consulta->fetchAll() as $fila) {
            $agrupadas[(int) $fila['categoria_id']][] = $fila;
        }

        return $agrupadas;
    }

    public function obtenerPorId(int $id): ?array
    {
        $consulta = $this->db->prepare('SELECT id, categoria_id, nombre, creado_en FROM subcategorias_turnero WHERE id = :id');
        $consulta->execute(['id' => $id]);
        $fila = $consulta->fetch();

        return $fila !== false ? $fila : null;
    }

    public function existeNombre(int $categoriaId, string $nombre): bool
    {
        $consulta = $this->db->prepare(
            'SELECT id FROM subcategorias_turnero WHERE categoria_id = :categoria_id AND nombre = :nombre LIMIT 1'
        );
        $consulta->execute(['categoria_id' => $categoriaId, 'nombre' => $nombre]);

        return $consulta->fetch() !== false;
    }

    public function crear(int $categoriaId, string $nombre): bool
    {
        $consulta = $this->db->prepare(
            'INSERT INTO subcategorias_turnero (categoria_id, nombre) VALUES (:categoria_id, :nombre)'
        );

        return $consulta->execute(['categoria_id' => $categoriaId, 'nombre' => $nombre]);
    }

    public function eliminar(int $id): bool
    {
        $consulta = $this->db->prepare('DELETE FROM subcategorias_turnero WHERE id = :id');

        return $consulta->execute(['id' => $id]);
    }
}
