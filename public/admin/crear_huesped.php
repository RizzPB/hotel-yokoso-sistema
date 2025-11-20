 <?php
// public/vistas/admin/crear_huesped.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$error = null;
$mensaje = null;

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

    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($tipoDocumento) || empty($nroDocumento)) {
        $error = "Los campos nombre, apellido, tipo y número de documento son obligatorios.";
    } else {
        // Verificar si ya existe un huésped con ese documento
        $stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE nroDocumento = ?");
        $stmt->execute([$nroDocumento]);
        if ($stmt->fetch()) {
            $error = "Ya existe un huésped con ese número de documento.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO Huesped (nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$nombre, $apellido, $tipoDocumento, $nroDocumento, $procedencia, $email, $telefono, $motivoVisita, $preferenciaAlimentaria, $activo])) {
                $mensaje = "Huésped registrado exitosamente.";
            } else {
                $error = "Error al registrar el huésped.";
            }
        }
    }
}

$titulo_pagina = "Registrar Huésped - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Registrar Nuevo Huésped</h2>
            <a href="ver_huespedes.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Huéspedes
            </a>
        </div>
    </div>

    ' . (!empty($mensaje) ? '<div class="alert alert-alert-success alert-dismissible fade show" role="alert">
        <strong>Éxito!</strong> ' . htmlspecialchars($mensaje) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>' : '') . '

    ' . (!empty($error) ? '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error:</strong> ' . htmlspecialchars($error) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>' : '') . '
    
    <div class="reserva-form-container">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input type="text" class="form-control" name="nombre" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apellido *</label>
                    <input type="text" class="form-control" name="apellido" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Tipo de Documento *</label>
                    <select class="form-select" name="tipoDocumento" required>
                        <option value="">Seleccionar...</option>
                        <option value="DNI">DNI</option>
                        <option value="Pasaporte">Pasaporte</option>
                        <option value="Carnet">Carnet</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Número de Documento *</label>
                    <input type="text" class="form-control" name="nroDocumento" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Procedencia</label>
                    <input type="text" class="form-control" name="procedencia">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" name="telefono">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Motivo de Visita</label>
                    <input type="text" class="form-control" name="motivoVisita">
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Preferencias Alimentarias</label>
                <textarea class="form-control" rows="2" name="preferenciaAlimentaria" placeholder="Ej. Vegetariano, sin gluten, alérgico a la leche..."></textarea>
            </div>

            <div class="mt-3">
                <label class="form-label">Estado</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="activo" value="1" id="activoSwitch" checked>
                    <label class="form-check-label" for="activoSwitch">Activo</label>
                </div>
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="ver_huespedes.php" class="btn btn-cancelar me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Guardar Huésped
                </button>
            </div>
        </form>
    </div>
';

include 'plantilla_admin.php';
?>