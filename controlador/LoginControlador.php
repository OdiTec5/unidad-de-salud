<?php

require_once __DIR__ . '/../modelo/Usuario.php';
require_once __DIR__ . '/../modelo/RecuperacionPassword.php';

class LoginControlador
{
    private Usuario $modeloUsuario;

    public function __construct()
    {
        $this->modeloUsuario = new Usuario();
    }

    public function index(): void
    {
        if (!empty($_SESSION['usuario_id'])) {
            header('Location: index.php?ruta=dashboard');
            exit;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = $this->procesar();
        }

        require __DIR__ . '/../vista/login/login.php';
    }

    private function procesar(): string
    {
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($correo === '' || $password === '') {
            return 'Debes completar correo electrónico y contraseña.';
        }

        $datos = $this->modeloUsuario->verificarCredenciales($correo, $password);

        if ($datos === false) {
            return 'Correo o contraseña incorrectos.';
        }

        $this->modeloUsuario->registrarAcceso((int) $datos['id']);

        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $datos['id'];
        $_SESSION['usuario_nombre'] = $datos['nombre'];
        $_SESSION['usuario_correo'] = $datos['correo'];
        $_SESSION['usuario_es_administrador'] = (int) ($datos['es_administrador'] ?? 0) === 1;

        header('Location: index.php?ruta=dashboard');
        exit;
    }

    /**
     * Paso 1 de "Olvidé mi contraseña". El envío por correo real está pendiente de definir
     * (SMTP/proveedor), así que por ahora el código se devuelve en la respuesta para mostrarlo
     * en el propio modal en vez de enviarlo por email.
     */
    public function recuperarSolicitar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $correo = trim($_POST['correo'] ?? '');

        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['exito' => false, 'mensaje' => 'Ingresa un correo electrónico válido.']);
            return;
        }

        $usuario = $this->modeloUsuario->buscarPorCorreo($correo);

        if ($usuario === false) {
            echo json_encode(['exito' => false, 'mensaje' => 'No encontramos una cuenta con ese correo electrónico.']);
            return;
        }

        $modeloRecuperacion = new RecuperacionPassword();
        $codigo = str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
        $expiraEn = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $modeloRecuperacion->invalidarPendientes((int) $usuario['id']);
        $modeloRecuperacion->crear((int) $usuario['id'], $codigo, $expiraEn);

        echo json_encode([
            'exito' => true,
            'mensaje' => 'Tu código de recuperación es: ' . $codigo . ' (válido 15 minutos).',
            'codigo' => $codigo,
        ]);
    }

    public function recuperarConfirmar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $correo = trim($_POST['correo'] ?? '');
        $codigo = trim($_POST['codigo'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmar = $_POST['confirmar'] ?? '';

        if ($correo === '' || $codigo === '' || $password === '' || $confirmar === '') {
            echo json_encode(['exito' => false, 'mensaje' => 'Completa todos los campos.']);
            return;
        }

        if ($password !== $confirmar) {
            echo json_encode(['exito' => false, 'mensaje' => 'Las contraseñas no coinciden.']);
            return;
        }

        if (mb_strlen($password) < 6) {
            echo json_encode(['exito' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres.']);
            return;
        }

        $usuario = $this->modeloUsuario->buscarPorCorreo($correo);

        if ($usuario === false) {
            echo json_encode(['exito' => false, 'mensaje' => 'No encontramos una cuenta con ese correo electrónico.']);
            return;
        }

        $modeloRecuperacion = new RecuperacionPassword();
        $codigoVigente = $modeloRecuperacion->obtenerVigente((int) $usuario['id'], $codigo);

        if ($codigoVigente === null) {
            echo json_encode(['exito' => false, 'mensaje' => 'El código es inválido o ya expiró.']);
            return;
        }

        $this->modeloUsuario->actualizarPassword((int) $usuario['id'], $password);
        $modeloRecuperacion->marcarUsado((int) $codigoVigente['id']);

        echo json_encode(['exito' => true, 'mensaje' => 'Tu contraseña fue actualizada. Ya puedes iniciar sesión.']);
    }
}
