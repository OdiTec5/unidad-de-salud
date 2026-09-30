<?php

require_once __DIR__ . '/../modelo/Turno.php';
require_once __DIR__ . '/../modelo/AlcanceTurnero.php';
require_once __DIR__ . '/../modelo/CategoriaTurnero.php';

class GestionTurnosControlador
{
    private Turno $modeloTurno;
    private AlcanceTurnero $modeloAlcance;
    private CategoriaTurnero $modeloCategoria;

    public function __construct()
    {
        $this->modeloTurno = new Turno();
        $this->modeloAlcance = new AlcanceTurnero();
        $this->modeloCategoria = new CategoriaTurnero();
    }

    public function index(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            if ($this->esPeticionJson()) {
                $this->responderJson(['ok' => false, 'mensaje' => 'Sesión expirada, vuelve a iniciar sesión.'], 401);
            }

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
            $ok = true;
            $mensaje = '';

            if ($accion === 'avanzar') {
                $turno = $this->modeloTurno->avanzar($categoriaIds, $subcategoriaIds);
                $ok = $turno !== null;
                $mensaje = $ok ? 'Avanzaste al turno ' . $turno['numero_turno'] . '.' : 'No hay turnos en espera para avanzar.';
            } elseif ($accion === 'retroceder' && $esAdministrador) {
                $turno = $this->modeloTurno->retroceder();
                $ok = $turno !== null;
                $mensaje = $ok ? 'Volviste a poner en espera el turno ' . $turno['numero_turno'] . '.' : 'No hay ningún turno atendido hoy para retroceder.';
            } elseif ($accion === 'transferir') {
                [$ok, $mensaje] = $this->transferir($categoriaIds, $subcategoriaIds);
            } else {
                $ok = false;
                $mensaje = 'Acción no reconocida.';
            }

            $estado = $this->construirEstado($esAdministrador, $categoriaIds, $subcategoriaIds);
            $this->responderJson(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $estado));
        }

        if ($this->esPeticionJson()) {
            $this->responderJson(array_merge(['ok' => true, 'mensaje' => ''], $this->construirEstado($esAdministrador, $categoriaIds, $subcategoriaIds)));
        }

        $tituloPagina = 'Turnos';
        $estado = $this->construirEstado($esAdministrador, $categoriaIds, $subcategoriaIds);
        $actual = $estado['actual'];
        $siguiente = $estado['siguiente'];
        $proximos = $estado['proximos'];
        $resumen = $estado['resumen'];
        $sinAlcanceAsignado = $estado['sinAlcanceAsignado'];
        $categorias = $this->modeloCategoria->obtenerTodos();

        require __DIR__ . '/../vista/turnos/index.php';
    }

    private function esPeticionJson(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    private function responderJson(array $datos, int $codigoHttp = 200): void
    {
        http_response_code($codigoHttp);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos);
        exit;
    }

    private function construirEstado(bool $esAdministrador, ?array $categoriaIds, ?array $subcategoriaIds): array
    {
        $actual = $this->modeloTurno->obtenerActual($categoriaIds, $subcategoriaIds);
        $proximos = $this->modeloTurno->obtenerProximos($actual['id'] ?? null, 4, $categoriaIds, $subcategoriaIds);

        return [
            'esAdministrador' => $esAdministrador,
            'actual' => $actual,
            // El primero de "próximos": la persona que sigue en la fila detrás de quien ya se está
            // atendiendo. Es a quien se transfiere, no al turno actual (ese ya está siendo atendido).
            'siguiente' => $proximos[0] ?? null,
            'proximos' => $proximos,
            'resumen' => $this->modeloTurno->obtenerResumenPorCategoria($categoriaIds),
            'sinAlcanceAsignado' => !$esAdministrador && empty($categoriaIds) && empty($subcategoriaIds),
        ];
    }

    /**
     * Solo se puede transferir el turno que ya es el "siguiente" del usuario (se vuelve a calcular
     * en el servidor, con su mismo alcance), para que nadie mueva turnos ajenos con una petición
     * armada a mano. Se transfiere el siguiente, no el actual, porque el actual ya se está
     * atendiendo (la persona ya está frente al operador).
     */
    private function transferir(?array $categoriaIds, ?array $subcategoriaIds): array
    {
        $turnoId = (int) ($_POST['turno_id'] ?? 0);
        $categoriaDestinoId = (int) ($_POST['categoria_destino_id'] ?? 0);

        if ($turnoId <= 0 || $categoriaDestinoId <= 0) {
            return [false, 'Selecciona una categoría destino válida.'];
        }

        $actual = $this->modeloTurno->obtenerActual($categoriaIds, $subcategoriaIds);
        $proximos = $this->modeloTurno->obtenerProximos($actual['id'] ?? null, 1, $categoriaIds, $subcategoriaIds);
        $siguiente = $proximos[0] ?? null;

        if ($siguiente === null || (int) $siguiente['id'] !== $turnoId) {
            return [false, 'Ese turno ya no es el siguiente en tu fila.'];
        }

        $resultado = $this->modeloTurno->transferir($turnoId, $categoriaDestinoId);

        if ($resultado === null) {
            return [false, 'No se pudo transferir el turno.'];
        }

        return [true, 'Turno transferido como ' . $resultado['numero_turno'] . '.'];
    }
}
