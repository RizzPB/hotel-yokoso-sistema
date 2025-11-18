<?php
// public/vistas/admin/eliminar_huesped.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ver_huespedes.php");
    exit;
}

// Obtener el huésped para confirmar
$stmt = $pdo->prepare("SELECT idHuesped, nombre, apellido FROM Huesped WHERE idHuesped = ?");
$stmt->execute([$id]);
$huesped = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$huesped) {
    header("Location: ver_huespedes.php");
    exit;
}

$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cambiar estado a inactivo (eliminar lógico)
    $stmt = $pdo->prepare("UPDATE Huesped SET activo = 0 WHERE idHuesped = ?");
    if ($stmt->execute([$id])) {
        $mensaje = "Huésped eliminado exitosamente (marcado como inactivo).";
    } else {
        $error = "Error al eliminar el huésped.";
    }
}

$titulo_pagina = "Eliminar Huésped - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Eliminar Huésped</h2>
            <a href="ver_huespedes.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Huéspedes
            </a>
        </div>
    </div>

    ' . (isset($mensaje) ? '<div class="alert alert-success">' . htmlspecialchars($mensaje) . '</div>' : '') . '
    ' . (isset($error) ? '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>' : '') . '

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h4 class="text-center text-rojo">¿Estás seguro de que deseas eliminar a este huésped?</h4>
            <p class="text-center">Esta acción no se puede deshacer. El huésped quedará como inactivo.</p>

            <div class="text-center my-4">
                <p><strong>Nombre:</strong> ' . htmlspecialchars($huesped['nombre'] . ' ' . $huesped['apellido']) . '</p>
            </div>

            <form method="POST" class="d-flex justify-content-center gap-3">
                <a href="ver_huespedes.php" class="btn btn-cancelar">Cancelar</a>
                <button type="submit" class="btn btn-danger btn-lg shadow-sm">
                    <i class="fas fa-trash me-2"></i>Eliminar
                </button>
            </form>
        </div>
    </div>
';

include 'plantilla_admin.php';
?>