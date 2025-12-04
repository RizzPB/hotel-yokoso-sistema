<?php
// public/admin/crear_huesped.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Recuperar valores enviados (para mantener el formulario en caso de error)
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$tipoDocumento = $_POST['tipoDocumento'] ?? '';
$nroDocumento = $_POST['nroDocumento'] ?? '';
$procedencia = $_POST['procedencia'] ?? '';
$email = $_POST['email'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$motivoVisita = $_POST['motivoVisita'] ?? '';
$preferenciaAlimentaria = $_POST['preferenciaAlimentaria'] ?? '';
$activo = isset($_POST['activo']) ? 1 : 0;

// Errores por campo
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar nombre
    if (empty(trim($nombre))) {
        $errores['nombre'] = "El nombre es obligatorio.";
    } elseif (!preg_match('/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]+$/', trim($nombre))) {
        $errores['nombre'] = "El nombre solo debe contener letras y espacios.";
    }

    // Validar apellido
    if (empty(trim($apellido))) {
        $errores['apellido'] = "El apellido es obligatorio.";
    } elseif (!preg_match('/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]+$/', trim($apellido))) {
        $errores['apellido'] = "El apellido solo debe contener letras y espacios.";
    }

    // Validar tipo y número de documento
    if (empty($tipoDocumento)) {
        $errores['tipoDocumento'] = "Selecciona un tipo de documento.";
    }
    if (empty(trim($nroDocumento))) {
        $errores['nroDocumento'] = "El número de documento es obligatorio.";
    } elseif (strlen(trim($nroDocumento)) < 5) {
        $errores['nroDocumento'] = "El número de documento debe tener al menos 5 caracteres.";
    }

    // Validar email (si se proporciona)
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = "Ingresa un correo electrónico válido.";
    }

    // Validar teléfono (si se proporciona)
    if (!empty($telefono)) {
        $telefonoLimpio = preg_replace('/[^0-9+\-\s]/', '', $telefono);
        if (strlen($telefonoLimpio) < 7) {
            $errores['telefono'] = "El teléfono debe tener al menos 7 dígitos.";
        }
    }

    // Validar preferencias alimentarias (solo letras y espacios)
    if (!empty($preferenciaAlimentaria) && !preg_match('/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ,]+$/u', $preferenciaAlimentaria)) {
        $errores['preferenciaAlimentaria'] = "Solo se permiten letras, espacios y comas.";
    }

    // Si no hay errores individuales, verificar duplicado de documento
    if (empty($errores) && !empty(trim($nroDocumento))) {
        $stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE nroDocumento = ?");
        $stmt->execute([trim($nroDocumento)]);
        if ($stmt->fetch()) {
            $errores['nroDocumento'] = "Ya existe un huésped con este número de documento.";
        } else {
            // Registrar huésped
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO Huesped (
                        nombre, apellido, tipoDocumento, nroDocumento, 
                        procedencia, email, telefono, motivoVisita, 
                        preferenciaAlimentaria, activo
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    trim($nombre), trim($apellido), $tipoDocumento, trim($nroDocumento),
                    trim($procedencia), $email ?: null, $telefono ?: null, trim($motivoVisita),
                    trim($preferenciaAlimentaria), $activo
                ]);

                // Éxito: limpiar formulario y mostrar mensaje
                $mensaje = "Huésped registrado exitosamente.";
                $nombre = $apellido = $tipoDocumento = $nroDocumento = $procedencia = $email = $telefono = $motivoVisita = $preferenciaAlimentaria = '';
                $activo = 1;
            } catch (Exception $e) {
                $errores['general'] = "Error al registrar el huésped. Inténtalo de nuevo.";
            }
        }
    }
}

// Función auxiliar para mostrar errores debajo del campo
function mostrarError($campo, $errores) {
    if (!empty($errores[$campo])) {
        return '<div class="text-danger mt-1 fs-6">' . htmlspecialchars($errores[$campo]) . '</div>';
    }
    return '';
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

    ' . (!empty($mensaje) ? '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Éxito!</strong> ' . htmlspecialchars($mensaje) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>' : '') . '

    ' . (!empty($errores['general']) ? '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error:</strong> ' . htmlspecialchars($errores['general']) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>' : '') . '
    
    <div class="reserva-form-container">
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" class="form-control" name="nombre" value="' . htmlspecialchars($nombre) . '" placeholder="Ej: María" required>
                    ' . mostrarError('nombre', $errores) . '
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Apellido *</label>
                    <input type="text" class="form-control" name="apellido" value="' . htmlspecialchars($apellido) . '" placeholder="Ej: López Pérez" required>
                    ' . mostrarError('apellido', $errores) . '
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipo de Documento *</label>
                    <select class="form-select" name="tipoDocumento" required>
                        <option value="">Seleccionar...</option>
                        <option value="DNI" ' . ($tipoDocumento === 'DNI' ? 'selected' : '') . '>DNI</option>
                        <option value="Pasaporte" ' . ($tipoDocumento === 'Pasaporte' ? 'selected' : '') . '>Pasaporte</option>
                        <option value="Carnet" ' . ($tipoDocumento === 'Carnet' ? 'selected' : '') . '>Carnet</option>
                    </select>
                    ' . mostrarError('tipoDocumento', $errores) . '
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Número de Documento *</label>
                    <input type="text" class="form-control" name="nroDocumento" value="' . htmlspecialchars($nroDocumento) . '" placeholder="Ej: 12345678 o AB123456" required>
                    ' . mostrarError('nroDocumento', $errores) . '
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Procedencia</label>
                    <input type="text" class="form-control" name="procedencia" value="' . htmlspecialchars($procedencia) . '" placeholder="Ej: La Paz, Bolivia">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="' . htmlspecialchars($email) . '" placeholder="Ej: maria.lopez@email.com">
                    ' . mostrarError('email', $errores) . '
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" name="telefono" value="' . htmlspecialchars($telefono) . '" placeholder="Ej: +591 777 12345">
                    ' . mostrarError('telefono', $errores) . '
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Motivo de Visita</label>
                    <input type="text" class="form-control" name="motivoVisita" value="' . htmlspecialchars($motivoVisita) . '" placeholder="Ej: Vacaciones, evento familiar">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Preferencias Alimentarias</label>
                <textarea class="form-control" rows="2" name="preferenciaAlimentaria" placeholder="Ej: Vegetariano, sin gluten, alérgico a la leche...">' . htmlspecialchars($preferenciaAlimentaria) . '</textarea>
                ' . mostrarError('preferenciaAlimentaria', $errores) . '
            </div>

            <div class="mb-3">
                <label class="form-label">Estado</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="activo" id="activoSwitch" ' . ($activo ? 'checked' : '') . '>
                    <label class="form-check-label" for="activoSwitch">Activo</label>
                </div>
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="ver_huespedes.php" class="btn btn-cancelar me-md-2">
                    <i class="fas fa-xmark me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-user-plus me-2"></i>Registrar Huésped
                </button>
            </div>
        </form>
    </div>
';

include 'plantilla_admin.php';
?>