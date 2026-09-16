<?php

session_start();

require_once __DIR__ . '/controlador/LoginControlador.php';
require_once __DIR__ . '/controlador/DashboardControlador.php';
require_once __DIR__ . '/controlador/ConfiguracionesControlador.php';
require_once __DIR__ . '/controlador/UsuarioControlador.php';
require_once __DIR__ . '/controlador/CategoriaTurneroControlador.php';
require_once __DIR__ . '/controlador/BeneficiarioControlador.php';
require_once __DIR__ . '/controlador/TurneroPublicoControlador.php';
require_once __DIR__ . '/controlador/GestionTurnosControlador.php';
require_once __DIR__ . '/controlador/VentanillaControlador.php';

$ruta = $_GET['ruta'] ?? 'login';

switch ($ruta) {
    case 'dashboard':
        (new DashboardControlador())->index();
        break;

    case 'configuraciones':
        (new ConfiguracionesControlador())->index();
        break;

    case 'usuarios':
        (new UsuarioControlador())->index();
        break;

    case 'categorias-turnero':
        (new CategoriaTurneroControlador())->index();
        break;

    case 'beneficiarios':
        (new BeneficiarioControlador())->index();
        break;

    case 'ventanillas':
        (new VentanillaControlador())->index();
        break;

    case 'turnos':
        (new GestionTurnosControlador())->index();
        break;

    case 'turno-solicitar':
        (new TurneroPublicoControlador())->solicitar();
        break;

    case 'turno-pantalla':
        (new TurneroPublicoControlador())->pantalla();
        break;

    case 'turno-pantalla-datos':
        (new TurneroPublicoControlador())->pantallaDatos();
        break;

    case 'logout':
        require __DIR__ . '/vista/login/logout.php';
        break;

    case 'recuperar-password-solicitar':
        (new LoginControlador())->recuperarSolicitar();
        break;

    case 'recuperar-password-confirmar':
        (new LoginControlador())->recuperarConfirmar();
        break;

    case 'login':
    default:
        (new LoginControlador())->index();
        break;
}
