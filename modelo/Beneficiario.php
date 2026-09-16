<?php

require_once __DIR__ . '/../config/database.php';

class Beneficiario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function obtenerTodos(): array
    {
        $consulta = $this->db->query(
            'SELECT id, tipo_identificacion, documento, primer_apellido, segundo_apellido, primer_nombre, segundo_nombre, bloqueado, creado_en
             FROM beneficiarios
             ORDER BY primer_apellido, primer_nombre'
        );

        return $consulta->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $consulta = $this->db->prepare(
            'SELECT id, tipo_identificacion, documento, primer_apellido, segundo_apellido, primer_nombre, segundo_nombre, bloqueado, creado_en
             FROM beneficiarios WHERE id = :id'
        );
        $consulta->execute(['id' => $id]);
        $fila = $consulta->fetch();

        return $fila !== false ? $fila : null;
    }

    public function obtenerPorTipoYDocumento(string $tipoIdentificacion, string $documento): ?array
    {
        $consulta = $this->db->prepare(
            'SELECT id, tipo_identificacion, documento, primer_apellido, segundo_apellido, primer_nombre, segundo_nombre, bloqueado
             FROM beneficiarios WHERE tipo_identificacion = :tipo AND documento = :documento LIMIT 1'
        );
        $consulta->execute(['tipo' => $tipoIdentificacion, 'documento' => $documento]);
        $fila = $consulta->fetch();

        return $fila !== false ? $fila : null;
    }

    public function existeDocumento(string $tipoIdentificacion, string $documento, ?int $ignorarId = null): bool
    {
        $consulta = $ignorarId !== null
            ? $this->db->prepare('SELECT id FROM beneficiarios WHERE tipo_identificacion = :tipo AND documento = :documento AND id != :id LIMIT 1')
            : $this->db->prepare('SELECT id FROM beneficiarios WHERE tipo_identificacion = :tipo AND documento = :documento LIMIT 1');

        $parametros = ['tipo' => $tipoIdentificacion, 'documento' => $documento];
        if ($ignorarId !== null) {
            $parametros['id'] = $ignorarId;
        }

        $consulta->execute($parametros);

        return $consulta->fetch() !== false;
    }

    public function crear(
        string $tipoIdentificacion,
        string $documento,
        string $primerApellido,
        ?string $segundoApellido,
        string $primerNombre,
        ?string $segundoNombre
    ): bool {
        $consulta = $this->db->prepare(
            'INSERT INTO beneficiarios (tipo_identificacion, documento, primer_apellido, segundo_apellido, primer_nombre, segundo_nombre)
             VALUES (:tipo, :documento, :primer_apellido, :segundo_apellido, :primer_nombre, :segundo_nombre)'
        );

        return $consulta->execute([
            'tipo' => $tipoIdentificacion,
            'documento' => $documento,
            'primer_apellido' => $primerApellido,
            'segundo_apellido' => $segundoApellido,
            'primer_nombre' => $primerNombre,
            'segundo_nombre' => $segundoNombre,
        ]);
    }

    public function actualizar(
        int $id,
        string $tipoIdentificacion,
        string $documento,
        string $primerApellido,
        ?string $segundoApellido,
        string $primerNombre,
        ?string $segundoNombre
    ): bool {
        $consulta = $this->db->prepare(
            'UPDATE beneficiarios
             SET tipo_identificacion = :tipo, documento = :documento, primer_apellido = :primer_apellido,
                 segundo_apellido = :segundo_apellido, primer_nombre = :primer_nombre, segundo_nombre = :segundo_nombre
             WHERE id = :id'
        );

        return $consulta->execute([
            'id' => $id,
            'tipo' => $tipoIdentificacion,
            'documento' => $documento,
            'primer_apellido' => $primerApellido,
            'segundo_apellido' => $segundoApellido,
            'primer_nombre' => $primerNombre,
            'segundo_nombre' => $segundoNombre,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $consulta = $this->db->prepare('DELETE FROM beneficiarios WHERE id = :id');

        return $consulta->execute(['id' => $id]);
    }

    public function alternarBloqueo(int $id): bool
    {
        $consulta = $this->db->prepare('UPDATE beneficiarios SET bloqueado = 1 - bloqueado WHERE id = :id');

        return $consulta->execute(['id' => $id]);
    }
}
