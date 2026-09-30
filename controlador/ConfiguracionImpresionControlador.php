<?php

require_once __DIR__ . '/../modelo/ConfiguracionImpresion.php';

class ConfiguracionImpresionControlador
{
    private ConfiguracionImpresion $modeloConfiguracionImpresion;

    public function __construct()
    {
        $this->modeloConfiguracionImpresion = new ConfiguracionImpresion();
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

        $exito = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $anchoPapel = $_POST['ancho_papel'] ?? '80mm';
            $autoImprimir = !empty($_POST['auto_imprimir']);

            $this->modeloConfiguracionImpresion->actualizar($anchoPapel, $autoImprimir);
            $exito = 'Configuración de impresión guardada correctamente.';
        }

        $configuracionImpresion = $this->modeloConfiguracionImpresion->obtener();

        require __DIR__ . '/../vista/configuracion-impresion/index.php';
    }
}
