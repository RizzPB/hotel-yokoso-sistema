<?php
// public/vistas/admin/editar_huesped.php

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

// Obtener el huésped actual
$stmt = $pdo->prepare("SELECT * FROM Huesped WHERE idHuesped = ?");
$stmt->execute([$id]);
$huesped = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$huesped) {
    header("Location: ver_huespedes.php");
    exit;
}

$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $tipoDocumento = $_POST['tipoDocumento'];
    $nroDocumento = trim($_POST['nroDocumento']);
    $procedencia = trim($_POST['procedencia']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $motivoVisita = trim($_POST['motivoVisita']);
    $preferenciaAlimentaria = trim($_POST['preferenciaAlimentaria']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (empty($nombre) || empty($apellido) || empty($tipoDocumento) || empty($nroDocumento)) {
        $error = "Los campos nombre, apellido, tipo y número de documento son obligatorios.";
    } else {
        // Verificar si ya existe otro huésped con el mismo documento
        $stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE nroDocumento = ? AND idHuesped != ?");
        $stmt->execute([$nroDocumento, $id]);
        if ($stmt->fetch()) {
            $error = "Ya existe otro huésped con ese número de documento.";
        } else {
            $stmt = $pdo->prepare("
                UPDATE Huesped
                SET nombre = ?, apellido = ?, tipoDocumento = ?, nroDocumento = ?, procedencia = ?, email = ?, telefono = ?, motivoVisita = ?, preferenciaAlimentaria = ?, activo = ?
                WHERE idHuesped = ?
            ");
            if ($stmt->execute([$nombre, $apellido, $tipoDocumento, $nroDocumento, $procedencia, $email, $telefono, $motivoVisita, $preferenciaAlimentaria, $activo, $id])) {
                $mensaje = "Huésped actualizado exitosamente.";
                // Recargar datos
                $stmt = $pdo->prepare("SELECT * FROM Huesped WHERE idHuesped = ?");
                $stmt->execute([$id]);
                $huesped = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Error al actualizar el huésped.";
            }
        }
    }
}

$titulo_pagina = "Editar Huésped - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Editar Huésped: ' . htmlspecialchars($huesped['nombre'] . ' ' . $huesped['apellido']) . '</h2>
            <a href="ver_huespedes.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Huéspedes
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
                    <input type="text" class="form-control" name="nombre" value="' . htmlspecialchars($huesped['nombre']) . '" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apellido *</label>
                    <input type="text" class="form-control" name="apellido" value="' . htmlspecialchars($huesped['apellido']) . '" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Tipo de Documento *</label>
                    <select class="form-select" name="tipoDocumento" required>
                        <option value="DNI" ' . ($huesped['tipoDocumento'] === 'DNI' ? 'selected' : '') . '>DNI</option>
                        <option value="Pasaporte" ' . ($huesped['tipoDocumento'] === 'Pasaporte' ? 'selected' : '') . '>Pasaporte</option>
                        <option value="Carnet" ' . ($huesped['tipoDocumento'] === 'Carnet' ? 'selected' : '') . '>Carnet</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Número de Documento *</label>
                    <input type="text" class="form-control" name="nroDocumento" value="' . htmlspecialchars($huesped['nroDocumento']) . '" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Procedencia</label>
                    <input type="text" class="form-control" name="procedencia" value="' . htmlspecialchars($huesped['procedencia']) . '">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="' . htmlspecialchars($huesped['email']) . '">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" name="telefono" value="' . htmlspecialchars($huesped['telefono']) . '">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Motivo de Visita</label>
                    <input type="text" class="form-control" name="motivoVisita" value="' . htmlspecialchars($huesped['motivoVisita']) . '">
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Preferencias Alimentarias</label>
                <textarea class="form-control" rows="2" name="preferenciaAlimentaria">' . htmlspecialchars($huesped['preferenciaAlimentaria']) . '</textarea>
            </div>

            <div class="mt-3">
                <label class="form-label">Estado</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="activo" value="1" id="activoSwitch" ' . ($huesped['activo'] ? 'checked' : '') . '>
                    <label class="form-check-label" for="activoSwitch">Activo</label>
                </div>
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="ver_huespedes.php" class="btn btn-cancelar me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
';

include 'plantilla_admin.php';
?>