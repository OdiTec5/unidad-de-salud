<?php

require_once __DIR__ . '/../config/database.php';

class RecuperacionPassword
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function invalidarPendientes(int $usuarioId): bool
    {
        $consulta = $this->db->prepare(
            'UPDATE recuperaciones_password SET usado = 1 WHERE usuario_id = :usuario_id AND usado = 0'
        );

        return $consulta->execute(['usuario_id' => $usuarioId]);
    }

    public function crear(int $usuarioId, string $codigo, string $expiraEn): bool
    {
        $consulta = $this->db->prepare(
            'INSERT INTO recuperaciones_password (usuario_id, codigo, expira_en) VALUES (:usuario_id, :codigo, :expira_en)'
        );

        return $consulta->execute([
            'usuario_id' => $usuarioId,
            'codigo' => $codigo,
            'expira_en' => $expiraEn,
        ]);
    }

    public function obtenerVigente(int $usuarioId, string $codigo): ?array
    {
        $consulta = $this->db->prepare(
            'SELECT * FROM recuperaciones_password
             WHERE usuario_id = :usuario_id AND codigo = :codigo AND usado = 0 AND expira_en >= NOW()
             ORDER BY id DESC LIMIT 1'
        );
        $consulta->execute(['usuario_id' => $usuarioId, 'codigo' => $codigo]);
        $fila = $consulta->fetch();

        return $fila !== false ? $fila : null;
    }

    public function marcarUsado(int $id): bool
    {
        $consulta = $this->db->prepare('UPDATE recuperaciones_password SET usado = 1 WHERE id = :id');

        return $consulta->execute(['id' => $id]);
    }
}
