<?php
// usuarios/EncargadoDeAlberca/index.php

// 1. Llamamos a nuestro nuevo guardia de seguridad
require_once __DIR__ . '/../../controllers/DashboardController.php';
require_once __DIR__ . '/../../controllers/EncargadoDeAlberca/EncargadoController.php';

$dashboard = new DashboardController();

// 2. Verificamos que el usuario tenga el rol 2 (Encargado de Alberca)
$usuario = $dashboard->verificarAcceso(2);

// 3. Inicializamos el controlador pasándole el ID del encargado
$controller = new EncargadoController($usuario['id']);

$mensaje = '';
$tipo_mensaje = '';

// 4. Procesar acciones (botones, formularios)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = $controller->procesarAccion($action, $_POST);
    $mensaje = $result['message'];
    $tipo_mensaje = $result['success'] ? 'success' : 'error';
}

// 5. Obtener todos los datos para llenar el panel visual
$data = $controller->getDashboardData();

// 6. Extraer las variables para que la vista las pueda usar
extract($data);

// 7. Mostrar la pantalla
include __DIR__ . '/../../views/EncargadoDeAlberca/index.php';
?>