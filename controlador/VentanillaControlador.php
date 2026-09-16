<?php

require_once __DIR__ . '/../modelo/Ventanilla.php';

class VentanillaControlador
{
    private Ventanilla $modeloVentanilla;

    public function __construct()
    {
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
            } elseif ($accion === 'alternar_activa') {
                $this->alternarActiva();
            } else {
                [$error, $exito] = $this->guardar();
            }
        }

        $ventanillas = $this->modeloVentanilla->obtenerTodos();

        require __DIR__ . '/../vista/ventanillas/index.php';
    }

    private function guardar(): array
    {
        $nombre = trim($_POST['nombre'] ?? '');

        if ($nombre === '') {
            return ['El nombre de la ventanilla es obligatorio.', ''];
        }

        if ($this->modeloVentanilla->existeNombre($nombre)) {
            return ['Ya existe una ventanilla con ese nombre.', ''];
        }

        $this->modeloVentanilla->crear($nombre);

        return ['', 'Ventanilla agregada correctamente.'];
    }

    private function actualizar(): array
    {
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');

        if ($id <= 0 || $this->modeloVentanilla->obtenerPorId($id) === null) {
            return ['La ventanilla que intentas editar no existe.', ''];
        }

        if ($nombre === '') {
            return ['El nombre de la ventanilla es obligatorio.', ''];
        }

        if ($this->modeloVentanilla->existeNombre($nombre, $id)) {
            return ['Ya existe una ventanilla con ese nombre.', ''];
        }

        $this->modeloVentanilla->actualizar($id, $nombre);

        return ['', 'Ventanilla actualizada correctamente.'];
    }

    private function eliminar(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $this->modeloVentanilla->eliminar($id);
        }

        header('Location: index.php?ruta=ventanillas');
        exit;
    }

    private function alternarActiva(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $this->modeloVentanilla->alternarActiva($id);
        }

        header('Location: index.php?ruta=ventanillas');
        exit;
    }
}
