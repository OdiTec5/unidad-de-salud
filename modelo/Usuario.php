<?php

require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function buscarPorCorreo(string $correo): array|false
    {
        $consulta = $this->db->prepare('SELECT * FROM usuarios WHERE correo = :correo LIMIT 1');
        $consulta->execute(['correo' => $correo]);

        return $consulta->fetch();
    }

    public function verificarCredenciales(string $correo, string $password): array|false
    {
        $datos = $this->buscarPorCorreo($correo);

        if ($datos && password_verify($password, $datos['password'])) {
            return $datos;
        }

        return false;
    }

    public function obtenerPorId(int $id): ?array
    {
        $consulta = $this->db->prepare(
            "SELECT u.id, u.nombre, u.correo, u.es_administrador, u.ventanilla_id, u.creado_en, u.ultimo_acceso,
                    v.nombre AS ventanilla_nombre
             FROM usuarios u
             LEFT JOIN ventanillas v ON v.id = u.ventanilla_id
             WHERE u.id = :id"
        );
        $consulta->execute(['id' => $id]);
        $fila = $consulta->fetch();

        return $fila !== false ? $fila : null;
    }

    public function obtenerTodos(): array
    {
        $consulta = $this->db->query(
            "SELECT u.id, u.nombre, u.correo, u.es_administrador, u.ventanilla_id, u.creado_en, u.ultimo_acceso,
                    v.nombre AS ventanilla_nombre
             FROM usuarios u
             LEFT JOIN ventanillas v ON v.id = u.ventanilla_id
             ORDER BY u.nombre"
        );

        return $consulta->fetchAll();
    }

    public function existeCorreo(string $correo, ?int $ignorarId = null): bool
    {
        if ($ignorarId !== null) {
            $consulta = $this->db->prepare('SELECT id FROM usuarios WHERE correo = :correo AND id != :id LIMIT 1');
            $consulta->execute(['correo' => $correo, 'id' => $ignorarId]);

            return $consulta->fetch() !== false;
        }

        return $this->buscarPorCorreo($correo) !== false;
    }

    public function crear(string $nombre, string $correo, string $password, bool $esAdministrador, ?int $ventanillaId): bool
    {
        $consulta = $this->db->prepare(
            'INSERT INTO usuarios (nombre, correo, password, es_administrador, ventanilla_id)
             VALUES (:nombre, :correo, :password, :es_administrador, :ventanilla_id)'
        );

        return $consulta->execute([
            'nombre' => $nombre,
            'correo' => $correo,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'es_administrador' => $esAdministrador ? 1 : 0,
            'ventanilla_id' => $ventanillaId,
        ]);
    }

    public function actualizar(int $id, string $nombre, string $correo, bool $esAdministrador, ?int $ventanillaId): bool
    {
        $consulta = $this->db->prepare(
            'UPDATE usuarios SET nombre = :nombre, correo = :correo, es_administrador = :es_administrador, ventanilla_id = :ventanilla_id
             WHERE id = :id'
        );

        return $consulta->execute([
            'id' => $id,
            'nombre' => $nombre,
            'correo' => $correo,
            'es_administrador' => $esAdministrador ? 1 : 0,
            'ventanilla_id' => $ventanillaId,
        ]);
    }

    public function actualizarPassword(int $id, string $password): bool
    {
        $consulta = $this->db->prepare('UPDATE usuarios SET password = :password WHERE id = :id');

        return $consulta->execute(['id' => $id, 'password' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    public function registrarAcceso(int $id): bool
    {
        $consulta = $this->db->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id');

        return $consulta->execute(['id' => $id]);
    }

    public function eliminar(int $id): bool
    {
        $consulta = $this->db->prepare('DELETE FROM usuarios WHERE id = :id');

        return $consulta->execute(['id' => $id]);
    }
}
