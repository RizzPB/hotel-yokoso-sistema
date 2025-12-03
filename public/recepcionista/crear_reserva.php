<?php
// public/recepcionista/crear_reserva.php


define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'crear_reserva';
require_once __DIR__ . '/../../config/database.php';

// ❤️ Errores por campo
$errores = [
    'nombre_huesped' => '',
    'fechaInicio' => '',
    'fechaFin' => '',
    'habitaciones' => '',
    'total' => '',
    'general' => ''
];

$mensaje = '';

// Cargar datos
$tiposHabitacion = $pdo->query("SELECT DISTINCT tipo FROM Habitacion WHERE estado = 'disponible' ORDER BY tipo")->fetchAll(PDO::FETCH_COLUMN);
$habitaciones    = $pdo->query("SELECT idHabitacion, numero, tipo, precioNoche FROM Habitacion WHERE estado = 'disponible' ORDER BY numero")->fetchAll(PDO::FETCH_ASSOC);
$paquetes        = $pdo->query("SELECT idPaquete, nombre, precio FROM PaqueteTuristico WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);

// Valores por defecto
$campos = [
    'nombre_huesped' => '',
    'idPaquete' => '',
    'fechaInicio' => '',
    'fechaFin' => '',
    'anticipo' => '0',
    'total' => '0',
    'habitaciones' => []
];

// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = [
        'nombre_huesped' => trim($_POST['nombre_huesped'] ?? ''),
        'idPaquete' => $_POST['idPaquete'] ?? '',
        'fechaInicio' => $_POST['fechaInicio'] ?? '',
        'fechaFin' => $_POST['fechaFin'] ?? '',
        'anticipo' => floatval($_POST['anticipo'] ?? 0),
        'total' => floatval($_POST['total'] ?? 0),
        'habitaciones' => $_POST['habitaciones'] ?? []
    ];

    $habitacionesSeleccionadas = $campos['habitaciones'];

    // ✨ VALIDACIONES
    if (empty($campos['nombre_huesped'])) {
        $errores['nombre_huesped'] = "El nombre del huésped es obligatorio.";
    } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/', $campos['nombre_huesped'])) {
        $errores['nombre_huesped'] = "El nombre solo puede contener letras y espacios.";
    }

    if (empty($campos['fechaInicio'])) {
        $errores['fechaInicio'] = "La fecha de entrada es obligatoria.";
    } elseif (strtotime($campos['fechaInicio']) < strtotime(date('Y-m-d'))) {
        $errores['fechaInicio'] = "La fecha de entrada no puede ser en el pasado.";
    }

    if (empty($campos['fechaFin'])) {
        $errores['fechaFin'] = "La fecha de salida es obligatoria.";
    } elseif (!empty($campos['fechaInicio']) && strtotime($campos['fechaFin']) <= strtotime($campos['fechaInicio'])) {
        $errores['fechaFin'] = "La fecha de salida debe ser posterior a la de entrada.";
    }

    if (empty($habitacionesSeleccionadas)) {
        $errores['habitaciones'] = "Debes seleccionar al menos una habitación.";
    }

    if ($campos['total'] <= 0) {
        $errores['total'] = "El total debe ser mayor a cero. Asegúrate de seleccionar fechas y habitaciones.";
    }

    // ✅ Si no hay errores, procesar
    if (!array_filter($errores)) {
        try {
            $pdo->beginTransaction();

            $partes = explode(' ', $campos['nombre_huesped'], 2);
            $nombre = $partes[0] ?? '';
            $apellido = $partes[1] ?? '';

            $stmt = $pdo->prepare("INSERT INTO Huesped (nombre, apellido, activo) VALUES (?, ?, 1)");
            $stmt->execute([$nombre, $apellido]);
            $idHuesped = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO Reserva (idHuesped, idPaquete, fechaInicio, fechaFin, anticipo, total, estado) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')");
            $stmt->execute([$idHuesped, $campos['idPaquete'], $campos['fechaInicio'], $campos['fechaFin'], $campos['anticipo'], $campos['total']]);
            $idReserva = $pdo->lastInsertId();

            $stmtHab = $pdo->prepare("INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche) VALUES (?, ?, (SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?))");
            $stmtUpd = $pdo->prepare("UPDATE Habitacion SET estado = 'reservada' WHERE idHabitacion = ? AND estado = 'disponible'");

            foreach ($habitacionesSeleccionadas as $idHab) {
                $stmtHab->execute([$idReserva, $idHab, $idHab]);
                $stmtUpd->execute([$idHab]);
            }

            $pdo->commit();
            $mensaje = "¡Reserva creada exitosamente para <strong>" . htmlspecialchars($campos['nombre_huesped']) . "</strong>! ID: #$idReserva";
            $_POST = []; // Limpiar después de éxito

        } catch (Exception $e) {
            $pdo->rollBack();
            $errores['general'] = "Error al crear la reserva. Es posible que alguna habitación ya no esté disponible.";
        }
    }
}

$titulo_pagina = "Crear Reserva Anticipada - Hotel Yokoso";

// ❤️ Función para mostrar errores
function mostrarError($campo, $errores) {
    if (!empty($errores[$campo])) {
        return '<div class="text-danger mt-1"><small><i class="fas fa-exclamation-circle me-1"></i>' . htmlspecialchars($errores[$campo]) . '</small></div>';
    }
    return '';
}

$contenido_principal = '
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-rojo fw-bold">Crear Reserva Anticipada</h2>
        <a href="ver_reservas.php" class="btn btn-yokoso btn-lg rounded-pill px-5 shadow-lg">Ver Reservas</a>
    </div>

    ' . (!empty($mensaje) ? '<div class="alert alert-success text-center mx-auto mb-4" style="max-width:900px;"><i class="fas fa-check-circle fa-2x"></i><br>' . $mensaje . '</div>' : '') . '
    ' . (!empty($errores['general']) ? '<div class="alert alert-danger text-center mx-auto mb-4" style="max-width:900px;"><i class="fas fa-times-circle fa-2x"></i><br>' . htmlspecialchars($errores['general']) . '</div>' : '') . '

    <div class="row justify-content-center">
        <div class="col-xl-10 col-xxl-9">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5 p-lg-6">
                    <form method="POST" id="formReserva">

                        <!-- NOMBRE DEL HUÉSPED -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-8">
                                <label class="form-label fw-bold text-dark">Reservar a nombre de *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" 
                                       name="nombre_huesped" placeholder="Ej. Juan Pérez, María Gómez" 
                                       value="' . htmlspecialchars($campos['nombre_huesped']) . '" required>
                                <small class="text-muted">Nombre de quien hará la reserva (solo letras y espacios)</small>
                                ' . mostrarError('nombre_huesped', $errores) . '
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Paquete Turístico (Opcional)</label>
                                <select class="form-select form-select-lg rounded-pill" name="idPaquete">
                                    <option value="">Sin paquete</option>
                                    ' . implode('', array_map(fn($p) => '<option value="'.$p['idPaquete'].'" data-precio="'.number_format($p['precio'], 2, '.', '').'">'.htmlspecialchars($p['nombre']).' - Bs. '.number_format($p['precio'],2).'</option>', $paquetes)) . '
                                </select>
                            </div>
                        </div>

                        <!-- FECHAS -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Fecha de Entrada *</label>
                                <input type="date" class="form-control form-control-lg rounded-pill" name="fechaInicio" 
                                       value="' . htmlspecialchars($campos['fechaInicio']) . '" min="' . date('Y-m-d') . '" required>
                                ' . mostrarError('fechaInicio', $errores) . '
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Fecha de Salida *</label>
                                <input type="date" class="form-control form-control-lg rounded-pill" 
                                       name="fechaFin" value="' . htmlspecialchars($campos['fechaFin']) . '" required>
                                ' . mostrarError('fechaFin', $errores) . '
                            </div>
                        </div>

                        <!-- HABITACIONES -->
                        <hr class="my-5 border-secondary">
                        <h4 class="text-rojo fw-bold mb-4">Habitaciones Disponibles</h4>
                        ' . mostrarError('habitaciones', $errores) . '
                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <select class="form-select form-select-lg rounded-pill" id="filtroHabitacion">
                                    <option value="">Todas las disponibles</option>
                                    ' . implode('', array_map(fn($t) => '<option value="'.htmlspecialchars($t).'">'.ucwords($t).'</option>', $tiposHabitacion)) . '
                                </select>
                            </div>
                        </div>

                        <div class="row g-4" id="listaHabitaciones" style="max-height:500px; overflow-y:auto;">
                            ' . implode('', array_map(function($hab) use ($campos) {
                                $checked = in_array($hab['idHabitacion'], $campos['habitaciones']) ? 'checked' : '';
                                return '
                                <div class="col-md-6 col-lg-4 habitacion-item" data-tipo="'.htmlspecialchars($hab['tipo']).'">
                                    <div class="card h-100 border-0 shadow hover-lift transition">
                                        <div class="card-body text-center p-4">
                                            <h3 class="text-rojo fw-bold mb-1">Hab. '.$hab['numero'].'</h3>
                                            <p class="text-muted fw-bold">'.ucwords($hab['tipo']).'</p>
                                            <h4 class="text-success fw-bold mb-3" data-precio="'.number_format($hab['precioNoche'], 2, '.', '').'">Bs. '.number_format($hab['precioNoche'],2).'</h4>
                                            <input type="checkbox" name="habitaciones[]" value="'.$hab['idHabitacion'].'" id="hab_'.$hab['idHabitacion'].'" class="btn-check" '.$checked.'>
                                            <label for="hab_'.$hab['idHabitacion'].'" class="btn btn-yokoso btn-lg w-100 rounded-pill shadow-sm">Seleccionar</label>
                                        </div>
                                    </div>
                                </div>';
                            }, $habitaciones)) . '
                        </div>

                        <!-- ANTICIPO Y TOTAL -->
                        <hr class="my-5 border-secondary">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Anticipo (Bs.)</label>
                                <input type="number" step="0.01" class="form-control form-control-lg rounded-pill" 
                                       name="anticipo" value="' . htmlspecialchars($campos['anticipo']) . '" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Total Estimado (Bs.) *</label>
                                <input type="number" step="0.01" class="form-control form-control-lg bg-warning-subtle fw-bold text-dark rounded-pill" 
                                       name="total" value="' . htmlspecialchars($campos['total']) . '" readonly required>
                                ' . mostrarError('total', $errores) . '
                            </div>
                        </div>

                        <!-- BOTONES -->
                        <div class="mt-5 pt-4 border-top text-end">
                            <a href="panel_recepcionista.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill me-3">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-yokoso btn-lg px-5 rounded-pill shadow-lg">
                                <i class="fas fa-calendar-check me-2"></i> Crear Reserva
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    function calcularTotal() {
        const fechaInicio = document.querySelector("[name=fechaInicio]").value;
        const fechaFin    = document.querySelector("[name=fechaFin]").value;

        if (!fechaInicio || !fechaFin) {
            document.querySelector("[name=total]").value = "";
            return;
        }

        const inicio = new Date(fechaInicio);
        const fin    = new Date(fechaFin);
        const noches = (fin - inicio) / (1000 * 60 * 60 * 24);

        if (noches <= 0) {
            document.querySelector("[name=total]").value = "";
            return;
        }

        let subtotal = 0;
        document.querySelectorAll("[name=\"habitaciones[]\"]:checked").forEach(cb => {
            const card = cb.closest(".card");
            const precio = parseFloat(card.querySelector("h4.text-success").dataset.precio);
            if (!isNaN(precio)) subtotal += precio * noches;
        });

        const paqueteSelect = document.querySelector("[name=idPaquete]");
        if (paqueteSelect?.value) {
            const precioPaquete = parseFloat(paqueteSelect.selectedOptions[0]?.dataset.precio || 0);
            if (!isNaN(precioPaquete)) subtotal += precioPaquete;
        }

        document.querySelector("[name=total]").value = subtotal.toFixed(2);
    }

    document.querySelectorAll("[name=fechaInicio], [name=fechaFin], [name=idPaquete]").forEach(el => el.addEventListener("change", calcularTotal));
    document.querySelectorAll("[name=\"habitaciones[]\"]").forEach(cb => cb.addEventListener("change", calcularTotal));

    document.getElementById("filtroHabitacion")?.addEventListener("change", function () {
        const tipo = this.value.toLowerCase();
        document.querySelectorAll(".habitacion-item").forEach(item => {
            item.style.display = (tipo === "" || item.dataset.tipo.toLowerCase() === tipo) ? "block" : "none";
        });
    });

    calcularTotal();
});
</script>
';

include 'plantilla_recepcionista.php';
?>