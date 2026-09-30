<?php

require_once __DIR__ . '/../modelo/ConfiguracionVoz.php';

class ConfiguracionVozControlador
{
    private ConfiguracionVoz $modeloConfiguracionVoz;

    public function __construct()
    {
        $this->modeloConfiguracionVoz = new ConfiguracionVoz();
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
            [$error, $exito] = $this->guardar();
        }

        $configuracionVoz = $this->modeloConfiguracionVoz->obtener();

        require __DIR__ . '/../vista/configuracion-voz/index.php';
    }

    private function guardar(): array
    {
        $vozNombre = trim($_POST['voz_nombre'] ?? '');
        $idioma = trim($_POST['idioma'] ?? '') ?: 'es-ES';
        $velocidad = (float) ($_POST['velocidad'] ?? 0.95);
        $tono = (float) ($_POST['tono'] ?? 1.0);
        $volumen = (float) ($_POST['volumen'] ?? 1.0);

        $velocidad = max(0.5, min(2.0, $velocidad));
        $tono = max(0.0, min(2.0, $tono));
        $volumen = max(0.0, min(1.0, $volumen));

        $this->modeloConfiguracionVoz->actualizar($vozNombre !== '' ? $vozNombre : null, $idioma, $velocidad, $tono, $volumen);

        return ['', 'Configuración de voz guardada correctamente.'];
    }
}
