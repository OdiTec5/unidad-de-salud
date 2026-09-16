<?php

require_once __DIR__ . '/../modelo/Turno.php';
require_once __DIR__ . '/../modelo/AlcanceTurnero.php';

class GestionTurnosControlador
{
    private Turno $modeloTurno;
    private AlcanceTurnero $modeloAlcance;

    public function __construct()
    {
        $this->modeloTurno = new Turno();
        $this->modeloAlcance = new AlcanceTurnero();
    }

    public function index(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?ruta=login');
            exit;
        }

        $esAdministrador = !empty($_SESSION['usuario_es_administrador']);

        // null, null = sin restricción (alcance global de administrador).
        $categoriaIds = null;
        $subcategoriaIds = null;

        if (!$esAdministrador) {
            $usuarioId = (int) $_SESSION['usuario_id'];
            $categoriaIds = $this->modeloAlcance->obtenerCategoriasDeUsuario($usuarioId);
            $subcategoriaIds = $this->modeloAlcance->obtenerSubcategoriasDeUsuario($usuarioId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? '';

            if ($accion === 'avanzar') {
                $this->modeloTurno->avanzar($categoriaIds, $subcategoriaIds);
            } elseif ($accion === 'retroceder' && $esAdministrador) {
                $this->modeloTurno->retroceder();
            }

            header('Location: index.php?ruta=turnos');
            exit;
        }

        $actual = $this->modeloTurno->obtenerActual($categoriaIds, $subcategoriaIds);
        $proximos = $this->modeloTurno->obtenerProximos($actual['id'] ?? null, 4, $categoriaIds, $subcategoriaIds);
        $resumen = $this->modeloTurno->obtenerResumenPorCategoria($categoriaIds);
        $sinAlcanceAsignado = !$esAdministrador && empty($categoriaIds) && empty($subcategoriaIds);

        require __DIR__ . '/../vista/turnos/index.php';
    }
}
