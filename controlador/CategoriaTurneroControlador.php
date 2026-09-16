<?php

require_once __DIR__ . '/../modelo/CategoriaTurnero.php';
require_once __DIR__ . '/../modelo/SubcategoriaTurnero.php';
require_once __DIR__ . '/../modelo/Ventanilla.php';

class CategoriaTurneroControlador
{
    private CategoriaTurnero $modeloCategoria;
    private SubcategoriaTurnero $modeloSubcategoria;
    private Ventanilla $modeloVentanilla;

    public function __construct()
    {
        $this->modeloCategoria = new CategoriaTurnero();
        $this->modeloSubcategoria = new SubcategoriaTurnero();
        $this->modeloVentanilla = new Ventanilla();
    }

    public function index(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?ruta=login');
            exit;
        }

        if (empty($_SESSION['usuario_es_administrador'])) {
            header('Location: index.php?ruta=turnos');
            exit;
        }

        $error = '';
        $exito = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? '';

            if ($accion === 'eliminar') {
                $this->eliminar();
            } elseif ($accion === 'actualizar') {
                [$error, $exito] = $this->actualizar();
            } elseif ($accion === 'crear_subcategoria') {
                [$error, $exito] = $this->guardarSubcategoria();
            } elseif ($accion === 'eliminar_subcategoria') {
                $this->eliminarSubcategoria();
            } else {
                [$error, $exito] = $this->guardar();
            }
        }

        $categorias = $this->modeloCategoria->obtenerTodos();
        $subcategoriasPorCategoria = $this->modeloSubcategoria->obtenerAgrupadasPorCategoria();
        $ventanillas = $this->modeloVentanilla->obtenerActivas();

        require __DIR__ . '/../vista/categorias-turnero/index.php';
    }

    private function guardarSubcategoria(): array
    {
        $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
        $nombre = trim($_POST['nombre_subcategoria'] ?? '');

        if ($categoriaId <= 0 || $this->modeloCategoria->obtenerPorId($categoriaId) === null) {
            return ['La categoría seleccionada no existe.', ''];
        }

        if ($nombre === '') {
            return ['El nombre de la subcategoría es obligatorio.', ''];
        }

        if ($this->modeloSubcategoria->existeNombre($categoriaId, $nombre)) {
            return ['Esa subcategoría ya existe en esta categoría.', ''];
        }

        $this->modeloSubcategoria->crear($categoriaId, $nombre);

        return ['', 'Subcategoría agregada correctamente.'];
    }

    private function eliminarSubcategoria(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $this->modeloSubcategoria->eliminar($id);
        }

        header('Location: index.php?ruta=categorias-turnero');
        exit;
    }

    private function validarColor(string $color): ?string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1 ? $color : null;
    }

    private function guardar(): array
    {
        $nombre = trim($_POST['nombre'] ?? '');
        $prefijo = mb_strtoupper(trim($_POST['prefijo'] ?? ''));
        $color = $this->validarColor(trim($_POST['color'] ?? '')) ?? '#0071e3';
        $ventanillaId = (int) ($_POST['ventanilla_id'] ?? 0);

        if ($nombre === '' || $prefijo === '') {
            return ['El nombre y el prefijo son obligatorios.', ''];
        }

        if (mb_strlen($prefijo) > 5) {
            return ['El prefijo no puede tener más de 5 caracteres.', ''];
        }

        if ($this->modeloCategoria->existeNombre($nombre)) {
            return ['Ya existe una categoría con ese nombre.', ''];
        }

        if ($this->modeloCategoria->existePrefijo($prefijo)) {
            return ['Ya existe una categoría con ese prefijo.', ''];
        }

        $this->modeloCategoria->crear($nombre, $prefijo, $color, $ventanillaId > 0 ? $ventanillaId : null);

        return ['', 'Categoría agregada correctamente.'];
    }

    private function actualizar(): array
    {
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $prefijo = mb_strtoupper(trim($_POST['prefijo'] ?? ''));
        $color = $this->validarColor(trim($_POST['color'] ?? '')) ?? '#0071e3';
        $ventanillaId = (int) ($_POST['ventanilla_id'] ?? 0);

        if ($id <= 0 || $this->modeloCategoria->obtenerPorId($id) === null) {
            return ['La categoría que intentas editar no existe.', ''];
        }

        if ($nombre === '' || $prefijo === '') {
            return ['El nombre y el prefijo son obligatorios.', ''];
        }

        if (mb_strlen($prefijo) > 5) {
            return ['El prefijo no puede tener más de 5 caracteres.', ''];
        }

        if ($this->modeloCategoria->existeNombre($nombre, $id)) {
            return ['Ya existe una categoría con ese nombre.', ''];
        }

        if ($this->modeloCategoria->existePrefijo($prefijo, $id)) {
            return ['Ya existe una categoría con ese prefijo.', ''];
        }

        $this->modeloCategoria->actualizar($id, $nombre, $prefijo, $color, $ventanillaId > 0 ? $ventanillaId : null);

        return ['', 'Categoría actualizada correctamente.'];
    }

    private function eliminar(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $this->modeloCategoria->eliminar($id);
        }

        header('Location: index.php?ruta=categorias-turnero');
        exit;
    }
}
