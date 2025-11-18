<?php
// public/vistas/admin/eliminar_empleado.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

// Obtener ID del empleado a eliminar
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ver_empleados.php");
    exit;
}

// Obtener el empleado para confirmar
$stmt = $pdo->prepare("
    SELECT e.idEmpleado, e.nombre, e.apellido, u.nombreUsuario
    FROM Empleado e
    JOIN Usuario u ON e.idUsuario = u.idUsuario
    WHERE e.idEmpleado = ?
");
$stmt->execute([$id]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    header("Location: ver_empleados.php");
    exit;
}

$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cambiar estado del usuario a inactivo (no eliminar físicamente)
    $stmt = $pdo->prepare("UPDATE Usuario SET activo = 0 WHERE idUsuario = (SELECT idUsuario FROM Empleado WHERE idEmpleado = ?)");
    if ($stmt->execute([$id])) {
        $mensaje = "Empleado eliminado exitosamente (marcado como inactivo).";
    } else {
        $error = "Error al eliminar el empleado.";
    }
}

$titulo_pagina = "Eliminar Empleado - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Eliminar Empleado</h2>
            <a href="ver_empleados.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Empleados
            </a>
        </div>
    </div>

    ' . (isset($mensaje) ? '<div class="alert alert-success">' . htmlspecialchars($mensaje) . '</div>' : '') . '
    ' . (isset($error) ? '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>' : '') . '

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h4 class="text-center text-rojo">¿Estás seguro de que deseas eliminar a este empleado?</h4>
            <p class="text-center">Esta acción no se puede deshacer. El empleado quedará como inactivo.</p>

            <div class="text-center my-4">
                <p><strong>Nombre:</strong> ' . htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']) . '</p>
                <p><strong>Usuario:</strong> ' . htmlspecialchars($empleado['nombreUsuario']) . '</p>
            </div>

            <form method="POST" class="d-flex justify-content-center gap-3">
                <a href="ver_empleados.php" class="btn btn-cancelar">Cancelar</a>
                <button type="submit" class="btn btn-danger btn-lg shadow-sm">
                    <i class="fas fa-trash me-2"></i>Eliminar
                </button>
            </form>
        </div>
    </div>
';

include 'plantilla_admin.php';
?>