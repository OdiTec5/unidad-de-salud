<?php

require_once __DIR__ . '/../modelo/ReporteAtencion.php';

class ReporteAtencionControlador
{
    private ReporteAtencion $modeloReporte;

    public function __construct()
    {
        $this->modeloReporte = new ReporteAtencion();
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

        $fecha = $_GET['fecha'] ?? '';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !strtotime($fecha)) {
            $fecha = date('Y-m-d');
        }

        $resumenGeneral = $this->modeloReporte->obtenerResumenGeneral($fecha);
        $resumenPorCategoria = $this->modeloReporte->obtenerResumenPorCategoria($fecha);
        $resumenPorVentanilla = $this->modeloReporte->obtenerResumenPorVentanilla($fecha);
        $detalle = $this->modeloReporte->obtenerDetalleAtendidos($fecha);

        require __DIR__ . '/../vista/reporte-atencion/index.php';
    }
}
