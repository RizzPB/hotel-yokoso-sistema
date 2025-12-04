<?php
// public/admin/editar_empleado.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Obtener ID del empleado a editar
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ver_empleados.php");
    exit;
}

// Obtener datos del empleado a editar, incluyendo idUsuario
$stmt = $pdo->prepare("
    SELECT e.idEmpleado, e.nombre, e.apellido, e.cargo, 
           u.idUsuario, u.nombreUsuario, u.email, u.rol, u.activo
    FROM Empleado e
    JOIN Usuario u ON e.idUsuario = u.idUsuario
    WHERE e.idEmpleado = ?
");
// Ejecutar la consulta
$stmt->execute([$id]);
// Fetch del empleado a editar 
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no existe el empleado o falta el idUsuario, redirigir
if (!$empleado || !isset($empleado['idUsuario'])) {
    $_SESSION['mensaje_error'] = "Empleado no encontrado.";
    header("Location: ver_empleados.php");
    exit;
}

$error = null;
$mensaje = null;

// Procesar el formulario al enviarse 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $nombreUsuario = trim($_POST['nombreUsuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;
    $nuevaPassword = $_POST['nuevaPassword'] ?? '';

    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($cargo) || empty($nombreUsuario) || empty($email)) {
        $error = "Los campos nombre, apellido, cargo, usuario y email son obligatorios.";
    } else {
        // Verificar duplicados (excluyendo al usuario actual)
        $stmt = $pdo->prepare("SELECT idUsuario FROM Usuario WHERE (nombreUsuario = ? OR email = ?) AND idUsuario != ?");
        $stmt->execute([$nombreUsuario, $email, $empleado['idUsuario']]);
        if ($stmt->fetch()) {
            $error = "El nombre de usuario o correo ya está registrado por otro usuario.";
        } else {
            try {
                // Actualizar usuario
                if (!empty($nuevaPassword)) {
                    if (strlen($nuevaPassword) < 8 || 
                        !preg_match('/[A-Z]/', $nuevaPassword) || 
                        !preg_match('/[a-z]/', $nuevaPassword) || 
                        !preg_match('/[0-9]/', $nuevaPassword) || 
                        !preg_match('/[@$!%*?&]/', $nuevaPassword)) {
                        $error = "La contraseña debe tener al menos 8 caracteres, mayúsculas, minúsculas, números y símbolos.";
                    } else {
                        // Actualizar con nueva contraseña 
                        $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("
                            UPDATE Usuario
                            SET nombreUsuario = ?, email = ?, activo = ?, contrasena = ?
                            WHERE idUsuario = ?
                        ");
                        $stmt->execute([$nombreUsuario, $email, $activo, $hash, $empleado['idUsuario']]);
                    }
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE Usuario
                        SET nombreUsuario = ?, email = ?, activo = ?
                        WHERE idUsuario = ?
                    ");
                    $stmt->execute([$nombreUsuario, $email, $activo, $empleado['idUsuario']]);
                }

                if (!$error) {
                    // Actualizar empleado
                    $stmt = $pdo->prepare("
                        UPDATE Empleado
                        SET nombre = ?, apellido = ?, cargo = ?
                        WHERE idEmpleado = ?
                    ");
                    // Ejecutar actualización MENSAJE DE ÉXITO
                    if ($stmt->execute([$nombre, $apellido, $cargo, $id])) {
                        $mensaje = "Empleado actualizado exitosamente.";

                        // Recargar datos con idUsuario incluido
                        $stmt = $pdo->prepare("
                            SELECT e.idEmpleado, e.nombre, e.apellido, e.cargo, 
                                   u.idUsuario, u.nombreUsuario, u.email, u.rol, u.activo
                            FROM Empleado e
                            JOIN Usuario u ON e.idUsuario = u.idUsuario
                            WHERE e.idEmpleado = ?
                        ");
                        $stmt->execute([$id]);
                        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
                    } else {
                        $error = "Error al actualizar los datos del empleado.";
                    }
                }
            } catch (Exception $e) {
                $error = "Ocurrió un error inesperado al guardar los cambios.";
            }
        }
    }
}

$titulo_pagina = "Editar Empleado - Hotel Yokoso";

// Estilos personalizados para el botón de cancelar
$estilos_adicionales = '
<style>
.btn-cancelar {
    background-color: #f8f9fa !important;
    color: #6c757d !important;
    border: 1px solid #ced4da !important;
    border-radius: 50px !important;
    padding: 0.5rem 1.5rem !important;
    font-weight: 600 !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
    transition: all 0.2s ease !important;
}
.btn-cancelar:hover {
    background-color: #e9ecef !important;
    color: #495057 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}
</style>
';

$contenido_principal = $estilos_adicionales . '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Editar Empleado: ' . htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']) . '</h2>
            <a href="ver_empleados.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Empleados
            </a>
        </div>
    </div>

    ' . (!empty($mensaje) ? '<div class="alert alert-success alert-dismissible fade show" role="alert">
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
                    <input type="text" class="form-control" name="nombre" value="' . htmlspecialchars($empleado['nombre']) . '" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apellido *</label>
                    <input type="text" class="form-control" name="apellido" value="' . htmlspecialchars($empleado['apellido']) . '" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Cargo *</label>
                    <input type="text" class="form-control" name="cargo" value="' . htmlspecialchars($empleado['cargo']) . '" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Usuario *</label>
                    <input type="text" class="form-control" name="nombreUsuario" value="' . htmlspecialchars($empleado['nombreUsuario']) . '" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-control" name="email" value="' . htmlspecialchars($empleado['email']) . '" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Estado</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="activo" id="activoSwitch" ' . ($empleado['activo'] ? 'checked' : '') . '>
                        <label class="form-check-label" for="activoSwitch">Activo</label>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Nueva Contraseña (Opcional)</label>
                    <input type="password" class="form-control" name="nuevaPassword" placeholder="Solo si deseas cambiarla">
                    <div class="form-text">Mínimo 8 caracteres, con mayúsculas, minúsculas, números y símbolos.</div>
                </div>
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="ver_empleados.php" class="btn btn-cancelar me-md-2">
                    <i class="fas fa-xmark me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
';

include 'plantilla_admin.php';
?>