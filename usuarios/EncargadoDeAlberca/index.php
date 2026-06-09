<?php
// usuarios/EncargadoDeAlberca/index.php
session_start();

require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/EncargadoDeAlberca/EncargadoController.php';

$auth = new AuthController();
$auth->requireRol(1); // Solo encargado de alberca

$usuario = $auth->getCurrentUser();
$controller = new EncargadoController($usuario['id']);

$mensaje = '';
$tipo_mensaje = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = $controller->procesarAccion($action, $_POST);
    $mensaje = $result['message'];
    $tipo_mensaje = $result['success'] ? 'success' : 'error';
}

// Obtener datos del dashboard
$data = $controller->getDashboardData();

// Extraer variables para la vista
extract($data);

// Incluir la vista (el CSS está DENTRO de la vista, no aquí)
include __DIR__ . '/../../views/EncargadoDeAlberca/index.php';
?>