<?php
// public/admin/editar_paquete.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header("Location: ver_paquetes.php");
    exit;
}

// Obtener paquete
$stmt = $pdo->prepare("SELECT * FROM PaqueteTuristico WHERE idPaquete = ?");
$stmt->execute([$id]);
$paquete = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paquete) {
    header("Location: ver_paquetes.php");
    exit;
}

$mensaje = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre       = trim($_POST['nombre'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $precio       = $_POST['precio'] ?? 0;
    $duracionDias = $_POST['duracionDias'] ?? 0;
    $activo       = isset($_POST['activo']) ? 1 : 0;

    if (empty($nombre) || empty($descripcion) || $precio <= 0 || $duracionDias <= 0) {
        $error = "Nombre, descripción, precio y duración son obligatorios.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE PaqueteTuristico 
            SET nombre = ?, descripcion = ?, precio = ?, duracionDias = ?, activo = ? 
            WHERE idPaquete = ?
        ");
        if ($stmt->execute([$nombre, $descripcion, $precio, $duracionDias, $activo, $id])) {
            $mensaje = "Paquete actualizado exitosamente.";
            $stmt = $pdo->prepare("SELECT * FROM PaqueteTuristico WHERE idPaquete = ?");
            $stmt->execute([$id]);
            $paquete = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Error al guardar los cambios.";
        }
    }
}

$titulo_pagina = "Editar Paquete Turístico - Hotel Yokoso";

$contenido_principal = '
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-rojo fw-bold">
            Editar Paquete Turístico
        </h2>
        <a href="ver_paquetes.php" class="btn btn-outline-secondary btn-lg rounded-pill px-5">
            Volver a Paquetes
        </a>
    </div>

    ' . ($mensaje ? '<div class="alert alert-success text-center mx-auto mb-4" style="max-width:900px;">Paquete actualizado</div>' : '') . '
    ' . ($error ? '<div class="alert alert-danger text-center mx-auto mb-4" style="max-width:900px;">Error: ' . htmlspecialchars($error) . '</div>' : '') . '

    <!-- FORMULARIO FIJO, ANCHO Y SIN ESPACIOS GIGANTES -->
    <div class="row justify-content-center">
        <div class="col-xl-11 col-xxl-10">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5 p-lg-6">

                    <div class="text-center mb-5">
                        <h3 class="text-rojo fw-bold">' . htmlspecialchars($paquete['nombre']) . '</h3>
                        <p class="text-muted">ID #' . $paquete['idPaquete'] . ' • ' . $paquete['duracionDias'] . ' día' . ($paquete['duracionDias'] > 1 ? 's' : '') . '</p>
                    </div>

                    <form method="POST">

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Nombre del Paquete *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="nombre" value="'.htmlspecialchars($paquete['nombre']).'" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Precio (Bs.) *</label>
                                <input type="number" step="0.01" min="0" class="form-control form-control-lg rounded-pill" name="precio" value="'.number_format($paquete['precio'], 2, '.', '').'" required>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Duración (días) *</label>
                                <input type="number" min="1" class="form-control form-control-lg rounded-pill" name="duracionDias" value="'.$paquete['duracionDias'].'" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Estado del Paquete</label>
                                <div class="form-check form-switch form-switch-lg mt-2">
                                    <input class="form-check-input" type="checkbox" name="activo" id="activoSwitch" '.($paquete['activo'] ? 'checked' : '').'>
                                    <label class="form-check-label fs-5" for="activoSwitch">
                                        '.($paquete['activo'] ? '<span class="text-success">Activo</span>' : '<span class="text-danger">Inactivo</span>').'
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark">Descripción *</label>
                            <textarea class="form-control form-control-lg rounded-4" rows="5" name="descripcion" required>'.htmlspecialchars($paquete['descripcion']).'</textarea>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Incluye</label>
                                <textarea class="form-control form-control-lg rounded-4" rows="5" name="incluye" placeholder="Ej. Desayuno buffet, traslado aeropuerto, tour guiado...">'.htmlspecialchars($paquete['incluye'] ?? '').'</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">No Incluye</label>
                                <textarea class="form-control form-control-lg rounded-4" rows="5" name="noIncluye" placeholder="Ej. Propinas, bebidas alcohólicas, seguros...">'.htmlspecialchars($paquete['noIncluye'] ?? '').'</textarea>
                            </div>
                        </div>

                        <!-- BOTONES FINALES -->
                        <div class="pt-4 border-top text-end">
                            <a href="ver_paquetes.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill me-3">
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
include __DIR__ . '/../layout.php';
?>