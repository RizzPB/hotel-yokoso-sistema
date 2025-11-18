<?php
// public/vistas/recepcionista/editar_reserva.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Obtener ID de la reserva
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ver_reservas.php");
    exit;
}

// Obtener la reserva
$stmt = $pdo->prepare("
    SELECT r.idReserva, r.fechaInicio, r.fechaFin, r.anticipo, r.total, r.estado, r.idHuesped, r.idPaquete
    FROM Reserva r
    WHERE r.idReserva = ?
");
$stmt->execute([$id]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    header("Location: ver_reservas.php");
    exit;
}

// Obtener las habitaciones asociadas a esta reserva
$stmt = $pdo->prepare("
    SELECT idHabitacion
    FROM ReservaHabitacion
    WHERE idReserva = ?
");
$stmt->execute([$id]);
$habitacionesReserva = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Obtener todos los tipos de habitación únicos (para el filtro)
$stmt = $pdo->prepare("SELECT DISTINCT tipo FROM Habitacion WHERE estado = 'disponible' ORDER BY tipo ASC");
$stmt->execute();
$tiposHabitacion = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Obtener todas las habitaciones disponibles
$stmt = $pdo->prepare("SELECT idHabitacion, numero, tipo, precioNoche FROM Habitacion WHERE estado = 'disponible'");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener paquetes turísticos activos
$stmt = $pdo->prepare("SELECT idPaquete, nombre, descripcion, precio FROM PaqueteTuristico WHERE activo = 1");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener huéspedes
$stmt = $pdo->prepare("SELECT idHuesped, nombre, apellido FROM Huesped WHERE activo = 1");
$stmt->execute();
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si el formulario fue enviado, procesarlo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idHuesped = $_POST['idHuesped'];
    $idPaquete = $_POST['idPaquete'] ?: null;
    $fechaInicio = $_POST['fechaInicio'];
    $fechaFin = $_POST['fechaFin'];
    $anticipo = $_POST['anticipo'] ?: 0;
    $total = $_POST['total'];
    $estado = $_POST['estado'];

    // Validaciones básicas
    if (empty($idHuesped) || empty($fechaInicio) || empty($fechaFin) || empty($total)) {
        $error = "Los campos huésped, fechas y total son obligatorios.";
    } elseif (strtotime($fechaFin) <= strtotime($fechaInicio)) {
        $error = "La fecha de salida debe ser mayor a la fecha de entrada.";
    } else {
        // Actualizar la reserva
        $stmt = $pdo->prepare("
            UPDATE Reserva
            SET idHuesped = ?, idPaquete = ?, fechaInicio = ?, fechaFin = ?, anticipo = ?, total = ?, estado = ?
            WHERE idReserva = ?
        ");
        if ($stmt->execute([$idHuesped, $idPaquete, $fechaInicio, $fechaFin, $anticipo, $total, $estado, $id])) {
            // Eliminar las habitaciones anteriores de esta reserva
            $stmt = $pdo->prepare("DELETE FROM ReservaHabitacion WHERE idReserva = ?");
            $stmt->execute([$id]);

            // Insertar las nuevas habitaciones
            $habitacionesSeleccionadas = $_POST['habitaciones'] ?? [];
            foreach ($habitacionesSeleccionadas as $idHab) {
                $stmt = $pdo->prepare("
                    INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche)
                    VALUES (?, ?, (SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?))
                ");
                $stmt->execute([$id, $idHab, $idHab]);

                // Actualizar estado de la habitación a ocupada
                $stmt = $pdo->prepare("UPDATE Habitacion SET estado = 'ocupada' WHERE idHabitacion = ?");
                $stmt->execute([$idHab]);
            }

            $mensaje = "Reserva actualizada exitosamente.";
        } else {
            $error = "Error al actualizar la reserva.";
        }
    }
}

$titulo_pagina = "Editar Reserva - Hotel Yokoso";

$contenido_principal = '
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-rojo fw-bold">Editar Reserva #'. htmlspecialchars($reserva['idReserva']) .'</h2>
    </div>

    ' . (isset($mensaje) ? '<div class="alert alert-success">' . htmlspecialchars($mensaje) . '</div>' : '') . '
    ' . (isset($error) ? '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>' : '') . '

    <div class="reserva-form-container">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Huésped *</label>
                    <select class="form-select" name="idHuesped" required>
                        ' . implode('', array_map(function($h) use ($reserva) {
                            $selected = $h['idHuesped'] == $reserva['idHuesped'] ? 'selected' : '';
                            return '<option value="' . $h['idHuesped'] . '" ' . $selected . '>' . htmlspecialchars($h['nombre'] . ' ' . $h['apellido']) . '</option>';
                        }, $huespedes)) . '
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Paquete Turístico (Opcional)</label>
                    <select class="form-select" name="idPaquete">
                        <option value="">No deseo paquete</option>
                        ' . implode('', array_map(function($pkg) use ($reserva) {
                            $selected = $pkg['idPaquete'] == $reserva['idPaquete'] ? 'selected' : '';
                            return '<option value="' . $pkg['idPaquete'] . '" ' . $selected . '>' . htmlspecialchars($pkg['nombre']) . ' - Bs. ' . htmlspecialchars($pkg['precio']) . '</option>';
                        }, $paquetes)) . '
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Fecha de Entrada *</label>
                    <input type="date" class="form-control" name="fechaInicio" value="' . htmlspecialchars($reserva['fechaInicio']) . '" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha de Salida *</label>
                    <input type="date" class="form-control" name="fechaFin" value="' . htmlspecialchars($reserva['fechaFin']) . '" required>
                </div>
            </div>

            <!-- Sección de Habitaciones -->
            <div class="mt-4">
                <h4 class="text-rojo">Habitaciones</h4>
                <label class="form-label">Filtrar por tipo de habitación</label>
                <select class="form-select mb-3" id="filtroHabitacion">
                    <option value="">Ver todas las disponibles</option>
                    ' . implode('', array_map(fn($tipo) => "<option value=\"" . htmlspecialchars($tipo) . "\">" . htmlspecialchars($tipo) . "</option>", $tiposHabitacion)) . '
                </select>

                <div id="listaHabitaciones" class="border p-3 rounded bg-light">
                    ' . implode('', array_map(function($hab) use ($habitacionesReserva) {
                        $checked = in_array($hab['idHabitacion'], $habitacionesReserva) ? 'checked' : '';
                        return '
                            <div class="habitacion-item mb-2 p-2 bg-white rounded shadow-sm" data-tipo="' . $hab['tipo'] . '">
                                <input type="checkbox" name="habitaciones[]" value="' . $hab['idHabitacion'] . '" id="hab_' . $hab['idHabitacion'] . '" ' . $checked . '>
                                <label for="hab_' . $hab['idHabitacion'] . '" class="fw-semibold">
                                    ' . htmlspecialchars($hab['numero']) . ' (' . htmlspecialchars($hab['tipo']) . ') - Bs. ' . htmlspecialchars($hab['precioNoche']) . '/noche
                                </label>
                            </div>';
                    }, $habitaciones)) . '
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Anticipo (Bs.)</label>
                    <input type="number" class="form-control" name="anticipo" step="0.01" value="' . htmlspecialchars($reserva['anticipo']) . '">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Total (Bs.) *</label>
                    <input type="number" class="form-control" name="total" step="0.01" value="' . htmlspecialchars($reserva['total']) . '" required>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado">
                    <option value="pendiente" ' . ($reserva['estado'] === 'pendiente' ? 'selected' : '') . '>Pendiente</option>
                    <option value="confirmada" ' . ($reserva['estado'] === 'confirmada' ? 'selected' : '') . '>Confirmada</option>
                    <option value="cancelada" ' . ($reserva['estado'] === 'cancelada' ? 'selected' : '') . '>Cancelada</option>
                    <option value="finalizada" ' . ($reserva['estado'] === 'finalizada' ? 'selected' : '') . '>Finalizada</option>
                </select>
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="ver_reservas.php" class="btn btn-cancelar me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <script>
        // Filtrar habitaciones por tipo
        document.getElementById("filtroHabitacion").addEventListener("change", function() {
            const tipo = this.value;
            const items = document.querySelectorAll("#listaHabitaciones .habitacion-item");

            items.forEach(item => {
                if (tipo === "" || item.getAttribute("data-tipo") === tipo) {
                    item.style.display = "flex";
                } else {
                    item.style.display = "none";
                }
            });
        });
    </script>
';

include 'plantilla_recepcionista.php';
?>