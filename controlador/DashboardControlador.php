<?php

class DashboardControlador
{
    public function index(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?ruta=login');
            exit;
        }

        $tituloPagina = 'Inicio';

        require __DIR__ . '/../vista/dashboard/dashboard.php';
    }
}
