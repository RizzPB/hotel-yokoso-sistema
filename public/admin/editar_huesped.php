<?php
// public/admin/editar_huesped.php


define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'editar_huesped';
require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header("Location: ver_huespedes.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Huesped WHERE idHuesped = ?");
$stmt->execute([$id]);
$huesped = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$huesped) {
    header("Location: ver_huespedes.php");
    exit;
}

//  Errores por campo
$errores = [
    'nombre' => '',
    'apellido' => '',
    'tipoDocumento' => '',
    'nroDocumento' => '',
    'email' => '',
    'telefono' => '',
    'motivoVisita' => '',
    'preferenciaAlimentaria' => '',
    'general' => ''
];

$mensaje = '';

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
    $activo = isset($_POST['activo']) ? 1 : 0;

    // ✨ VALIDACIÓN DE CAMPOS
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

    // ✨ Procedencia: solo letras y espacios (¡nada de números!)
    if (!empty($procedencia) && !preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/u', $procedencia)) {
        $errores['procedencia'] = "La procedencia solo puede contener letras y espacios.";
    }

    // ✨ Email: opcional, pero si se pone, debe ser válido y único
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = "Por favor, ingresa un correo electrónico válido.";
        } else {
            $stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE email = ? AND idHuesped != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $errores['email'] = "Este correo ya está registrado por otro huésped.";
            }
        }
    }

    // Teléfono: opcional, formato flexible
    if (!empty($telefono) && !preg_match('/^[0-9+\-\s()]+$/', $telefono)) {
        $errores['telefono'] = "El teléfono solo puede contener números y símbolos como +, -, ( ).";
    }

    // Motivo de visita: solo letras, espacios y puntuación
    if (!empty($motivoVisita) && !preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s.,;:¡!¿?()]+$/u', $motivoVisita)) {
        $errores['motivoVisita'] = "El motivo de visita solo puede contener letras, espacios y signos de puntuación.";
    }

    // Preferencias alimentarias: solo letras y espacios
    if (!empty($preferenciaAlimentaria) && !preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/u', $preferenciaAlimentaria)) {
        $errores['preferenciaAlimentaria'] = "Las preferencias alimentarias solo pueden contener letras y espacios.";
    }

    // ✨ Verificar que el documento no esté duplicado (excepto para este huésped)
    $stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE nroDocumento = ? AND idHuesped != ?");
    $stmt->execute([$nroDocumento, $id]);
    if ($stmt->fetch()) {
        $errores['nroDocumento'] = "Ya existe otro huésped con ese número de documento.";
    }

    // ✅ SOLO GUARDAR SI NO HAY ERRORES
    if (!array_filter($errores)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE Huesped SET 
                    nombre = ?, apellido = ?, tipoDocumento = ?, nroDocumento = ?, 
                    procedencia = ?, email = ?, telefono = ?, motivoVisita = ?, 
                    preferenciaAlimentaria = ?, activo = ?
                WHERE idHuesped = ?
            ");
            $stmt->execute([$nombre, $apellido, $tipoDocumento, $nroDocumento, $procedencia, $email, $telefono, $motivoVisita, $preferenciaAlimentaria, $activo, $id]);
            $mensaje = "¡Huésped actualizado exitosamente!";
            // Recargar datos
            $stmt = $pdo->prepare("SELECT * FROM Huesped WHERE idHuesped = ?");
            $stmt->execute([$id]);
            $huesped = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $errores['general'] = "Error al guardar los cambios. Por favor, inténtalo de nuevo.";
        }
    }
}

$titulo_pagina = "Editar Huésped - Hotel Yokoso";

// ❤️ Función para mostrar errores debajo de cada campo
function mostrarError($campo, $errores) {
    if (!empty($errores[$campo])) {
        return '<div class="text-danger mt-1"><small><i class="fas fa-exclamation-circle me-1"></i>' . htmlspecialchars($errores[$campo]) . '</small></div>';
    }
    return '';
}

$contenido_principal = '
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-rojo fw-bold">Editar Huésped</h2>
        
    </div>

    ' . (!empty($mensaje) ? '<div class="alert alert-success text-center mx-auto mb-4" style="max-width:900px;"><i class="fas fa-check-circle fa-2x"></i><br>' . htmlspecialchars($mensaje) . '</div>' : '') . '
    ' . (!empty($errores['general']) ? '<div class="alert alert-danger text-center mx-auto mb-4" style="max-width:900px;"><i class="fas fa-times-circle fa-2x"></i><br>' . htmlspecialchars($errores['general']) . '</div>' : '') . '

    <div class="row justify-content-center">
        <div class="col-xl-11 col-xxl-10">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5 p-lg-6">
                    <div class="text-center mb-5">
                        <h3 class="text-rojo fw-bold">' . htmlspecialchars($huesped['nombre'] . ' ' . $huesped['apellido']) . '</h3>
                        <p class="text-muted">ID: #' . $huesped['idHuesped'] . ' • Documento: ' . htmlspecialchars($huesped['nroDocumento']) . '</p>
                    </div>

                    <form method="POST">
                        <!-- Nombre y Apellido -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Nombre *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="nombre" value="' . htmlspecialchars($_POST['nombre'] ?? $huesped['nombre']) . '" required>
                                ' . mostrarError('nombre', $errores) . '
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Apellido *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="apellido" value="' . htmlspecialchars($_POST['apellido'] ?? $huesped['apellido']) . '" required>
                                ' . mostrarError('apellido', $errores) . '
                            </div>
                        </div>

                        <!-- Documento -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Tipo Documento *</label>
                                <select class="form-select form-select-lg rounded-pill" name="tipoDocumento" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="DNI" ' . (($_POST['tipoDocumento'] ?? $huesped['tipoDocumento']) === 'DNI' ? 'selected' : '') . '>DNI</option>
                                    <option value="Pasaporte" ' . (($_POST['tipoDocumento'] ?? $huesped['tipoDocumento']) === 'Pasaporte' ? 'selected' : '') . '>Pasaporte</option>
                                    <option value="Carnet" ' . (($_POST['tipoDocumento'] ?? $huesped['tipoDocumento']) === 'Carnet' ? 'selected' : '') . '>Carnet</option>
                                </select>
                                ' . mostrarError('tipoDocumento', $errores) . '
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Nro. Documento *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="nroDocumento" value="' . htmlspecialchars($_POST['nroDocumento'] ?? $huesped['nroDocumento']) . '" required>
                                ' . mostrarError('nroDocumento', $errores) . '
                            </div>
                        </div>

                        <!-- Contacto -->
                        <div class="row g-4 mb-5"> <!-- ✅ CORREGIDO: faltaba el = en class -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Procedencia</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="procedencia" value="' . htmlspecialchars($_POST['procedencia'] ?? $huesped['procedencia']) . '">
                                ' . mostrarError('procedencia', $errores) . '
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Email</label>
                                <input type="email" class="form-control form-control-lg rounded-pill" name="email" value="' . htmlspecialchars($_POST['email'] ?? $huesped['email']) . '">
                                ' . mostrarError('email', $errores) . '
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Teléfono</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="telefono" value="' . htmlspecialchars($_POST['telefono'] ?? $huesped['telefono']) . '">
                                ' . mostrarError('telefono', $errores) . '
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Motivo de Visita</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="motivoVisita" value="' . htmlspecialchars($_POST['motivoVisita'] ?? $huesped['motivoVisita']) . '">
                                ' . mostrarError('motivoVisita', $errores) . '
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark">Preferencias Alimentarias</label>
                            <textarea class="form-control form-control-lg rounded-4" rows="3" name="preferenciaAlimentaria" placeholder="Ej. Vegetariano, sin gluten...">' . htmlspecialchars($_POST['preferenciaAlimentaria'] ?? $huesped['preferenciaAlimentaria']) . '</textarea>
                            ' . mostrarError('preferenciaAlimentaria', $errores) . '
                        </div>

                        <!-- Estado -->
                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark">Estado del Huésped</label>
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" name="activo" id="activoSwitch" ' . ((isset($_POST['activo']) || $huesped['activo']) ? 'checked' : '') . '>
                                <label class="form-check-label fs-5" for="activoSwitch">
                                    <span class="text-success">Activo</span> / <span class="text-danger">Inactivo</span>
                                </label>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="pt-4 border-top text-end">
                            <a href="ver_huespedes.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill me-3">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-yokoso btn-lg px-5 rounded-pill shadow-lg">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
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