<?php

require_once __DIR__ . '/../config/database.php';

class Turno
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    /**
     * Fragmento SQL + parámetros para restringir por el alcance de un usuario no administrador:
     * categorías completas que puede avanzar, más subcategorías puntuales sueltas. null en ambos
     * significa "sin restricción" (uso administrador); arrays vacíos significan "sin alcance
     * asignado" (no debe ver ningún turno).
     */
    private function condicionAlcance(?array $categoriaIds, ?array $subcategoriaIds): array
    {
        if ($categoriaIds === null && $subcategoriaIds === null) {
            return ['', []];
        }

        $categoriaIds = array_values(array_unique(array_map('intval', $categoriaIds ?? [])));
        $subcategoriaIds = array_values(array_unique(array_map('intval', $subcategoriaIds ?? [])));

        if (empty($categoriaIds) && empty($subcategoriaIds)) {
            return [' AND 1 = 0', []];
        }

        $partes = [];
        $parametros = [];

        if (!empty($categoriaIds)) {
            $marcadores = [];
            foreach ($categoriaIds as $indice => $id) {
                $clave = "alcance_cat_{$indice}";
                $marcadores[] = ":{$clave}";
                $parametros[$clave] = $id;
            }
            $partes[] = 't.categoria_id IN (' . implode(',', $marcadores) . ')';
        }

        if (!empty($subcategoriaIds)) {
            $marcadores = [];
            foreach ($subcategoriaIds as $indice => $id) {
                $clave = "alcance_sub_{$indice}";
                $marcadores[] = ":{$clave}";
                $parametros[$clave] = $id;
            }
            $partes[] = 't.subcategoria_id IN (' . implode(',', $marcadores) . ')';
        }

        return [' AND (' . implode(' OR ', $partes) . ')', $parametros];
    }

    /**
     * Crea el turno dentro de una transacción con bloqueo de fila sobre la categoría, para que dos
     * solicitudes simultáneas de la misma categoría no reciban el mismo consecutivo del día.
     */
    public function crear(int $categoriaId, ?int $subcategoriaId, int $beneficiarioId): array
    {
        $this->db->beginTransaction();

        try {
            $bloqueo = $this->db->prepare('SELECT prefijo FROM categorias_turnero WHERE id = :id FOR UPDATE');
            $bloqueo->execute(['id' => $categoriaId]);
            $categoria = $bloqueo->fetch();

            $consulta = $this->db->prepare(
                "SELECT COALESCE(MAX(consecutivo), 0) + 1 AS siguiente
                 FROM turnos
                 WHERE categoria_id = :categoria_id AND DATE(creado_en) = CURDATE()"
            );
            $consulta->execute(['categoria_id' => $categoriaId]);
            $consecutivo = (int) $consulta->fetch()['siguiente'];

            $numeroTurno = $categoria['prefijo'] . '-' . str_pad((string) $consecutivo, 3, '0', STR_PAD_LEFT);

            $insertar = $this->db->prepare(
                'INSERT INTO turnos (categoria_id, subcategoria_id, beneficiario_id, numero_turno, consecutivo)
                 VALUES (:categoria_id, :subcategoria_id, :beneficiario_id, :numero_turno, :consecutivo)'
            );
            $insertar->execute([
                'categoria_id' => $categoriaId,
                'subcategoria_id' => $subcategoriaId,
                'beneficiario_id' => $beneficiarioId,
                'numero_turno' => $numeroTurno,
                'consecutivo' => $consecutivo,
            ]);

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();

            return ['id' => $id, 'numero_turno' => $numeroTurno];
        } catch (Throwable $excepcion) {
            $this->db->rollBack();
            throw $excepcion;
        }
    }

    public function obtenerActual(?array $categoriaIds = null, ?array $subcategoriaIds = null): ?array
    {
        [$condicion, $parametros] = $this->condicionAlcance($categoriaIds, $subcategoriaIds);

        $consulta = $this->db->prepare(
            "SELECT t.id, t.numero_turno, t.creado_en, c.nombre AS categoria_nombre, c.color AS categoria_color,
                    s.nombre AS subcategoria_nombre, v.nombre AS ventanilla_nombre,
                    CONCAT_WS(' ', b.primer_nombre, b.segundo_nombre, b.primer_apellido, b.segundo_apellido) AS beneficiario_nombre
             FROM turnos t
             JOIN categorias_turnero c ON c.id = t.categoria_id
             JOIN beneficiarios b ON b.id = t.beneficiario_id
             LEFT JOIN subcategorias_turnero s ON s.id = t.subcategoria_id
             LEFT JOIN ventanillas v ON v.id = c.ventanilla_id
             WHERE t.estado = 'espera' AND DATE(t.creado_en) = CURDATE() {$condicion}
             ORDER BY t.creado_en ASC, t.id ASC
             LIMIT 1"
        );
        $consulta->execute($parametros);
        $fila = $consulta->fetch();

        return $fila !== false ? $fila : null;
    }

    public function obtenerProximos(?int $idExcluir, int $limite = 4, ?array $categoriaIds = null, ?array $subcategoriaIds = null): array
    {
        [$condicion, $parametros] = $this->condicionAlcance($categoriaIds, $subcategoriaIds);

        $consulta = $this->db->prepare(
            "SELECT t.id, t.numero_turno, t.creado_en, c.nombre AS categoria_nombre, c.color AS categoria_color,
                    s.nombre AS subcategoria_nombre, v.nombre AS ventanilla_nombre,
                    CONCAT_WS(' ', b.primer_nombre, b.segundo_nombre, b.primer_apellido, b.segundo_apellido) AS beneficiario_nombre
             FROM turnos t
             JOIN categorias_turnero c ON c.id = t.categoria_id
             JOIN beneficiarios b ON b.id = t.beneficiario_id
             LEFT JOIN subcategorias_turnero s ON s.id = t.subcategoria_id
             LEFT JOIN ventanillas v ON v.id = c.ventanilla_id
             WHERE t.estado = 'espera' AND DATE(t.creado_en) = CURDATE() AND t.id != :id_excluir {$condicion}
             ORDER BY t.creado_en ASC, t.id ASC
             LIMIT :limite"
        );
        $consulta->bindValue('id_excluir', $idExcluir ?? 0, PDO::PARAM_INT);
        $consulta->bindValue('limite', $limite, PDO::PARAM_INT);
        foreach ($parametros as $clave => $valor) {
            $consulta->bindValue($clave, $valor, PDO::PARAM_INT);
        }
        $consulta->execute();

        return $consulta->fetchAll();
    }

    /**
     * Marca el turno actual (el más antiguo en espera, dentro del alcance dado) como atendido,
     * avanzando la fila. Sin alcance (null, null) opera de forma global, como lo hace un administrador.
     */
    public function avanzar(?array $categoriaIds = null, ?array $subcategoriaIds = null): ?array
    {
        $actual = $this->obtenerActual($categoriaIds, $subcategoriaIds);

        if ($actual === null) {
            return null;
        }

        $consulta = $this->db->prepare("UPDATE turnos SET estado = 'atendido', atendido_en = NOW() WHERE id = :id");
        $consulta->execute(['id' => $actual['id']]);

        return $actual;
    }

    /**
     * Deshace la última llamada: vuelve a poner en espera el turno atendido más recientemente.
     * Es una acción global, reservada a administradores.
     */
    public function retroceder(): ?array
    {
        $consulta = $this->db->query(
            "SELECT t.id, t.numero_turno, c.nombre AS categoria_nombre
             FROM turnos t
             JOIN categorias_turnero c ON c.id = t.categoria_id
             WHERE t.estado = 'atendido' AND DATE(t.creado_en) = CURDATE()
             ORDER BY t.atendido_en DESC, t.id DESC
             LIMIT 1"
        );
        $fila = $consulta->fetch();

        if ($fila === false) {
            return null;
        }

        $actualizar = $this->db->prepare("UPDATE turnos SET estado = 'espera', atendido_en = NULL WHERE id = :id");
        $actualizar->execute(['id' => $fila['id']]);

        return $fila;
    }

    public function obtenerResumenPorCategoria(?array $categoriaIds = null): array
    {
        $condicion = '';
        $parametros = [];

        if ($categoriaIds !== null) {
            $categoriaIds = array_values(array_unique(array_map('intval', $categoriaIds)));

            if (empty($categoriaIds)) {
                return [];
            }

            $marcadores = [];
            foreach ($categoriaIds as $indice => $id) {
                $clave = "cat_{$indice}";
                $marcadores[] = ":{$clave}";
                $parametros[$clave] = $id;
            }
            $condicion = ' WHERE c.id IN (' . implode(',', $marcadores) . ')';
        }

        $consulta = $this->db->prepare(
            "SELECT
                c.id, c.nombre, c.prefijo, c.color,
                SUM(CASE WHEN t.estado = 'atendido' AND DATE(t.creado_en) = CURDATE() THEN 1 ELSE 0 END) AS atendidos_hoy,
                SUM(CASE WHEN t.estado = 'espera' AND DATE(t.creado_en) = CURDATE() THEN 1 ELSE 0 END) AS en_espera_hoy,
                SUM(CASE WHEN DATE(t.creado_en) = CURDATE() THEN 1 ELSE 0 END) AS total_hoy
             FROM categorias_turnero c
             LEFT JOIN turnos t ON t.categoria_id = c.id
             {$condicion}
             GROUP BY c.id, c.nombre, c.prefijo, c.color
             ORDER BY c.nombre"
        );
        $consulta->execute($parametros);

        return $consulta->fetchAll();
    }
}
