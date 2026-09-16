<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Qué categorías y subcategorías de turnero puede avanzar cada usuario no administrador. Un usuario
 * con una categoría asignada puede avanzar cualquier turno de esa categoría (sin importar la
 * subcategoría); un usuario con solo una subcategoría asignada puede avanzar turnos de esa
 * subcategoría puntual, aunque no tenga la categoría completa.
 */
class AlcanceTurnero
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function obtenerCategoriasDeUsuario(int $usuarioId): array
    {
        $consulta = $this->db->prepare('SELECT categoria_id FROM usuario_categorias_turnero WHERE usuario_id = :usuario_id');
        $consulta->execute(['usuario_id' => $usuarioId]);

        return array_map('intval', $consulta->fetchAll(PDO::FETCH_COLUMN));
    }

    public function obtenerSubcategoriasDeUsuario(int $usuarioId): array
    {
        $consulta = $this->db->prepare('SELECT subcategoria_id FROM usuario_subcategorias_turnero WHERE usuario_id = :usuario_id');
        $consulta->execute(['usuario_id' => $usuarioId]);

        return array_map('intval', $consulta->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Todas las asignaciones de categorías, agrupadas por usuario_id, para precargar el modal de
     * editar usuario sin tener que hacer una consulta por cada fila de la tabla.
     */
    public function obtenerTodasCategoriasPorUsuario(): array
    {
        $consulta = $this->db->query('SELECT usuario_id, categoria_id FROM usuario_categorias_turnero');

        $agrupadas = [];
        foreach ($consulta->fetchAll() as $fila) {
            $agrupadas[(int) $fila['usuario_id']][] = (int) $fila['categoria_id'];
        }

        return $agrupadas;
    }

    public function obtenerTodasSubcategoriasPorUsuario(): array
    {
        $consulta = $this->db->query('SELECT usuario_id, subcategoria_id FROM usuario_subcategorias_turnero');

        $agrupadas = [];
        foreach ($consulta->fetchAll() as $fila) {
            $agrupadas[(int) $fila['usuario_id']][] = (int) $fila['subcategoria_id'];
        }

        return $agrupadas;
    }

    /**
     * Reemplaza por completo el alcance de un usuario (borra lo anterior y guarda lo nuevo), para
     * que guardar el formulario de usuario sea siempre una operación de "así queda", sin tener que
     * calcular altas y bajas.
     */
    public function guardarAlcance(int $usuarioId, array $categoriaIds, array $subcategoriaIds): void
    {
        $this->db->beginTransaction();

        try {
            $borrarCategorias = $this->db->prepare('DELETE FROM usuario_categorias_turnero WHERE usuario_id = :usuario_id');
            $borrarCategorias->execute(['usuario_id' => $usuarioId]);

            $borrarSubcategorias = $this->db->prepare('DELETE FROM usuario_subcategorias_turnero WHERE usuario_id = :usuario_id');
            $borrarSubcategorias->execute(['usuario_id' => $usuarioId]);

            $insertarCategoria = $this->db->prepare(
                'INSERT INTO usuario_categorias_turnero (usuario_id, categoria_id) VALUES (:usuario_id, :categoria_id)'
            );
            foreach (array_unique(array_map('intval', $categoriaIds)) as $categoriaId) {
                if ($categoriaId > 0) {
                    $insertarCategoria->execute(['usuario_id' => $usuarioId, 'categoria_id' => $categoriaId]);
                }
            }

            $insertarSubcategoria = $this->db->prepare(
                'INSERT INTO usuario_subcategorias_turnero (usuario_id, subcategoria_id) VALUES (:usuario_id, :subcategoria_id)'
            );
            foreach (array_unique(array_map('intval', $subcategoriaIds)) as $subcategoriaId) {
                if ($subcategoriaId > 0) {
                    $insertarSubcategoria->execute(['usuario_id' => $usuarioId, 'subcategoria_id' => $subcategoriaId]);
                }
            }

            $this->db->commit();
        } catch (Throwable $excepcion) {
            $this->db->rollBack();
            throw $excepcion;
        }
    }
}
