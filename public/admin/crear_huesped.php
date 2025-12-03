<?php
// public/vistas/admin/crear_huesped.php


define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'crear_huesped';
require_once __DIR__ . '/../../config/database.php';

// ❤️ Errores por campo
$errores = [
    'nombre' => '',
    'apellido' => '',
    'tipoDocumento' => '',
    'nroDocumento' => '',
    'procedencia' => '',
    'email' => '',
    'telefono' => '',
    'motivoVisita' => '',
    'preferenciaAlimentaria' => '',
    'general' => ''
];

$mensaje = '';

// Valores por defecto
$campos = [
    'nombre' => '',
    'apellido' => '',
    'tipoDocumento' => '',
    'nroDocumento' => '',
    'procedencia' => '',
    'email' => '',
    'telefono' => '',
    'motivoVisita' => '',
    'preferenciaAlimentaria' => '',
    'activo' => true
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'apellido' => trim($_POST['apellido'] ?? ''),
        'tipoDocumento' => $_POST['tipoDocumento'] ?? '',
        'nroDocumento' => trim($_POST['nroDocumento'] ?? ''),
        'procedencia' => trim($_POST['procedencia'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'motivoVisita' => trim($_POST['motivoVisita'] ?? ''),
        'preferenciaAlimentaria' => trim($_POST['preferenciaAlimentaria'] ?? ''),
        'activo' => isset($_POST['activo'])
    ];

    $nombre = $campos['nombre'];
    $apellido = $campos['apellido'];
    $tipoDocumento = $campos['tipoDocumento'];
    $nroDocumento = $campos['nroDocumento'];
    $procedencia = $campos['procedencia'];
    $email = $campos['email'];
    $telefono = $campos['telefono'];
    $motivoVisita = $campos['motivoVisita'];
    $preferenciaAlimentaria = $campos['preferenciaAlimentaria'];
    $activo = $campos['activo'];

    // ✨ VALIDACIONES
    if (empty($nombre)) {
        $errores['nombre'] = "El nombre es obligatorio.";
    } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/', $nombre)) {
        $errores['nombre'] = "El nombre solo puede contener letras y espacios.";
    }

    if (empty($apellido)) {
        $errores['apellido'] = "El apellido es obligatorio.";
    } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/', $apellido)) {
        $errores['apellido'] = "El apellido solo puede contener letras y espacios.";
    }

    if (empty($tipoDocumento)) {
        $errores['tipoDocumento'] = "Selecciona un tipo de documento.";
    }

    if (empty($nroDocumento)) {
        $errores['nroDocumento'] = "El número de documento es obligatorio.";
    } else {
        if ($tipoDocumento === 'DNI' || $tipoDocumento === 'Carnet') {
            if (!preg_match('/^[0-9]+$/', $nroDocumento)) {
                $errores['nroDocumento'] = "El DNI o Carnet solo puede contener números.";
            } elseif (strlen($nroDocumento) < 5) {
                $errores['nroDocumento'] = "El documento debe tener al menos 5 dígitos.";
            }
        } elseif ($tipoDocumento === 'Pasaporte') {
            if (!preg_match('/^[a-zA-Z0-9]+$/', $nroDocumento)) {
                $errores['nroDocumento'] = "El pasaporte solo puede contener letras y números.";
            } elseif (strlen($nroDocumento) < 6) {
                $errores['nroDocumento'] = "El pasaporte debe tener al menos 6 caracteres.";
            }
        }
    }

    // ✨ Procedencia: solo letras y espacios
    if (!empty($procedencia) && !preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/u', $procedencia)) {
        $errores['procedencia'] = "La procedencia solo puede contener letras y espacios.";
    }

    // ✨ Email: opcional pero único y válido
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = "Por favor, ingresa un correo electrónico válido.";
        } else {
            $stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errores['email'] = "Este correo ya está registrado por otro huésped.";
            }
        }
    }

    // Teléfono: opcional
    if (!empty($telefono) && !preg_match('/^[0-9+\-\s()]+$/', $telefono)) {
        $errores['telefono'] = "El teléfono solo puede contener números y símbolos como +, -, ( ).";
    }

    // Motivo de visita
    if (!empty($motivoVisita) && !preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s.,;:¡!¿?()]+$/u', $motivoVisita)) {
        $errores['motivoVisita'] = "El motivo de visita solo puede contener letras, espacios y signos de puntuación.";
    }

    // Preferencias alimentarias
    if (!empty($preferenciaAlimentaria) && !preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/u', $preferenciaAlimentaria)) {
        $errores['preferenciaAlimentaria'] = "Las preferencias alimentarias solo pueden contener letras y espacios.";
    }

    // ✨ Verificar documento único
    if (empty($errores['nroDocumento'])) {
        $stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE nroDocumento = ?");
        $stmt->execute([$nroDocumento]);
        if ($stmt->fetch()) {
            $errores['nroDocumento'] = "Ya existe un huésped con ese número de documento.";
        }
    }

    // ✅ Si no hay errores, guardar
    if (!array_filter($errores)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO Huesped (nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nombre, $apellido, $tipoDocumento, $nroDocumento, $procedencia, $email, $telefono, $motivoVisita, $preferenciaAlimentaria, $activo ? 1 : 0]);
            $mensaje = "¡Huésped registrado exitosamente!";
        } catch (Exception $e) {
            $errores['general'] = "Error al registrar el huésped. Por favor, inténtalo de nuevo.";
        }
    }
}

$titulo_pagina = "Registrar Huésped - Hotel Yokoso";

// ❤️ Función para mostrar errores
function mostrarError($campo, $errores) {
    if (!empty($errores[$campo])) {
        return '<div class="text-danger mt-1"><small><i class="fas fa-exclamation-circle me-1"></i>' . htmlspecialchars($errores[$campo]) . '</small></div>';
    }
    return '';
}

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Registrar Nuevo Huésped</h2>
          
        </div>
    </div>

    ' . (!empty($mensaje) ? '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-check-circle me-1"></i> ¡Éxito!</strong> ' . htmlspecialchars($mensaje) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>' : '') . '

    ' . (!empty($errores['general']) ? '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-exclamation-triangle me-1"></i> Error:</strong> ' . htmlspecialchars($errores['general']) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>' : '') . '
    
    <div class="reserva-form-container">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input type="text" class="form-control" name="nombre" value="' . htmlspecialchars($campos['nombre']) . '" required>
                    ' . mostrarError('nombre', $errores) . '
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apellido *</label>
                    <input type="text" class="form-control" name="apellido" value="' . htmlspecialchars($campos['apellido']) . '" required>
                    ' . mostrarError('apellido', $errores) . '
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Tipo de Documento *</label>
                    <select class="form-select" name="tipoDocumento" required>
                        <option value="">Seleccionar...</option>
                        <option value="DNI" ' . ($campos['tipoDocumento'] === 'DNI' ? 'selected' : '') . '>DNI</option>
                        <option value="Pasaporte" ' . ($campos['tipoDocumento'] === 'Pasaporte' ? 'selected' : '') . '>Pasaporte</option>
                        <option value="Carnet" ' . ($campos['tipoDocumento'] === 'Carnet' ? 'selected' : '') . '>Carnet</option>
                    </select>
                    ' . mostrarError('tipoDocumento', $errores) . '
                </div>
                <div class="col-md-6">
                    <label class="form-label">Número de Documento *</label>
                    <input type="text" class="form-control" name="nroDocumento" value="' . htmlspecialchars($campos['nroDocumento']) . '" required>
                    ' . mostrarError('nroDocumento', $errores) . '
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Procedencia</label>
                    <input type="text" class="form-control" name="procedencia" value="' . htmlspecialchars($campos['procedencia']) . '">
                    ' . mostrarError('procedencia', $errores) . '
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="' . htmlspecialchars($campos['email']) . '">
                    ' . mostrarError('email', $errores) . '
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" name="telefono" value="' . htmlspecialchars($campos['telefono']) . '">
                    ' . mostrarError('telefono', $errores) . '
                </div>
                <div class="col-md-6">
                    <label class="form-label">Motivo de Visita</label>
                    <input type="text" class="form-control" name="motivoVisita" value="' . htmlspecialchars($campos['motivoVisita']) . '">
                    ' . mostrarError('motivoVisita', $errores) . '
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Preferencias Alimentarias</label>
                <textarea class="form-control" rows="2" name="preferenciaAlimentaria" placeholder="Ej. Vegetariano, sin gluten, alérgico a la leche...">' . htmlspecialchars($campos['preferenciaAlimentaria']) . '</textarea>
                ' . mostrarError('preferenciaAlimentaria', $errores) . '
            </div>

            <div class="mt-3">
                <label class="form-label">Estado</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="activo" value="1" id="activoSwitch" ' . ($campos['activo'] ? 'checked' : '') . '>
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