<?php

require_once __DIR__ . '/../modelo/Beneficiario.php';
require_once __DIR__ . '/../modelo/CategoriaTurnero.php';
require_once __DIR__ . '/../modelo/SubcategoriaTurnero.php';
require_once __DIR__ . '/../modelo/Turno.php';

class TurneroPublicoControlador
{
    private Beneficiario $modeloBeneficiario;
    private CategoriaTurnero $modeloCategoria;
    private SubcategoriaTurnero $modeloSubcategoria;
    private Turno $modeloTurno;

    public function __construct()
    {
        $this->modeloBeneficiario = new Beneficiario();
        $this->modeloCategoria = new CategoriaTurnero();
        $this->modeloSubcategoria = new SubcategoriaTurnero();
        $this->modeloTurno = new Turno();
    }

    public function solicitar(): void
    {
        $paso = 'documento';
        $error = '';
        $beneficiario = null;
        $categorias = [];
        $categoriaSeleccionada = null;
        $subcategorias = [];
        $turnoCreado = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? '';

            if ($accion === 'validar_documento') {
                $tipo = mb_strtoupper(trim($_POST['tipo_identificacion'] ?? ''));
                $documento = trim($_POST['documento'] ?? '');

                if ($tipo === '' || $documento === '') {
                    $error = 'Selecciona tu tipo de documento e ingresa el número.';
                } else {
                    $beneficiario = $this->modeloBeneficiario->obtenerPorTipoYDocumento($tipo, $documento);

                    if ($beneficiario === null) {
                        $error = 'No encontramos tu documento registrado como beneficiario. Verifica los datos o acércate al mostrador.';
                    } elseif ((int) $beneficiario['bloqueado'] === 1) {
                        $error = 'Tu documento está bloqueado para solicitar turnos. Por favor acércate al mostrador.';
                    } else {
                        $paso = 'categoria';
                        $categorias = $this->modeloCategoria->obtenerTodos();
                    }
                }
            } elseif ($accion === 'elegir_categoria') {
                $tipo = mb_strtoupper(trim($_POST['tipo_identificacion'] ?? ''));
                $documento = trim($_POST['documento'] ?? '');
                $categoriaId = (int) ($_POST['categoria_id'] ?? 0);

                $beneficiario = $this->modeloBeneficiario->obtenerPorTipoYDocumento($tipo, $documento);
                $categoria = $categoriaId > 0 ? $this->modeloCategoria->obtenerPorId($categoriaId) : null;

                if ($beneficiario === null || (int) $beneficiario['bloqueado'] === 1) {
                    $error = 'Tu documento ya no es válido para solicitar turnos. Por favor acércate al mostrador.';
                    $paso = 'documento';
                } elseif ($categoria === null) {
                    $error = 'Selecciona una categoría válida.';
                    $paso = 'categoria';
                    $categorias = $this->modeloCategoria->obtenerTodos();
                } else {
                    $subcategorias = $this->modeloSubcategoria->obtenerPorCategoria($categoriaId);

                    if (!empty($subcategorias)) {
                        $paso = 'subcategoria';
                        $categoriaSeleccionada = $categoria;
                    } else {
                        $turnoCreado = $this->modeloTurno->crear($categoriaId, null, (int) $beneficiario['id']);
                        $turnoCreado['categoria_nombre'] = $categoria['nombre'];
                        $turnoCreado['categoria_color'] = $categoria['color'];
                        $turnoCreado['subcategoria_nombre'] = null;
                        $paso = 'confirmacion';
                    }
                }
            } elseif ($accion === 'confirmar_turno') {
                $tipo = mb_strtoupper(trim($_POST['tipo_identificacion'] ?? ''));
                $documento = trim($_POST['documento'] ?? '');
                $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
                $subcategoriaId = (int) ($_POST['subcategoria_id'] ?? 0);

                $beneficiario = $this->modeloBeneficiario->obtenerPorTipoYDocumento($tipo, $documento);
                $categoria = $categoriaId > 0 ? $this->modeloCategoria->obtenerPorId($categoriaId) : null;
                $subcategoria = $subcategoriaId > 0 ? $this->modeloSubcategoria->obtenerPorId($subcategoriaId) : null;

                if ($beneficiario === null || (int) $beneficiario['bloqueado'] === 1) {
                    $error = 'Tu documento ya no es válido para solicitar turnos. Por favor acércate al mostrador.';
                    $paso = 'documento';
                } elseif ($categoria === null) {
                    $error = 'Selecciona una categoría válida.';
                    $paso = 'categoria';
                    $categorias = $this->modeloCategoria->obtenerTodos();
                } elseif ($subcategoria === null || (int) $subcategoria['categoria_id'] !== $categoriaId) {
                    $error = 'Selecciona una subcategoría válida.';
                    $paso = 'subcategoria';
                    $categoriaSeleccionada = $categoria;
                    $subcategorias = $this->modeloSubcategoria->obtenerPorCategoria($categoriaId);
                } else {
                    $turnoCreado = $this->modeloTurno->crear($categoriaId, $subcategoriaId, (int) $beneficiario['id']);
                    $turnoCreado['categoria_nombre'] = $categoria['nombre'];
                    $turnoCreado['categoria_color'] = $categoria['color'];
                    $turnoCreado['subcategoria_nombre'] = $subcategoria['nombre'];
                    $paso = 'confirmacion';
                }
            }
        }

        require __DIR__ . '/../vista/turnero-publico/solicitar.php';
    }

    public function pantalla(): void
    {
        require __DIR__ . '/../vista/turnero-publico/pantalla.php';
    }

    public function pantallaDatos(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $actual = $this->modeloTurno->obtenerActual();
        $proximos = $this->modeloTurno->obtenerProximos($actual['id'] ?? null, 4);

        echo json_encode([
            'actual' => $actual,
            'proximos' => $proximos,
        ]);
    }
}
