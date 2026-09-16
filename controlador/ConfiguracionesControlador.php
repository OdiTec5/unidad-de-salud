<?php

class ConfiguracionesControlador
{
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

        require __DIR__ . '/../vista/configuraciones/index.php';
    }
}
