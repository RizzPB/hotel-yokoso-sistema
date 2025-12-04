<?php
// public/vistas/admin/crear_habitacion.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Errores por campo
$errores = [];
$mensaje = null;

// Procesar el formulario al enviarse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = trim($_POST['numero'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $precioNoche = trim($_POST['precioNoche'] ?? '');
    $estado = $_POST['estado'] ?? '';
    $foto = $_FILES['foto']['name'] ?? null;

// Validar número de habitación: permitir letras, números, guiones y espacios (opcional)
if (empty($numero)) {
    $errores['numero'] = "El número de habitación es obligatorio.";
} elseif (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $numero)) {
    $errores['numero'] = "El número de habitación solo puede contener letras, números, espacios, guiones (-) o guiones bajos (_).";
} elseif (strlen($numero) > 20) {
    $errores['numero'] = "El número de habitación es demasiado largo (máx. 20 caracteres).";
}

    // Validar tipo
    $tiposValidos = ['simple', 'doble', 'triple', 'cuadruple', 'familiar', 'suite de sal'];
    if (empty($tipo) || !in_array($tipo, $tiposValidos)) {
        $errores['tipo'] = "Por favor, selecciona un tipo de habitación válido.";
    }

    // Validar precio por noche
    if (empty($precioNoche)) {
        $errores['precioNoche'] = "El precio por noche es obligatorio.";
    } else {
        // Convertir a número
        $precio = filter_var($precioNoche, FILTER_VALIDATE_FLOAT);
        if ($precio === false || $precio <= 0) {
            $errores['precioNoche'] = "El precio debe ser un número válido mayor que 0.";
        }
    }

    // Validar estado
    $estadosValidos = ['disponible', 'ocupada', 'mantenimiento'];
    if (empty($estado) || !in_array($estado, $estadosValidos)) {
        $errores['estado'] = "Estado inválido.";
    }

    // Solo continuar si NO hay errores
    if (empty($errores)) {
        // Subir imagen si existe
        $rutaFoto = null;
        if ($foto) {
            $directorio = __DIR__ . '/../../assets/img/habitaciones/';
            $rutaArchivo = $directorio . basename($foto);
            $extension = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $errores['foto'] = "Solo se permiten archivos JPG, JPEG, PNG o GIF.";
            } elseif (!move_uploaded_file($_FILES['foto']['tmp_name'], $rutaArchivo)) {
                $errores['foto'] = "Error al subir la imagen.";
            } else {
                $rutaFoto = $foto;
            }
        }

        // Si no hubo error en la foto (o no hay foto), insertar en BD
        if (empty($errores)) {
            try {
                if ($rutaFoto !== null) {
                    $stmt = $pdo->prepare("INSERT INTO Habitacion (numero, tipo, precioNoche, estado, foto) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$numero, $tipo, $precio, $estado, $rutaFoto]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO Habitacion (numero, tipo, precioNoche, estado) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$numero, $tipo, $precio, $estado]);
                }
                $mensaje = "Habitación registrada exitosamente.";
            } catch (PDOException $e) {
                $errores['general'] = "Error al registrar la habitación. Puede que el número ya exista.";
            }
        }
    }
}

$titulo_pagina = "Crear Habitación - Hotel Yokoso";

// Función auxiliar para mostrar error de un campo
function mostrarError($campo, $errores) {
    if (!empty($errores[$campo])) {
        return '<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>' . htmlspecialchars($errores[$campo]) . '</div>';
    }
    return '';
}

// Función para recordar valor en input o select
function recordar($campo, $default = '') {
    return $_POST[$campo] ?? $default;
}

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Registrar Nueva Habitación</h2>
            <a href="ver_habitaciones.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Habitaciones
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
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Número de Habitación *</label>
                    <input type="text" class="form-control" name="numero" value="' . htmlspecialchars(recordar('numero')) . '" required>
                    ' . mostrarError('numero', $errores) . '
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipo de Habitación *</label>
                    <select class="form-select" name="tipo" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="simple" ' . (recordar('tipo') === 'simple' ? 'selected' : '') . '>Simple</option>
                        <option value="doble" ' . (recordar('tipo') === 'doble' ? 'selected' : '') . '>Doble</option>
                        <option value="triple" ' . (recordar('tipo') === 'triple' ? 'selected' : '') . '>Triple</option>
                        <option value="cuadruple" ' . (recordar('tipo') === 'cuadruple' ? 'selected' : '') . '>Cuádruple</option>
                        <option value="familiar" ' . (recordar('tipo') === 'familiar' ? 'selected' : '') . '>Familiar</option>
                        <option value="suite" ' . (recordar('tipo') === 'suite' ? 'selected' : '') . '>Suite de Sal</option>
                        
                    </select>
                    ' . mostrarError('tipo', $errores) . '
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Precio por Noche (Bs.) *</label>
                    <input type="number" class="form-control" name="precioNoche" step="0.01" min="0.01" value="' . htmlspecialchars(recordar('precioNoche')) . '" required>
                    ' . mostrarError('precioNoche', $errores) . '
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estado *</label>
                    <select class="form-select" name="estado" required>
                        <option value="disponible" ' . (recordar('estado', 'disponible') === 'disponible' ? 'selected' : '') . '>Disponible</option>
                        <option value="ocupada" ' . (recordar('estado') === 'ocupada' ? 'selected' : '') . '>Ocupada</option>
                        <option value="mantenimiento" ' . (recordar('estado') === 'mantenimiento' ? 'selected' : '') . '>En mantenimiento</option>
                    </select>
                    ' . mostrarError('estado', $errores) . '
                </div>
            </div>

            <div class="mt-3 mb-3">
                <label class="form-label">Foto de la Habitación</label>
                <input type="file" class="form-control" name="foto" accept="image/*">
                ' . mostrarError('foto', $errores) . '
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