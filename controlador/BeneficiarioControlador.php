<?php

require_once __DIR__ . '/../modelo/Beneficiario.php';

class BeneficiarioControlador
{
    private const TIPOS_VALIDOS = ['CC', 'TI', 'RC', 'CE', 'PA', 'PEP', 'PT'];

    private Beneficiario $modeloBeneficiario;

    public function __construct()
    {
        $this->modeloBeneficiario = new Beneficiario();
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
            } elseif ($accion === 'alternar_bloqueo') {
                $this->alternarBloqueo();
            } elseif ($accion === 'actualizar') {
                [$error, $exito] = $this->actualizar();
            } else {
                [$error, $exito] = $this->guardar();
            }
        }

        $beneficiarios = $this->modeloBeneficiario->obtenerTodos();
        $tipos = self::TIPOS_VALIDOS;

        require __DIR__ . '/../vista/beneficiarios/index.php';
    }

    private function leerDatosFormulario(): array
    {
        return [
            'tipo' => mb_strtoupper(trim($_POST['tipo_identificacion'] ?? '')),
            'documento' => trim($_POST['documento'] ?? ''),
            'primer_apellido' => trim($_POST['primer_apellido'] ?? ''),
            'segundo_apellido' => trim($_POST['segundo_apellido'] ?? ''),
            'primer_nombre' => trim($_POST['primer_nombre'] ?? ''),
            'segundo_nombre' => trim($_POST['segundo_nombre'] ?? ''),
        ];
    }

    private function validar(array $datos): string
    {
        if (!in_array($datos['tipo'], self::TIPOS_VALIDOS, true)) {
            return 'Selecciona un tipo de identificación válido.';
        }

        if ($datos['documento'] === '' || $datos['primer_apellido'] === '' || $datos['primer_nombre'] === '') {
            return 'Documento, primer apellido y primer nombre son obligatorios.';
        }

        return '';
    }

    private function guardar(): array
    {
        $datos = $this->leerDatosFormulario();
        $errorValidacion = $this->validar($datos);

        if ($errorValidacion !== '') {
            return [$errorValidacion, ''];
        }

        if ($this->modeloBeneficiario->existeDocumento($datos['tipo'], $datos['documento'])) {
            return ['Ya existe un beneficiario con ese tipo y número de documento.', ''];
        }

        $this->modeloBeneficiario->crear(
            $datos['tipo'],
            $datos['documento'],
            $datos['primer_apellido'],
            $datos['segundo_apellido'] !== '' ? $datos['segundo_apellido'] : null,
            $datos['primer_nombre'],
            $datos['segundo_nombre'] !== '' ? $datos['segundo_nombre'] : null
        );

        return ['', 'Beneficiario agregado correctamente.'];
    }

    private function actualizar(): array
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0 || $this->modeloBeneficiario->obtenerPorId($id) === null) {
            return ['El beneficiario que intentas editar no existe.', ''];
        }

        $datos = $this->leerDatosFormulario();
        $errorValidacion = $this->validar($datos);

        if ($errorValidacion !== '') {
            return [$errorValidacion, ''];
        }

        if ($this->modeloBeneficiario->existeDocumento($datos['tipo'], $datos['documento'], $id)) {
            return ['Ya existe un beneficiario con ese tipo y número de documento.', ''];
        }

        $this->modeloBeneficiario->actualizar(
            $id,
            $datos['tipo'],
            $datos['documento'],
            $datos['primer_apellido'],
            $datos['segundo_apellido'] !== '' ? $datos['segundo_apellido'] : null,
            $datos['primer_nombre'],
            $datos['segundo_nombre'] !== '' ? $datos['segundo_nombre'] : null
        );

        return ['', 'Beneficiario actualizado correctamente.'];
    }

    private function eliminar(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $this->modeloBeneficiario->eliminar($id);
        }

        header('Location: index.php?ruta=beneficiarios');
        exit;
    }

    private function alternarBloqueo(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $this->modeloBeneficiario->alternarBloqueo($id);
        }

        header('Location: index.php?ruta=beneficiarios');
        exit;
    }
}
