<?php

require_once __DIR__ . '/../config/database.php';

class Ventanilla
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function obtenerTodos(): array
    {
        $consulta = $this->db->query('SELECT id, nombre, activa, creado_en FROM ventanillas ORDER BY nombre');

        return $consulta->fetchAll();
    }

    public function obtenerActivas(): array
    {
        $consulta = $this->db->query('SELECT id, nombre FROM ventanillas WHERE activa = 1 ORDER BY nombre');

        return $consulta->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $consulta = $this->db->prepare('SELECT id, nombre, activa, creado_en FROM ventanillas WHERE id = :id');
        $consulta->execute(['id' => $id]);
        $fila = $consulta->fetch();

        return $fila !== false ? $fila : null;
    }

    public function existeNombre(string $nombre, ?int $ignorarId = null): bool
    {
        $consulta = $ignorarId !== null
            ? $this->db->prepare('SELECT id FROM ventanillas WHERE nombre = :nombre AND id != :id LIMIT 1')
            : $this->db->prepare('SELECT id FROM ventanillas WHERE nombre = :nombre LIMIT 1');

        $parametros = ['nombre' => $nombre];
        if ($ignorarId !== null) {
            $parametros['id'] = $ignorarId;
        }

        $consulta->execute($parametros);

        return $consulta->fetch() !== false;
    }

    public function crear(string $nombre): bool
    {
        $consulta = $this->db->prepare('INSERT INTO ventanillas (nombre) VALUES (:nombre)');

        return $consulta->execute(['nombre' => $nombre]);
    }

    public function actualizar(int $id, string $nombre): bool
    {
        $consulta = $this->db->prepare('UPDATE ventanillas SET nombre = :nombre WHERE id = :id');

        return $consulta->execute(['id' => $id, 'nombre' => $nombre]);
    }

    public function eliminar(int $id): bool
    {
        $consulta = $this->db->prepare('DELETE FROM ventanillas WHERE id = :id');

        return $consulta->execute(['id' => $id]);
    }

    public function alternarActiva(int $id): bool
    {
        $consulta = $this->db->prepare('UPDATE ventanillas SET activa = 1 - activa WHERE id = :id');

        return $consulta->execute(['id' => $id]);
    }
}
