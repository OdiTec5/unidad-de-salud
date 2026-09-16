<?php

require_once __DIR__ . '/../modelo/Usuario.php';
require_once __DIR__ . '/../modelo/Ventanilla.php';
require_once __DIR__ . '/../modelo/CategoriaTurnero.php';
require_once __DIR__ . '/../modelo/SubcategoriaTurnero.php';
require_once __DIR__ . '/../modelo/AlcanceTurnero.php';

class UsuarioControlador
{
    private Usuario $modeloUsuario;
    private Ventanilla $modeloVentanilla;
    private CategoriaTurnero $modeloCategoria;
    private SubcategoriaTurnero $modeloSubcategoria;
    private AlcanceTurnero $modeloAlcance;

    public function __construct()
    {
        $this->modeloUsuario = new Usuario();
        $this->modeloVentanilla = new Ventanilla();
        $this->modeloCategoria = new CategoriaTurnero();
        $this->modeloSubcategoria = new SubcategoriaTurnero();
        $this->modeloAlcance = new AlcanceTurnero();
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
            } elseif ($accion === 'actualizar') {
                [$error, $exito] = $this->actualizar();
            } else {
                [$error, $exito] = $this->crear();
            }
        }

        $usuarios = $this->modeloUsuario->obtenerTodos();
        $ventanillas = $this->modeloVentanilla->obtenerActivas();
        $categorias = $this->modeloCategoria->obtenerTodos();
        $subcategoriasPorCategoria = $this->modeloSubcategoria->obtenerAgrupadasPorCategoria();
        $categoriasPorUsuario = $this->modeloAlcance->obtenerTodasCategoriasPorUsuario();
        $subcategoriasPorUsuario = $this->modeloAlcance->obtenerTodasSubcategoriasPorUsuario();

        require __DIR__ . '/../vista/usuarios/index.php';
    }

    private function leerAlcancePost(): array
    {
        $categoriaIds = array_map('intval', $_POST['categorias'] ?? []);
        $subcategoriaIds = array_map('intval', $_POST['subcategorias'] ?? []);

        return [$categoriaIds, $subcategoriaIds];
    }

    private function crear(): array
    {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';
        $esAdministrador = !empty($_POST['es_administrador']);
        $ventanillaId = (int) ($_POST['ventanilla_id'] ?? 0);

        if ($nombre === '' || $correo === '' || $password === '') {
            return ['Todos los campos son obligatorios.', ''];
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['Ingresa un correo electrónico válido.', ''];
        }

        if (mb_strlen($password) < 6) {
            return ['La contraseña debe tener al menos 6 caracteres.', ''];
        }

        if ($this->modeloUsuario->existeCorreo($correo)) {
            return ['Ese correo ya está registrado.', ''];
        }

        $this->modeloUsuario->crear($nombre, $correo, $password, $esAdministrador, $ventanillaId > 0 ? $ventanillaId : null);

        if (!$esAdministrador) {
            $idCreado = (int) $this->modeloUsuario->buscarPorCorreo($correo)['id'];
            [$categoriaIds, $subcategoriaIds] = $this->leerAlcancePost();
            $this->modeloAlcance->guardarAlcance($idCreado, $categoriaIds, $subcategoriaIds);
        }

        return ['', 'Usuario agregado correctamente.'];
    }

    private function actualizar(): array
    {
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $esAdministrador = !empty($_POST['es_administrador']);
        $ventanillaId = (int) ($_POST['ventanilla_id'] ?? 0);

        if ($id <= 0 || $this->modeloUsuario->obtenerPorId($id) === null) {
            return ['El usuario que intentas editar no existe.', ''];
        }

        if ($nombre === '' || $correo === '') {
            return ['Todos los campos son obligatorios.', ''];
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['Ingresa un correo electrónico válido.', ''];
        }

        if ($this->modeloUsuario->existeCorreo($correo, $id)) {
            return ['Ese correo ya está registrado.', ''];
        }

        if ($password !== '' && mb_strlen($password) < 6) {
            return ['La contraseña debe tener al menos 6 caracteres.', ''];
        }

        $this->modeloUsuario->actualizar($id, $nombre, $correo, $esAdministrador, $ventanillaId > 0 ? $ventanillaId : null);

        if ($password !== '') {
            $this->modeloUsuario->actualizarPassword($id, $password);
        }

        [$categoriaIds, $subcategoriaIds] = $this->leerAlcancePost();
        $this->modeloAlcance->guardarAlcance($id, $esAdministrador ? [] : $categoriaIds, $esAdministrador ? [] : $subcategoriaIds);

        return ['', 'Usuario actualizado correctamente.'];
    }

    private function eliminar(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0 && $id !== (int) $_SESSION['usuario_id']) {
            $this->modeloUsuario->eliminar($id);
        }

        header('Location: index.php?ruta=usuarios');
        exit;
    }
}
