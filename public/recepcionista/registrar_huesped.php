<?php
// public/resepcionista/registrar_huesped.php


define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'registrar_huesped';
require_once __DIR__ . '/../../config/database.php';

// ❤️ Errores por campo
$errores = [
    'nombre' => '',
    'apellido' => '',
    'tipoDocumento' => '',
    'nroDocumento' => '',
    'email' => '',
    'telefono' => '',
    'motivoVisita' => '',
    'preferenciaAlimentaria' => '',
    'habitaciones' => '',
    'fechaInicio' => '',
    'fechaFin' => '',
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
    'idPaquete' => '',
    'habitaciones' => [],
    'fechaInicio' => date('Y-m-d'),
    'fechaFin' => date('Y-m-d', strtotime('+7 days'))
];

// Cargar datos
$stmt = $pdo->prepare("SELECT DISTINCT tipo FROM Habitacion WHERE estado = 'disponible' ORDER BY tipo");
$stmt->execute();
$tiposHabitacion = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("SELECT idHabitacion, numero, tipo, precioNoche FROM Habitacion WHERE estado = 'disponible' ORDER BY numero");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT idPaquete, nombre, descripcion, precio FROM PaqueteTuristico WHERE activo = 1");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar POST
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
        'idPaquete' => $_POST['idPaquete'] ?? '',
        'habitaciones' => $_POST['habitaciones'] ?? [],
        'fechaInicio' => $_POST['fechaInicio'] ?? date('Y-m-d'),
        'fechaFin' => $_POST['fechaFin'] ?? date('Y-m-d', strtotime('+7 days'))
    ];

    $habitacionesSeleccionadas = $campos['habitaciones'];

    // ✨ VALIDACIONES
    if (empty($campos['nombre'])) {
        $errores['nombre'] = "El nombre es obligatorio.";
    } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/', $campos['nombre'])) {
        $errores['nombre'] = "El nombre solo puede contener letras y espacios.";
    }

    if (empty($campos['apellido'])) {
        $errores['apellido'] = "El apellido es obligatorio.";
    } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/', $campos['apellido'])) {
        $errores['apellido'] = "El apellido solo puede contener letras y espacios.";
    }

    if (empty($campos['tipoDocumento'])) {
        $errores['tipoDocumento'] = "Selecciona un tipo de documento.";
    }

    if (empty($campos['nroDocumento'])) {
        $errores['nroDocumento'] = "El número de documento es obligatorio.";
    } elseif (strlen($campos['nroDocumento']) < 5) {
        $errores['nroDocumento'] = "El documento debe tener al menos 5 caracteres.";
    }

    if (!empty($campos['email']) && !filter_var($campos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = "Por favor, ingresa un correo electrónico válido.";
    }

    if (!empty($campos['telefono']) && !preg_match('/^[0-9+\-\s()]+$/', $campos['telefono'])) {
        $errores['telefono'] = "El teléfono solo puede contener números y símbolos como +, -, ( ).";
    }

    // ✨ NUEVO: Validar motivo de visita (sin números)
    if (!empty($campos['motivoVisita']) && !preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s.,;:¡!¿?()]+$/u', $campos['motivoVisita'])) {
        $errores['motivoVisita'] = "El motivo de visita solo puede contener letras, espacios y signos de puntuación.";
    }

    // ✨ NUEVO: Validar preferencias alimentarias (solo letras y espacios)
    if (!empty($campos['preferenciaAlimentaria']) && !preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/u', $campos['preferenciaAlimentaria'])) {
        $errores['preferenciaAlimentaria'] = "Las preferencias alimentarias solo pueden contener letras y espacios.";
    }

    // ✅ ¡HABITACIONES OBLIGATORIAS!
    if (empty($habitacionesSeleccionadas)) {
        $errores['habitaciones'] = "Debes seleccionar al menos una habitación para registrar al huésped.";
    }

    $fechaInicio = $campos['fechaInicio'];
    $fechaFin = $campos['fechaFin'];
    if (empty($fechaInicio) || empty($fechaFin)) {
        $errores['fechaInicio'] = "Las fechas de ingreso y salida son obligatorias.";
    } elseif (strtotime($fechaFin) <= strtotime($fechaInicio)) {
        $errores['fechaFin'] = "La fecha de salida debe ser posterior a la de ingreso.";
    } elseif (strtotime($fechaInicio) < strtotime(date('Y-m-d'))) {
        $errores['fechaInicio'] = "La fecha de ingreso no puede ser en el pasado.";
    }

    // ✅ Si no hay errores, guardar
    if (!array_filter($errores)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO Huesped (nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([
                $campos['nombre'], $campos['apellido'], $campos['tipoDocumento'], $campos['nroDocumento'],
                $campos['procedencia'], $campos['email'], $campos['telefono'], $campos['motivoVisita'], $campos['preferenciaAlimentaria']
            ]);
            $idHuesped = $pdo->lastInsertId();

            // Calcular total
            $total = 0;
            $preciosHabitaciones = [];
            foreach ($habitacionesSeleccionadas as $idHab) {
                $stmt = $pdo->prepare("SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?");
                $stmt->execute([$idHab]);
                $precio = $stmt->fetchColumn();
                $total += $precio;
                $preciosHabitaciones[$idHab] = $precio;
            }
            if (!empty($campos['idPaquete'])) {
                $stmt = $pdo->prepare("SELECT precio FROM PaqueteTuristico WHERE idPaquete = ?");
                $stmt->execute([$campos['idPaquete']]);
                $total += $stmt->fetchColumn();
            }

            // ✅ CORREGIDO: Reserva → "confirmada" (no "ocupada")
            $stmt = $pdo->prepare("INSERT INTO Reserva (idHuesped, idPaquete, fechaInicio, fechaFin, total, anticipo, estado) VALUES (?, ?, ?, ?, ?, ?, 'confirmada')");
            $stmt->execute([$idHuesped, $campos['idPaquete'], $fechaInicio, $fechaFin, $total, $total]);
            $idReserva = $pdo->lastInsertId();

            // Asignar habitaciones → "ocupada"
            $stmtInsert = $pdo->prepare("INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche) VALUES (?, ?, ?)");
            $stmtUpdate = $pdo->prepare("UPDATE Habitacion SET estado = 'ocupada' WHERE idHabitacion = ?");

            foreach ($habitacionesSeleccionadas as $idHab) {
                $stmtInsert->execute([$idReserva, $idHab, $preciosHabitaciones[$idHab]]);
                $stmtUpdate->execute([$idHab]);
            }

            $pdo->commit();
            $mensaje = "¡Huésped registrado y habitación asignada con éxito! Reserva #$idReserva";

        } catch (Exception $e) {
            $pdo->rollBack();
            $errores['general'] = "Error al registrar el huésped. Puede que la habitación ya no esté disponible.";
        }
    }
}

$titulo_pagina = "Check-in: Registrar Huésped - Hotel Yokoso";

// ❤️ Función para mostrar errores debajo de cada campo
function mostrarError($campo, $errores) {
    if (!empty($errores[$campo])) {
        return '<div class="text-danger mt-1"><small><i class="fas fa-exclamation-circle me-1"></i>' . htmlspecialchars($errores[$campo]) . '</small></div>';
    }
    return '';
}

$contenido_principal = '
<div class="container py-5">
    <h2 class="text-rojo fw-bold text-center mb-5">Check-in: Registrar Huésped</h2>

    ' . (!empty($mensaje) ? '<div class="alert alert-success text-center mx-auto mb-4" style="max-width: 900px;"><i class="fas fa-check-circle fa-3x mb-3"></i><br><strong>' . htmlspecialchars($mensaje) . '</strong></div>' : '') . '
    ' . (!empty($errores['general']) ? '<div class="alert alert-danger text-center mx-auto mb-4" style="max-width: 900px;"><i class="fas fa-times-circle fa-3x mb-3"></i><br>' . htmlspecialchars($errores['general']) . '</div>' : '') . '

    <div class="row justify-content-center">
        <div class="col-xl-10 col-xxl-9">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5 p-lg-6">
                    <form method="POST">

                        <!-- Nombre y Apellido -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Nombre *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="nombre" value="' . htmlspecialchars($campos['nombre']) . '" required placeholder="Ej. Juan Carlos">
                                ' . mostrarError('nombre', $errores) . '
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Apellido *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="apellido" value="' . htmlspecialchars($campos['apellido']) . '" required placeholder="Ej. Pérez Gómez">
                                ' . mostrarError('apellido', $errores) . '
                            </div>
                        </div>

                        <!-- Documento -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Tipo Documento *</label>
                                <select class="form-select form-select-lg rounded-pill" name="tipoDocumento" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="Carnet" ' . ($campos['tipoDocumento'] === 'Carnet' ? 'selected' : '') . '>Carnet de Identidad</option>
                                    <option value="DNI" ' . ($campos['tipoDocumento'] === 'DNI' ? 'selected' : '') . '>DNI</option>
                                    <option value="Pasaporte" ' . ($campos['tipoDocumento'] === 'Pasaporte' ? 'selected' : '') . '>Pasaporte</option>
                                </select>
                                ' . mostrarError('tipoDocumento', $errores) . '
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Nro. Documento *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="nroDocumento" value="' . htmlspecialchars($campos['nroDocumento']) . '" required placeholder="Ej. 12345678">
                                ' . mostrarError('nroDocumento', $errores) . '
                            </div>
                        </div>

                        <!-- Contacto -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Procedencia</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="procedencia" value="' . htmlspecialchars($campos['procedencia']) . '" placeholder="Ej. La Paz, Santa Cruz">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Teléfono</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="telefono" value="' . htmlspecialchars($campos['telefono']) . '" placeholder="Ej. 70707070">
                                ' . mostrarError('telefono', $errores) . '
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Email</label>
                                <input type="email" class="form-control form-control-lg rounded-pill" name="email" value="' . htmlspecialchars($campos['email']) . '" placeholder="Ej. juan@gmail.com">
                                ' . mostrarError('email', $errores) . '
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Motivo de Visita</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="motivoVisita" value="' . htmlspecialchars($campos['motivoVisita']) . '" placeholder="Ej. Turismo, Negocios">
                                ' . mostrarError('motivoVisita', $errores) . '
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark">Preferencias Alimentarias</label>
                            <textarea class="form-control form-control-lg rounded-4" rows="3" name="preferenciaAlimentaria" placeholder="Ej. Sin gluten, vegetariano, alérgico al maní...">' . htmlspecialchars($campos['preferenciaAlimentaria']) . '</textarea>
                            ' . mostrarError('preferenciaAlimentaria', $errores) . '
                        </div>

                        <!-- Fechas -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Fecha de Ingreso *</label>
                                <input type="date" class="form-control form-control-lg rounded-pill" name="fechaInicio" value="' . htmlspecialchars($campos['fechaInicio']) . '" required readonly>
                                ' . mostrarError('fechaInicio', $errores) . '
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Fecha de Salida *</label>
                                <input type="date" class="form-control form-control-lg rounded-pill" name="fechaFin" value="' . htmlspecialchars($campos['fechaFin']) . '" required min="' . date('Y-m-d') . '">
                                ' . mostrarError('fechaFin', $errores) . '
                                <div class="form-text">Elige la fecha en que el huésped se retirará.</div>
                            </div>
                        </div>

                        <!-- Habitaciones -->
                        <hr class="my-5 border-secondary">
                        <h4 class="text-rojo fw-bold mb-4">Seleccionar Habitación(es) Disponibles</h4>
                        ' . mostrarError('habitaciones', $errores) . '
                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <select class="form-select form-select-lg rounded-pill" id="filtroHabitacion">
                                    <option value="">Todas las disponibles</option>
                                    ' . implode('', array_map(fn($t) => '<option value="'.htmlspecialchars($t).'">'.ucwords($t).'</option>', $tiposHabitacion)) . '
                                </select>
                            </div>
                        </div>

                        <div class="row g-4" id="listaHabitaciones">
                            ' . implode('', array_map(function($hab) use ($campos) {
                                $checked = in_array($hab['idHabitacion'], $campos['habitaciones']) ? 'checked' : '';
                                return '
                                <div class="col-md-6 col-lg-4 habitacion-item" data-tipo="'.htmlspecialchars($hab['tipo']).'">
                                    <div class="card h-100 border-0 shadow hover-lift transition">
                                        <div class="card-body text-center p-4">
                                            <h3 class="text-rojo fw-bold mb-1">Hab. '.$hab['numero'].'</h3>
                                            <p class="text-muted fw-bold">'.ucwords($hab['tipo']).'</p>
                                            <h4 class="text-success fw-bold mb-3">Bs. '.number_format($hab['precioNoche'], 2).'</h4>
                                            <input type="checkbox" name="habitaciones[]" value="'.$hab['idHabitacion'].'" id="hab_'.$hab['idHabitacion'].'" class="btn-check" '.$checked.'>
                                            <label for="hab_'.$hab['idHabitacion'].'" class="btn btn-yokoso btn-lg w-100 rounded-pill shadow-sm">Seleccionar</label>
                                        </div>
                                    </div>
                                </div>';
                            }, $habitaciones)) . '
                        </div>

                        <!-- Paquetes -->
                        <hr class="my-5 border-secondary">
                        <h4 class="text-rojo fw-bold mb-4">Paquete Turístico (Opcional)</h4>
                        <div class="row g-4">
                            ' . implode('', array_map(function($pkg) use ($campos) {
                                $checked = ($campos['idPaquete'] == $pkg['idPaquete']) ? 'checked' : '';
                                return '
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <h5 class="text-rojo fw-bold">'.$pkg['nombre'].'</h5>
                                            <p class="small text-muted">'.$pkg['descripcion'].'</p>
                                            <h4 class="text-mostaza fw-bold">Bs. '.number_format($pkg['precio'],2).'</h4>
                                            <input type="radio" name="idPaquete" value="'.$pkg['idPaquete'].'" id="pkg_'.$pkg['idPaquete'].'" class="btn-check" '.$checked.'>
                                            <label for="pkg_'.$pkg['idPaquete'].'" class="btn btn-outline-yokoso btn-lg w-100 mt-3 rounded-pill">Elegir</label>
                                        </div>
                                    </div>
                                </div>';
                            }, $paquetes)) . '
                        </div>

                        <!-- Botones -->
                        <div class="mt-5 pt-4 text-end">
                            <a href="panel_recepcionista.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill me-3">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-yokoso btn-lg px-5 rounded-pill shadow-lg">
                                <i class="fas fa-user-check me-2"></i> Registrar y Asignar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById("filtroHabitacion")?.addEventListener("change", function() {
    const tipo = this.value.toLowerCase();
    document.querySelectorAll(".habitacion-item").forEach(item => {
        const tipoHab = item.dataset.tipo.toLowerCase();
        item.style.display = (tipo === "" || tipoHab.includes(tipo)) ? "block" : "none";
    });
});
</script>
';

include 'plantilla_recepcionista.php';
?>