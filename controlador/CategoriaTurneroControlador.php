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

    /**
     * Lee hora_inicio/hora_fin/dias[] del POST. Si falta cualquiera de las dos horas, se
     * descartan ambas (sin restricción de horario); los días se guardan como CSV o null si no
     * se marcó ninguno (sin restricción de días).
     */
    private function leerHorarioPost(): array
    {
        $horaInicio = trim($_POST['hora_inicio'] ?? '');
        $horaFin = trim($_POST['hora_fin'] ?? '');
        $dias = array_filter(array_map('intval', $_POST['dias'] ?? []), static fn (int $d): bool => $d >= 1 && $d <= 7);

        if ($horaInicio === '' || $horaFin === '' || $horaInicio >= $horaFin) {
            $horaInicio = null;
            $horaFin = null;
        }

        $diasSemana = !empty($dias) ? implode(',', array_unique($dias)) : null;

        return [$horaInicio ?: null, $horaFin ?: null, $diasSemana];
    }

    private function guardar(): array
    {
        $nombre = trim($_POST['nombre'] ?? '');
        $prefijo = mb_strtoupper(trim($_POST['prefijo'] ?? ''));
        $color = $this->validarColor(trim($_POST['color'] ?? '')) ?? '#0071e3';
        $ventanillaId = (int) ($_POST['ventanilla_id'] ?? 0);
        [$horaInicio, $horaFin, $diasSemana] = $this->leerHorarioPost();

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

        $this->modeloCategoria->crear($nombre, $prefijo, $color, $ventanillaId > 0 ? $ventanillaId : null, $horaInicio, $horaFin, $diasSemana);

        return ['', 'Categoría agregada correctamente.'];
    }

    private function actualizar(): array
    {
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $prefijo = mb_strtoupper(trim($_POST['prefijo'] ?? ''));
        $color = $this->validarColor(trim($_POST['color'] ?? '')) ?? '#0071e3';
        $ventanillaId = (int) ($_POST['ventanilla_id'] ?? 0);
        [$horaInicio, $horaFin, $diasSemana] = $this->leerHorarioPost();

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

        $this->modeloCategoria->actualizar($id, $nombre, $prefijo, $color, $ventanillaId > 0 ? $ventanillaId : null, $horaInicio, $horaFin, $diasSemana);

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
