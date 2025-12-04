<?php
// public/admin/crear_empleado.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Valores previos para mantener el formulario en caso de error
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$cargo = $_POST['cargo'] ?? '';
$nombreUsuario = $_POST['nombreUsuario'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirmarPassword = $_POST['confirmarPassword'] ?? '';

// Errores por campo
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar cada campo
    if (empty(trim($nombre))) {
        $errores['nombre'] = "El nombre es obligatorio.";
    } elseif (!preg_match('/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]+$/', trim($nombre))) {
        $errores['nombre'] = "El nombre solo debe contener letras y espacios.";
    }

    if (empty(trim($apellido))) {
        $errores['apellido'] = "El apellido es obligatorio.";
    } elseif (!preg_match('/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]+$/', trim($apellido))) {
        $errores['apellido'] = "El apellido solo debe contener letras y espacios.";
    }

    if (empty(trim($cargo))) {
        $errores['cargo'] = "El cargo es obligatorio.";
    } elseif (strlen(trim($cargo)) < 2) {
        $errores['cargo'] = "El cargo debe tener al menos 2 caracteres.";
    }

    if (empty(trim($nombreUsuario))) {
        $errores['nombreUsuario'] = "El nombre de usuario es obligatorio.";
    } elseif (strlen(trim($nombreUsuario)) < 3) {
        $errores['nombreUsuario'] = "El usuario debe tener al menos 3 caracteres.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($nombreUsuario))) {
        $errores['nombreUsuario'] = "Solo letras, números y guión bajo.";
    }

    if (empty(trim($email))) {
        $errores['email'] = "El correo es obligatorio.";
    } elseif (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = "Ingresa un correo válido.";
    }

    if (empty($password)) {
        $errores['password'] = "La contraseña es obligatoria.";
    } else {
        if (strlen($password) < 8) {
            $errores['password'] = "La contraseña debe tener al menos 8 caracteres.";
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errores['password'] = "Debe incluir al menos una mayúscula.";
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errores['password'] = "Debe incluir al menos una minúscula.";
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errores['password'] = "Debe incluir al menos un número.";
        } elseif (!preg_match('/[@$!%*?&]/', $password)) {
            $errores['password'] = "Debe incluir al menos un símbolo (@$!%*?&).";
        }
    }

    if ($password !== $confirmarPassword) {
        $errores['confirmarPassword'] = "Las contraseñas no coinciden.";
    }

    // Si no hay errores individuales, verificar duplicados
    if (empty($errores)) {
        $stmt = $pdo->prepare("SELECT idUsuario FROM Usuario WHERE nombreUsuario = ? OR email = ?");
        $stmt->execute([trim($nombreUsuario), trim($email)]);
        if ($stmt->fetch()) {
            // Puedes marcar ambos campos o uno; elegimos el más apropiado
            $errores['nombreUsuario'] = "Este usuario o correo ya está registrado.";
        } else {
            // Registrar
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO Usuario (nombreUsuario, contrasena, rol, email, activo)
                    VALUES (?, ?, 'empleado', ?, 1)
                ");
                $stmt->execute([trim($nombreUsuario), $hash, trim($email)]);
                $idUsuario = $pdo->lastInsertId();

                $stmt = $pdo->prepare("
                    INSERT INTO Empleado (nombre, apellido, cargo, idUsuario)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([trim($nombre), trim($apellido), trim($cargo), $idUsuario]);

                $pdo->commit();
                $mensaje = "Empleado registrado exitosamente.";
                // Reiniciar el formulario
                $nombre = $apellido = $cargo = $nombreUsuario = $email = $password = $confirmarPassword = '';
            } catch (Exception $e) {
                $pdo->rollback();
                $errores['general'] = "Error al registrar el empleado. Inténtalo de nuevo.";
            }
        }
    }
}

$titulo_pagina = "Crear Empleado - Hotel Yokoso";

// Función auxiliar para mostrar error debajo del campo
function mostrarError($campo, $errores) {
    if (!empty($errores[$campo])) {
        return '<div class="text-danger mt-1 fs-6">' . htmlspecialchars($errores[$campo]) . '</div>';
    }
    return '';
}

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Registrar Nuevo Empleado</h2>
            <a href="ver_empleados.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Empleados
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
                    <input type="text" class="form-control" name="apellido" value="' . htmlspecialchars($apellido) . '" placeholder="Ej: López" required>
                    ' . mostrarError('apellido', $errores) . '
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cargo *</label>
                    <input type="text" class="form-control" name="cargo" value="' . htmlspecialchars($cargo) . '" placeholder="Ej: Recepcionista" required>
                    ' . mostrarError('cargo', $errores) . '
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Usuario *</label>
                    <input type="text" class="form-control" name="nombreUsuario" value="' . htmlspecialchars($nombreUsuario) . '" placeholder="Ej: mlopez_2025" required>
                    ' . mostrarError('nombreUsuario', $errores) . '
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-control" name="email" value="' . htmlspecialchars($email) . '" placeholder="Ej: mlopez@yokoso.com" required>
                    ' . mostrarError('email', $errores) . '
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contraseña *</label>
                    <input type="password" class="form-control" name="password" value="' . htmlspecialchars($password) . '" placeholder="••••••••" required>
                    <div class="form-text">Mínimo 8 caracteres, con mayúsculas, minúsculas, números y símbolos (@$!%*?&).</div>
                    ' . mostrarError('password', $errores) . '
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirmar Contraseña *</label>
                    <input type="password" class="form-control" name="confirmarPassword" value="' . htmlspecialchars($confirmarPassword) . '" placeholder="••••••••" required>
                    ' . mostrarError('confirmarPassword', $errores) . '
                </div>
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="ver_empleados.php" class="btn btn-cancelar me-md-2">
                    <i class="fas fa-xmark me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-user-plus me-2"></i>Registrar Empleado
                </button>
            </div>
        </form>
    </div>
';

include 'plantilla_admin.php';
?>