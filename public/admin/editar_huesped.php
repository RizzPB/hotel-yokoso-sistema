<?php
// public/admin/editar_huesped.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header("Location: ver_huespedes.php");
    exit;
}

// Obtener huésped
$stmt = $pdo->prepare("SELECT * FROM Huesped WHERE idHuesped = ?");
$stmt->execute([$id]);
$huesped = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$huesped) {
    header("Location: ver_huespedes.php");
    exit;
}

$mensaje = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre               = trim($_POST['nombre'] ?? '');
    $apellido             = trim($_POST['apellido'] ?? '');
    $tipoDocumento        = $_POST['tipoDocumento'] ?? '';
    $nroDocumento         = trim($_POST['nroDocumento'] ?? '');
    $procedencia          = trim($_POST['procedencia'] ?? '');
    $email                = trim($_POST['email'] ?? '');
    $telefono             = trim($_POST['telefono'] ?? '');
    $motivoVisita         = trim($_POST['motivoVisita'] ?? '');
    $preferenciaAlimentaria = trim($_POST['preferenciaAlimentaria'] ?? '');
    $activo               = isset($_POST['activo']) ? 1 : 0;

    if (empty($nombre) || empty($apellido) || empty($tipoDocumento) || empty($nroDocumento)) {
        $error = "Los campos nombre, apellido, tipo y número de documento son obligatorios.";
    } else {
        // Verificar duplicado de documento
        $stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE nroDocumento = ? AND idHuesped != ?");
        $stmt->execute([$nroDocumento, $id]);
        if ($stmt->fetch()) {
            $error = "Ya existe otro huésped con ese número de documento.";
        } else {
            $stmt = $pdo->prepare("
                UPDATE Huesped SET 
                    nombre = ?, apellido = ?, tipoDocumento = ?, nroDocumento = ?, 
                    procedencia = ?, email = ?, telefono = ?, motivoVisita = ?, 
                    preferenciaAlimentaria = ?, activo = ?
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
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-rojo fw-bold">
            Editar Huésped
        </h2>
        <a href="ver_huespedes.php" class="btn btn-outline-secondary btn-lg rounded-pill px-5">
            Volver a Huéspedes
        </a>
    </div>

    ' . ($mensaje ? '<div class="alert alert-success text-center mx-auto mb-4" style="max-width:900px;">Éxito!<br>' . $mensaje . '</div>' : '') . '
    ' . ($error ? '<div class="alert alert-danger text-center mx-auto mb-4" style="max-width:900px;">Error:<br>' . $error . '</div>' : '') . '

    <!-- FORMULARIO FIJO -->
    <div class="row justify-content-center">
        <div class="col-xl-11 col-xxl-10">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5 p-lg-6">

                    <div class="text-center mb-5">
                        <h3 class="text-rojo fw-bold">
                            ' . htmlspecialchars($huesped['nombre'] . ' ' . $huesped['apellido']) . '
                        </h3>
                        <p class="text-muted">ID: #' . $huesped['idHuesped'] . ' • Documento: ' . htmlspecialchars($huesped['nroDocumento']) . '</p>
                    </div>

                    <form method="POST">

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Nombre *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="nombre" value="'.htmlspecialchars($huesped['nombre']).'" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Apellido *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="apellido" value="'.htmlspecialchars($huesped['apellido']).'" required>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Tipo Documento *</label>
                                <select class="form-select form-select-lg rounded-pill" name="tipoDocumento" required>
                                    <option value="DNI"      '.($huesped['tipoDocumento']==='DNI'?'selected':'').'>DNI</option>
                                    <option value="Pasaporte"'.($huesped['tipoDocumento']==='Pasaporte'?'selected':'').'>Pasaporte</option>
                                    <option value="Carnet"   '.($huesped['tipoDocumento']==='Carnet'?'selected':'').'>Carnet Extranjeria</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Nro. Documento *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="nroDocumento" value="'.htmlspecialchars($huesped['nroDocumento']).'" required>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Procedencia</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="procedencia" value="'.htmlspecialchars($huesped['procedencia']).'">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Email</label>
                                <input type="email" class="form-control form-control-lg rounded-pill" name="email" value="'.htmlspecialchars($huesped['email']).'">
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Teléfono</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="telefono" value="'.htmlspecialchars($huesped['telefono']).'">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Motivo de Visita</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="motivoVisita" value="'.htmlspecialchars($huesped['motivoVisita']).'">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark">Preferencias Alimentarias</label>
                            <textarea class="form-control form-control-lg rounded-4" rows="3" name="preferenciaAlimentaria" placeholder="Ej. Vegetariano, sin gluten, alérgico a mariscos...">'.htmlspecialchars($huesped['preferenciaAlimentaria']).'</textarea>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark">Estado del Huésped</label>
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" name="activo" id="activoSwitch" '.($huesped['activo'] ? 'checked' : '').'>
                                <label class="form-check-label fs-5" for="activoSwitch">
                                    <span class="text-success">Activo</span> / <span class="text-danger">Inactivo</span>
                                </label>
                            </div>
                        </div>

                        <!-- BOTONES FINALES -->
                        <div class="pt-4 border-top text-end">
                            <a href="ver_huespedes.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill me-3">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-yokoso btn-lg px-5 rounded-pill shadow-lg">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
';

include 'plantilla_admin.php';
?>