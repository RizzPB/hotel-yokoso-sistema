<?php
// public/vistas/admin/eliminar_habitacion.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ver_habitaciones.php");
    exit;
}

// Obtener la habitación para confirmar
$stmt = $pdo->prepare("SELECT idHabitacion, numero, tipo FROM Habitacion WHERE idHabitacion = ?");
$stmt->execute([$id]);
$habitacion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$habitacion) {
    header("Location: ver_habitaciones.php");
    exit;
}

$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cambiar estado a eliminada (eliminar lógico)
    $stmt = $pdo->prepare("UPDATE Habitacion SET estado = 'eliminada' WHERE idHabitacion = ?");
    if ($stmt->execute([$id])) {
        $mensaje = "Habitación eliminada exitosamente (marcada como inactiva).";
    } else {
        $error = "Error al eliminar la habitación.";
    }
}

$titulo_pagina = "Eliminar Habitación - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Eliminar Habitación</h2>
            <a href="ver_habitaciones.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Habitaciones
            </a>
        </div>
    </div>

    ' . (isset($mensaje) ? '<div class="alert alert-success">' . htmlspecialchars($mensaje) . '</div>' : '') . '
    ' . (isset($error) ? '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>' : '') . '

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h4 class="text-center text-rojo">¿Estás seguro de que deseas eliminar esta habitación?</h4>
            <p class="text-center">Esta acción no se puede deshacer. La habitación quedará como inactiva.</p>

            <div class="text-center my-4">
                <p><strong>Número:</strong> ' . htmlspecialchars($habitacion['numero']) . '</p>
                <p><strong>Tipo:</strong> ' . htmlspecialchars($habitacion['tipo']) . '</p>
            </div>

            <form method="POST" class="d-flex justify-content-center gap-3">
                <a href="ver_habitaciones.php" class="btn btn-cancelar">Cancelar</a>
                <button type="submit" class="btn btn-danger btn-lg shadow-sm">
                    <i class="fas fa-trash me-2"></i>Eliminar
                </button>
            </form>
        </div>
    </div>
';

include 'plantilla_admin.php';
?>