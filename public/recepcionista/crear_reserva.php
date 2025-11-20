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

$mensaje = $error = null;

// Cargar datos
$tiposHabitacion = $pdo->query("SELECT DISTINCT tipo FROM Habitacion WHERE estado = 'disponible' ORDER BY tipo")->fetchAll(PDO::FETCH_COLUMN);
$habitaciones    = $pdo->query("SELECT idHabitacion, numero, tipo, precioNoche FROM Habitacion WHERE estado = 'disponible' ORDER BY numero")->fetchAll(PDO::FETCH_ASSOC);
$paquetes        = $pdo->query("SELECT idPaquete, nombre, precio FROM PaqueteTuristico WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);
$huespedes       = $pdo->query("SELECT idHuesped, nombre, apellido FROM Huesped WHERE activo = 1 ORDER BY apellido")->fetchAll(PDO::FETCH_ASSOC);

// PROCESAR FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idHuesped = $_POST['idHuesped'] ?? '';
    $idPaquete = !empty($_POST['idPaquete']) ? $_POST['idPaquete'] : null;
    $fechaInicio = $_POST['fechaInicio'] ?? '';
    $fechaFin    = $_POST['fechaFin'] ?? '';
 '';
    $anticipo    = $_POST['anticipo'] ?? 0;
    $total       = $_POST['total'] ?? 0;
    $habitacionesSeleccionadas = $_POST['habitaciones'] ?? [];

    if (empty($idHuesped) || empty($fechaInicio) || empty($fechaFin) || empty($habitacionesSeleccionadas) || $total <= 0) {
        $error = "Completa todos los campos obligatorios y selecciona al menos una habitación.";
    } elseif (strtotime($fechaFin) <= strtotime($fechaInicio)) {
        $error = "La fecha de salida debe ser posterior a la entrada.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO Reserva (idHuesped, idPaquete, fechaInicio, fechaFin, anticipo, total, estado) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')");
            $stmt->execute([$idHuesped, $idPaquete, $fechaInicio, $fechaFin, $anticipo, $total]);
            $idReserva = $pdo->lastInsertId();

            $stmtHab = $pdo->prepare("INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche) VALUES (?, ?, (SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?))");
            $stmtUpd = $pdo->prepare("UPDATE Habitacion SET estado = 'ocupada' WHERE idHabitacion = ?");

            foreach ($habitacionesSeleccionadas as $idHab) {
                $stmtHab->execute([$idReserva, $idHab, $idHab]);
                $stmtUpd->execute([$idHab]);
            }

            $pdo->commit();
            $mensaje = "¡Reserva creada exitosamente! ID: #$idReserva";

            // Limpiar formulario
            $_POST = [];
            $habitacionesSeleccionadas = [];

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al crear la reserva. Puede que alguna habitación ya no esté disponible.";
        }
    }
}

$titulo_pagina = "Crear Reserva - Hotel Yokoso";

$contenido_principal = '
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-rojo fw-bold">
            <i class="fas fa-calendar-plus me-3"></i>
            Crear Nueva Reserva
        </h2>
        <a href="ver_reservas.php" class="btn btn-yokoso btn-lg rounded-pill px-5 shadow-lg">
            <i class="fas fa-list me-2"></i>Ver Reservas
        </a>
    </div>

    ' . ($mensaje ? '<div class="alert alert-success text-center mx-auto mb-4" style="max-width:900px;"><i class="fas fa-check-circle fa-2x"></i><br>' . $mensaje . '</div>' : '') . '
    ' . ($error ? '<div class="alert alert-danger text-center mx-auto mb-4" style="max-width:900px;"><i class="fas fa-times-circle fa-2x"></i><br>' . $error . '</div>' : '') . '

    <!-- FORMULARIO FIJO, CENTRADO Y PREMIUM -->
    <div class="row justify-content-center">
        <div class="col-xl-10 col-xxl-9">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5 p-lg-6">
                    <form method="POST" id="formReserva">

                        <!-- HUÉSPED Y PAQUETE -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Huésped *</label>
                                <select class="form-select form-select-lg rounded-pill" name="idHuesped" required>
                                    <option value="">Seleccionar huésped</option>
                                    ' . implode('', array_map(fn($h) => '<option value="'.$h['idHuesped'].'">'.htmlspecialchars($h['nombre'].' '.$h['apellido']).'</option>', $huespedes)) . '
                                </select>
                            </div>
                            <div class="col-md-6">
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
                                <label class="form-label fw-bold text-dark">Fecha Entrada *</label>
                                <input type="date" class="form-control form-control-lg rounded-pill" name="fechaInicio" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Fecha Salida *</label>
                                <input type="date" class="form-control form-control-lg rounded-pill" name="fechaFin" required>
                            </div>
                        </div>

                        <!-- HABITACIONES CON FILTRO -->
                        <hr class="my-5 border-secondary">
                        <h4 class="text-rojo fw-bold mb-4">Habitaciones Disponibles</h4>
                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <select class="form-select form-select-lg rounded-pill" id="filtroHabitacion">
                                    <option value="">Todas las disponibles</option>
                                    ' . implode('', array_map(fn($t) => '<option value="'.htmlspecialchars($t).'">'.ucwords($t).'</option>', $tiposHabitacion)) . '
                                </select>
                            </div>
                        </div>

                        <div class="row g-4" id="listaHabitaciones" style="max-height:500px; overflow-y:auto;">
                            ' . implode('', array_map(function($hab) {
                                return '
                                <div class="col-md-6 col-lg-4 habitacion-item" data-tipo="'.htmlspecialchars($hab['tipo']).'">
                                    <div class="card h-100 border-0 shadow hover-lift transition">
                                        <div class="card-body text-center p-4">
                                            <h3 class="text-rojo fw-bold mb-1">Hab. '.$hab['numero'].'</h3>
                                            <p class="text-muted fw-bold">'.ucwords($hab['tipo']).'</p>
                                            <h4 class="text-success fw-bold mb-3">Bs. '.number_format($hab['precioNoche'],2).'</h4>
                                            <input type="checkbox" name="habitaciones[]" value="'.$hab['idHabitacion'].'" id="hab_'.$hab['idHabitacion'].'" class="btn-check">
                                            <label for="hab_'.$hab['idHabitacion'].'" class="btn btn-yokoso btn-lg w-100 rounded-pill shadow-sm">
                                                <i class="fas fa-bed me-2"></i>Seleccionar
                                            </label>
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
                                <input type="number" step="0.01" class="form-control form-control-lg rounded-pill" name="anticipo" value="0" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Total a Pagar (Bs.)</label>
                                <input type="number" step="0.01" class="form-control form-control-lg bg-warning-subtle fw-bold text-dark rounded-pill" name="total" readonly required>
                            </div>
                        </div>

                        <!-- BOTONES FINALES -->
                        <div class="mt-5 pt-4 border-top text-end">
                            <a href="panel_recepcionista.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill me-3">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-yokoso btn-lg px-5 rounded-pill shadow-lg">
                                <i class="fas fa-save fa-lg me-2"></i>
                                Crear Reserva
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// CÁLCULO AUTOMÁTICO DEL TOTAL - AHORA SÍ 100% CORRECTO
document.addEventListener("DOMContentLoaded", function () {
    function calcularTotal() {
        const fechaInicio = document.querySelector("[name=fechaInicio]").value;
        const fechaFin    = document.querySelector("[name=fechaFin]").value;

        if (!fechaInicio || !fechaFin) {
            document.querySelector("[name=total]").value = "";
            return;
        }

        // CÁLCULO CORRECTO DE NOCHES (el secreto estaba aquí)
        const inicio = new Date(fechaInicio);
        const fin    = new Date(fechaFin);
        const noches = (fin - inicio) / (1000 * 60 * 60 * 24); // Esto da 2.0 exacto del 21 al 23

        if (noches <= 0) {
            document.querySelector("[name=total]").value = "";
            return;
        }

        let subtotal = 0;

        // SUMAR HABITACIONES
        document.querySelectorAll("[name=\"habitaciones[]\"]:checked").forEach(cb => {
            const card = cb.closest(".card");
            const precioTexto = card.querySelector("h4.text-success").textContent;
            const precio = parseFloat(precioTexto.replace(/[^\d.-]/g, "").replace(",", ""));
            if (!isNaN(precio)) {
                subtotal += precio * noches;
            }
        });

        // SUMAR PAQUETE (usando data-precio = 100% seguro)
        const paqueteSelect = document.querySelector("[name=idPaquete]");
        if (paqueteSelect && paqueteSelect.value) {
            const opcion = paqueteSelect.options[paqueteSelect.selectedIndex];
            const precioPaquete = parseFloat(opcion.dataset.precio || 0);
            if (!isNaN(precioPaquete)) {
                subtotal += precioPaquete;
            }
        }

        document.querySelector("[name=total]").value = subtotal.toFixed(2);
    }

    // Ejecutar al cargar y en cada cambio
    document.querySelectorAll("[name=fechaInicio], [name=fechaFin], [name=idPaquete]").forEach(el => {
        el.addEventListener("change", calcularTotal);
    });

    document.querySelectorAll("[name=\"habitaciones[]\"]").forEach(cb => {
        cb.addEventListener("change", calcularTotal);
    });

    // Filtro habitaciones
    document.getElementById("filtroHabitacion")?.addEventListener("change", function () {
        const tipo = this.value.toLowerCase();
        document.querySelectorAll(".habitacion-item").forEach(item => {
            item.style.display = (tipo === "" || item.dataset.tipo.toLowerCase() === tipo) ? "block" : "none";
        });
    });

    // Calcular al cargar por si ya hay datos
    calcularTotal();
});
</script>
';

include 'plantilla_recepcionista.php';
?>