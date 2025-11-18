<?php
// public/vistas/admin/editar_paquete.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ver_paquetes.php");
    exit;
}

// Obtener el paquete actual
$stmt = $pdo->prepare("SELECT * FROM PaqueteTuristico WHERE idPaquete = ?");
$stmt->execute([$id]);
$paquete = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paquete) {
    header("Location: ver_paquetes.php");
    exit;
}

$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $duracionDias = $_POST['duracionDias'];
    $incluye = trim($_POST['incluye']);
    $noIncluye = trim($_POST['noIncluye']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (empty($nombre) || empty($descripcion) || empty($precio) || empty($duracionDias)) {
        $error = "Los campos nombre, descripción, precio y duración son obligatorios.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE PaqueteTuristico
            SET nombre = ?, descripcion = ?, precio = ?, duracionDias = ?, incluye = ?, noIncluye = ?, activo = ?
            WHERE idPaquete = ?
        ");
        if ($stmt->execute([$nombre, $descripcion, $precio, $duracionDias, $incluye, $noIncluye, $activo, $id])) {
            $mensaje = "Paquete turístico actualizado exitosamente.";
            // Recargar datos
            $stmt = $pdo->prepare("SELECT * FROM PaqueteTuristico WHERE idPaquete = ?");
            $stmt->execute([$id]);
            $paquete = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Error al actualizar el paquete.";
        }
    }
}

$titulo_pagina = "Editar Paquete Turístico - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Editar Paquete Turístico: ' . htmlspecialchars($paquete['nombre']) . '</h2>
            <a href="ver_paquetes.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Paquetes
            </a>
        </div>
    </div>

    ' . (isset($mensaje) ? '<div class="alert alert-success">' . htmlspecialchars($mensaje) . '</div>' : '') . '
    ' . (isset($error) ? '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>' : '') . '

    <div class="reserva-form-container">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input type="text" class="form-control" name="nombre" value="' . htmlspecialchars($paquete['nombre']) . '" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Precio (Bs.) *</label>
                    <input type="number" class="form-control" name="precio" step="0.01" value="' . htmlspecialchars($paquete['precio']) . '" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Duración (días) *</label>
                    <input type="number" class="form-control" name="duracionDias" value="' . htmlspecialchars($paquete['duracionDias']) . '" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Estado</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="activo" value="1" id="activoSwitch" ' . ($paquete['activo'] ? 'checked' : '') . '>
                        <label class="form-check-label" for="activoSwitch">Activo</label>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Descripción *</label>
                <textarea class="form-control" rows="3" name="descripcion" required>' . htmlspecialchars($paquete['descripcion']) . '</textarea>
            </div>

            <div class="mt-3">
                <label class="form-label">Incluye</label>
                <textarea class="form-control" rows="2" name="incluye">' . htmlspecialchars($paquete['incluye'] ?? '') . '</textarea>
            </div>

            <div class="mt-3">
                <label class="form-label">No Incluye</label>
                <textarea class="form-control" rows="2" name="noIncluye">' . htmlspecialchars($paquete['noIncluye'] ?? '') . '</textarea>
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="ver_paquetes.php" class="btn btn-cancelar me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
';

include 'plantilla_admin.php';
?>