<?php
// public/vistas/admin/crear_empleado.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $cargo = trim($_POST['cargo']);
    $nombreUsuario = trim($_POST['nombreUsuario']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmarPassword = $_POST['confirmarPassword'];

    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($cargo) || empty($nombreUsuario) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($password !== $confirmarPassword) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[@$!%*?&]/', $password)) {
        $error = "La contraseña debe tener al menos 8 caracteres, mayúsculas, minúsculas, números y símbolos.";
    } else {
        // Verificar si el usuario ya existe
        $stmt = $pdo->prepare("SELECT idUsuario FROM Usuario WHERE nombreUsuario = ? OR email = ?");
        $stmt->execute([$nombreUsuario, $email]);
        if ($stmt->fetch()) {
            $error = "El nombre de usuario o correo ya está registrado.";
        } else {
            // Cifrar contraseña
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar en Usuario
            $stmt = $pdo->prepare("
                INSERT INTO Usuario (nombreUsuario, contrasena, rol, email, activo)
                VALUES (?, ?, 'empleado', ?, 1)
            ");
            if ($stmt->execute([$nombreUsuario, $hash, $email])) {
                $idUsuario = $pdo->lastInsertId();

                // Insertar en Empleado
                $stmt = $pdo->prepare("
                    INSERT INTO Empleado (nombre, apellido, cargo, idUsuario)
                    VALUES (?, ?, ?, ?)
                ");
                if ($stmt->execute([$nombre, $apellido, $cargo, $idUsuario])) {
                    $mensaje = "Empleado registrado exitosamente.";
                } else {
                    $error = "Error al registrar el empleado.";
                }
            } else {
                $error = "Error al crear la cuenta de usuario.";
            }
        }
    }
}

$titulo_pagina = "Crear Empleado - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Registrar Nuevo Empleado</h2>
            <a href="ver_empleados.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Empleados
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
                    <input type="text" class="form-control" name="nombre" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apellido *</label>
                    <input type="text" class="form-control" name="apellido" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Cargo *</label>
                    <input type="text" class="form-control" name="cargo" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Usuario *</label>
                    <input type="text" class="form-control" name="nombreUsuario" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contraseña *</label>
                    <input type="password" class="form-control" name="password" required>
                    <div class="form-text">Mínimo 8 caracteres, con mayúsculas, minúsculas, números y símbolos.</div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Confirmar Contraseña *</label>
                    <input type="password" class="form-control" name="confirmarPassword" required>
                </div>
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="ver_empleados.php" class="btn btn-cancelar me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Guardar Empleado
                </button>
            </div>
        </form>
    </div>
';

include 'plantilla_admin.php';
?>