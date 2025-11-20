<?php
// public/recepcionista/editar_huesped.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'ver_huespedes'; // Resalta "Ver Huéspedes" en el menú
require_once __DIR__ . '/../../config/database.php';

$idHuesped = $_GET['id'] ?? null;
if (!$idHuesped || !is_numeric($idHuesped)) {
    header("Location: ver_huespedes.php");
    exit;
}

// Obtener datos del huésped
$stmt = $pdo->prepare("SELECT * FROM Huesped WHERE idHuesped = ? AND activo = 1");
$stmt->execute([$idHuesped]);
$huesped = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$huesped) {
    header("Location: ver_huespedes.php");
    exit;
}

$mensaje = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $tipoDocumento = $_POST['tipoDocumento'] ?? '';
    $nroDocumento = trim($_POST['nroDocumento'] ?? '');
    $procedencia = trim($_POST['procedencia'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $motivoVisita = trim($_POST['motivoVisita'] ?? '');
    $preferenciaAlimentaria = trim($_POST['preferenciaAlimentaria'] ?? '');

    if (empty($nombre) || empty($apellido) || empty($tipoDocumento) || empty($nroDocumento)) {
        $error = "Los campos obligatorios no pueden estar vacíos.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE Huesped SET 
                nombre = ?, apellido = ?, tipoDocumento = ?, nroDocumento = ?,
                procedencia = ?, email = ?, telefono = ?, motivoVisita = ?, preferenciaAlimentaria = ?
            WHERE idHuesped = ?
        ");
        if ($stmt->execute([$nombre, $apellido, $tipoDocumento, $nroDocumento, $procedencia, $email, $telefono, $motivoVisita, $preferenciaAlimentaria, $idHuesped])) {
            $mensaje = "Huésped actualizado correctamente.";
            // Recargar datos actualizados
            $stmt = $pdo->prepare("SELECT * FROM Huesped WHERE idHuesped = ?");
            $stmt->execute([$idHuesped]);
            $huesped = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Error al guardar los cambios.";
        }
    }
}

$titulo_pagina = "Editar Huésped - Hotel Yokoso";

$contenido_principal = '
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-rojo fw-bold">
            <i class="fas fa-user-edit me-3"></i>
            Editar Huésped #'. htmlspecialchars($huesped['idHuesped']) .'
        </h2>
        <a href="ver_huespedes.php" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    '. ($mensaje ? '<div class="alert alert-success text-center mx-auto" style="max-width:900px;"><i class="fas fa-check-circle fa-2x"></i><br>'.htmlspecialchars($mensaje).'</div>' : '') .'
    '. ($error ? '<div class="alert alert-danger text-center mx-auto" style="max-width:900px;"><i class="fas fa-times-circle fa-2x"></i><br>'.htmlspecialchars($error).'</div>' : '') .'

    <!-- FORMULARIO FIJO, CENTRADO Y HERMOSO -->
    <div class="row justify-content-center">
        <div class="col-xl-10 col-xxl-9">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5 p-lg-6">

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
                                    <option value="Carnet" '.($huesped['tipoDocumento']==='Carnet' ? 'selected' : '').'>Carnet</option>
                                    <option value="DNI" '.($huesped['tipoDocumento']==='DNI' ? 'selected' : '').'>DNI</option>
                                    <option value="Pasaporte" '.($huesped['tipoDocumento']==='Pasaporte' ? 'selected' : '').'>Pasaporte</option>
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
                            <textarea class="form-control form-control-lg rounded-4" rows="4" name="preferenciaAlimentaria" placeholder="Ej. Vegetariano, sin gluten...">'.htmlspecialchars($huesped['preferenciaAlimentaria']).'</textarea>
                        </div>

                        <!-- BOTONES FINALES FIJOS Y PERFECTOS -->
                        <div class="pt-4 border-top text-end">
                            <a href="ver_huespedes.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill me-3">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-yokoso btn-lg px-5 rounded-pill shadow-lg">
                                <i class="fas fa-save fa-lg me-2"></i>
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

include 'plantilla_recepcionista.php';
?>