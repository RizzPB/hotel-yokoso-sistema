<?php
// public/vistas/admin/editar_habitacion.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ver_habitaciones.php");
    exit;
}

// Obtener la habitación actual
$stmt = $pdo->prepare("SELECT * FROM Habitacion WHERE idHabitacion = ?");
$stmt->execute([$id]);
$habitacion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$habitacion) {
    header("Location: ver_habitaciones.php");
    exit;
}

$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = trim($_POST['numero']);
    $tipo = $_POST['tipo'];
    $precioNoche = $_POST['precioNoche'];
    $estado = $_POST['estado'];
    $foto = $_FILES['foto']['name'] ?? null;

    if (empty($numero) || empty($tipo) || empty($precioNoche)) {
        $error = "Los campos número, tipo y precio por noche son obligatorios.";
    } else {
        if ($foto) {
            // Subir nueva imagen
            $directorio = __DIR__ . '/../../assets/img/habitaciones/';
            $rutaFoto = $directorio . basename($foto);
            $extension = strtolower(pathinfo($rutaFoto, PATHINFO_EXTENSION));

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error = "Solo se permiten archivos JPG, JPEG, PNG o GIF.";
            } elseif (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFoto)) {
                // Actualizar con nueva foto
                $stmt = $pdo->prepare("
                    UPDATE Habitacion
                    SET numero = ?, tipo = ?, precioNoche = ?, estado = ?, foto = ?
                    WHERE idHabitacion = ?
                ");
                if ($stmt->execute([$numero, $tipo, $precioNoche, $estado, $foto, $id])) {
                    $mensaje = "Habitación actualizada exitosamente.";
                    // Recargar datos
                    $stmt = $pdo->prepare("SELECT * FROM Habitacion WHERE idHabitacion = ?");
                    $stmt->execute([$id]);
                    $habitacion = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $error = "Error al actualizar la habitación.";
                }
            } else {
                $error = "Error al subir la imagen.";
            }
        } else {
            // Actualizar sin cambiar foto
            $stmt = $pdo->prepare("
                UPDATE Habitacion
                SET numero = ?, tipo = ?, precioNoche = ?, estado = ?
                WHERE idHabitacion = ?
            ");
            if ($stmt->execute([$numero, $tipo, $precioNoche, $estado, $id])) {
                $mensaje = "Habitación actualizada exitosamente.";
            } else {
                $error = "Error al actualizar la habitación.";
            }
        }
    }
}

$titulo_pagina = "Editar Habitación - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Editar Habitación #' . htmlspecialchars($habitacion['idHabitacion']) . '</h2>
            <a href="ver_habitaciones.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Habitaciones
            </a>
        </div>
    </div>

    ' . (isset($mensaje) ? '<div class="alert alert-success">' . htmlspecialchars($mensaje) . '</div>' : '') . '
    ' . (isset($error) ? '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>' : '') . '

    <div class="reserva-form-container">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Número de Habitación *</label>
                    <input type="text" class="form-control" name="numero" value="' . htmlspecialchars($habitacion['numero']) . '" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo de Habitación *</label>
                    <select class="form-select" name="tipo" required>
                        <option value="simple" ' . ($habitacion['tipo'] === 'simple' ? 'selected' : '') . '>Simple</option>
                        <option value="doble" ' . ($habitacion['tipo'] === 'doble' ? 'selected' : '') . '>Doble</option>
                        <option value="triple" ' . ($habitacion['tipo'] === 'triple' ? 'selected' : '') . '>Triple</option>
                        <option value="cuadruple" ' . ($habitacion['tipo'] === 'cuadruple' ? 'selected' : '') . '>Cuádruple</option>
                        <option value="familiar" ' . ($habitacion['tipo'] === 'familiar' ? 'selected' : '') . '>Familiar</option>
                        <option value="suite" ' . ($habitacion['tipo'] === 'suite' ? 'selected' : '') . '>Suite</option>
                        <option value="de sal" ' . ($habitacion['tipo'] === 'de sal' ? 'selected' : '') . '>De Sal</option>
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Precio por Noche (Bs.) *</label>
                    <input type="number" class="form-control" name="precioNoche" step="0.01" value="' . htmlspecialchars($habitacion['precioNoche']) . '" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Estado *</label>
                    <select class="form-select" name="estado" required>
                        <option value="disponible" ' . ($habitacion['estado'] === 'disponible' ? 'selected' : '') . '>Disponible</option>
                        <option value="ocupada" ' . ($habitacion['estado'] === 'ocupada' ? 'selected' : '') . '>Ocupada</option>
                        <option value="mantenimiento" ' . ($habitacion['estado'] === 'mantenimiento' ? 'selected' : '') . '>En mantenimiento</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Foto Actual</label>
                <div>
                    <img src="/assets/img/habitaciones/' . htmlspecialchars($habitacion['foto']) . '" alt="Foto de habitación" width="100" class="rounded">
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Actualizar Foto</label>
                <input type="file" class="form-control" name="foto" accept="image/*">
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="ver_habitaciones.php" class="btn btn-cancelar me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
';

include 'plantilla_admin.php';
?>