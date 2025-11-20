<?php
// public/vistas/admin/crear_habitacion.php

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
    $numero = trim($_POST['numero']);
    $tipo = $_POST['tipo'];
    $precioNoche = $_POST['precioNoche'];
    $estado = $_POST['estado'];
    $foto = $_FILES['foto']['name'] ?? null;

    if (empty($numero) || empty($tipo) || empty($precioNoche)) {
        $error = "Los campos número, tipo y precio por noche son obligatorios.";
    } else {
        // Subir imagen si existe
        if ($foto) {
            $directorio = __DIR__ . '/../../assets/img/habitaciones/';
            $rutaFoto = $directorio . basename($foto);
            $extension = strtolower(pathinfo($rutaFoto, PATHINFO_EXTENSION));

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error = "Solo se permiten archivos JPG, JPEG, PNG o GIF.";
            } elseif (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFoto)) {
                // Insertar en la base de datos
                $stmt = $pdo->prepare("
                    INSERT INTO Habitacion (numero, tipo, precioNoche, estado, foto)
                    VALUES (?, ?, ?, ?, ?)
                ");
                if ($stmt->execute([$numero, $tipo, $precioNoche, $estado, $foto])) {
                    $mensaje = "Habitación registrada exitosamente.";
                } else {
                    $error = "Error al registrar la habitación.";
                }
            } else {
                $error = "Error al subir la imagen.";
            }
        } else {
            // Insertar sin foto
            $stmt = $pdo->prepare("
                INSERT INTO Habitacion (numero, tipo, precioNoche, estado)
                VALUES (?, ?, ?, ?)
            ");
            if ($stmt->execute([$numero, $tipo, $precioNoche, $estado])) {
                $mensaje = "Habitación registrada exitosamente.";
            } else {
                $error = "Error al registrar la habitación.";
            }
        }
    }
}

$titulo_pagina = "Crear Habitación - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Registrar Nueva Habitación</h2>
            <a href="ver_habitaciones.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Habitaciones
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
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Número de Habitación *</label>
                    <input type="text" class="form-control" name="numero" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo de Habitación *</label>
                    <select class="form-select" name="tipo" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="simple">Simple</option>
                        <option value="doble">Doble</option>
                        <option value="triple">Triple</option>
                        <option value="cuadruple">Cuádruple</option>
                        <option value="familiar">Familiar</option>
                        <option value="suite">Suite</option>
                        <option value="de sal">De Sal</option>
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Precio por Noche (Bs.) *</label>
                    <input type="number" class="form-control" name="precioNoche" step="0.01" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Estado *</label>
                    <select class="form-select" name="estado" required>
                        <option value="disponible">Disponible</option>
                        <option value="ocupada">Ocupada</option>
                        <option value="mantenimiento">En mantenimiento</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Foto de la Habitación</label>
                <input type="file" class="form-control" name="foto" accept="image/*">
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="ver_habitaciones.php" class="btn btn-cancelar me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Guardar Habitación
                </button>
            </div>
        </form>
    </div>
';

include 'plantilla_admin.php';
?>