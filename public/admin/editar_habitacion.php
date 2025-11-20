<?php
// public/admin/editar_habitacion.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header("Location: ver_habitaciones.php");
    exit;
}

// Obtener habitación
$stmt = $pdo->prepare("SELECT * FROM Habitacion WHERE idHabitacion = ?");
$stmt->execute([$id]);
$habitacion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$habitacion) {
    header("Location: ver_habitaciones.php");
    exit;
}

$mensaje = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero      = trim($_POST['numero'] ?? '');
    $tipo        = $_POST['tipo'] ?? '';
    $precioNoche = $_POST['precioNoche'] ?? '';
    $estado      = $_POST['estado'] ?? '';
    $foto        = $_FILES['foto']['name'] ?? null;

    if (empty($numero) || empty($tipo) || empty($precioNoche)) {
        $error = "Los campos número, tipo y precio por noche son obligatorios.";
    } else {
        try {
            if ($foto) {
                $directorio = __DIR__ . '/../../assets/img/habitaciones/';
                $rutaFoto   = $directorio . basename($foto);
                $extension  = strtolower(pathinfo($rutaFoto, PATHINFO_EXTENSION));

                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $error = "Solo se permiten imágenes (JPG, PNG, GIF, WEBP).";
                } elseif (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFoto)) {
                    $stmt = $pdo->prepare("UPDATE Habitacion SET numero=?, tipo=?, precioNoche=?, estado=?, foto=? WHERE idHabitacion=?");
                    $stmt->execute([$numero, $tipo, $precioNoche, $estado, $foto, $id]);
                    $mensaje = "Habitación actualizada con nueva foto.";
                    $habitacion['foto'] = $foto;
                } else {
                    $error = "Error al subir la imagen.";
                }
            } else {
                $stmt = $pdo->prepare("UPDATE Habitacion SET numero=?, tipo=?, precioNoche=?, estado=? WHERE idHabitacion=?");
                $stmt->execute([$numero, $tipo, $precioNoche, $estado, $id]);
                $mensaje = "Habitación actualizada exitosamente.";
            }
        } catch (Exception $e) {
            $error = "Error al guardar los cambios.";
        }
    }
}

$titulo_pagina = "Editar Habitación - Hotel Yokoso";

$contenido_principal = '
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-rojo fw-bold">
            Editar Habitación #' . htmlspecialchars($habitacion['numero']) . '
        </h2>
        <a href="ver_habitaciones.php" class="btn btn-outline-secondary btn-lg rounded-pill px-5">
            Volver a Habitaciones
        </a>
    </div>

    ' . ($mensaje ? '<div class="alert alert-success text-center mx-auto mb-4" style="max-width:900px;"><i class="fas fa-check-circle fa-2x"></i><br>' . $mensaje . '</div>' : '') . '
    ' . ($error ? '<div class="alert alert-danger text-center mx-auto mb-4" style="max-width:900px;"><i class="fas fa-times-circle fa-2x"></i><br>' . $error . '</div>' : '') . '

    <!-- FORMULARIO FIJO, ANCHO Y PREMIUM -->
    <div class="row justify-content-center">
        <div class="col-xl-11 col-xxl-10">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5 p-lg-6">

                    <form method="POST" enctype="multipart/form-data">

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Número de Habitación *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="numero" value="'.htmlspecialchars($habitacion['numero']).'" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Tipo de Habitación *</label>
                                <select class="form-select form-select-lg rounded-pill" name="tipo" required>
                                    <option value="simple"     '.($habitacion['tipo']==='simple'?'selected':'').'>Simple</option>
                                    <option value="doble"      '.($habitacion['tipo']==='doble'?'selected':'').'>Doble</option>
                                    <option value="triple"     '.($habitacion['tipo']==='triple'?'selected':'').'>Triple</option>
                                    <option value="cuadruple"  '.($habitacion['tipo']==='cuadruple'?'selected':'').'>Cuádruple</option>
                                    <option value="familiar"   '.($habitacion['tipo']==='familiar'?'selected':'').'>Familiar</option>
                                    <option value="suite"      '.($habitacion['tipo']==='suite'?'selected':'').'>Suite</option>
                                    <option value="de sal"     '.($habitacion['tipo']==='de sal'?'selected':'').'>De Sal</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Precio por Noche (Bs.) *</label>
                                <input type="number" step="0.01" min="0" class="form-control form-control-lg rounded-pill" name="precioNoche" value="'.htmlspecialchars($habitacion['precioNoche']).'" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Estado *</label>
                                <select class="form-select form-select-lg rounded-pill" name="estado" required>
                                    <option value="disponible"    '.($habitacion['estado']==='disponible'?'selected':'').'>Disponible</option>
                                    <option value="ocupada"       '.($habitacion['estado']==='ocupada'?'selected':'').'>Ocupada</option>
                                    <option value="mantenimiento" '.($habitacion['estado']==='mantenimiento'?'selected':'').'>En Mantenimiento</option>
                                </select>
                            </div>
                        </div>

                        <!-- FOTO ACTUAL -->
                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark">Foto Actual</label>
                            <div class="text-center p-4 bg-light rounded-4">
                                <img src="/assets/img/habitaciones/'.htmlspecialchars($habitacion['foto']).'" 
                                     alt="Habitación '.htmlspecialchars($habitacion['numero']).'" 
                                     class="img-fluid rounded shadow" style="max-height:300px; object-fit:cover;">
                                <p class="mt-3 text-muted"><em>'.htmlspecialchars($habitacion['foto']).'</em></p>
                            </div>
                        </div>

                        <!-- NUEVA FOTO -->
                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark">Cambiar Foto</label>
                            <input type="file" class="form-control form-control-lg rounded-pill" name="foto" accept="image/*">
                            <div class="form-text">Formatos permitidos: JPG, PNG, GIF, WEBP (máx. 5MB recomendado)</div>
                        </div>

                        <!-- BOTONES FINALES -->
                        <div class="pt-4 border-top text-end">
                            <a href="ver_habitaciones.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill me-3">
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
?>