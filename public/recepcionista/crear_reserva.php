<?php
// public/vistas/recepcionista/crear_reserva.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Obtener todos los tipos de habitación únicos
$stmt = $pdo->prepare("SELECT DISTINCT tipo FROM Habitacion WHERE estado = 'disponible' ORDER BY tipo ASC");
$stmt->execute();
$tiposHabitacion = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Obtener todas las habitaciones disponibles
$stmt = $pdo->prepare("SELECT idHabitacion, numero, tipo, precioNoche FROM Habitacion WHERE estado = 'disponible'");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener tipos de paquetes turísticos (duración)
$stmt = $pdo->prepare("SELECT DISTINCT duracionDias FROM PaqueteTuristico WHERE activo = 1 ORDER BY duracionDias ASC");
$stmt->execute();
$duracionPaquetes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Obtener paquetes turísticos activos
$stmt = $pdo->prepare("SELECT idPaquete, nombre, descripcion, precio, duracionDias FROM PaqueteTuristico WHERE activo = 1");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los huéspedes activos
$stmt = $pdo->prepare("SELECT idHuesped, nombre, apellido FROM Huesped WHERE activo = 1");
$stmt->execute();
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Crear Reserva - Hotel Yokoso";

$contenido_principal = '
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-rojo fw-bold">Crear Nueva Reserva</h2>
    </div>

    <div class="reserva-form-container">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Huésped *</label>
                    <select class="form-select" name="idHuesped" required>
                        <option value="">Seleccionar huésped</option>
                        ' . implode('', array_map(function($h) {
                            return '<option value="' . $h['idHuesped'] . '">' . htmlspecialchars($h['nombre'] . ' ' . $h['apellido']) . '</option>';
                        }, $huespedes)) . '
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Paquete Turístico (Opcional)</label>
                    <select class="form-select" name="idPaquete">
                        <option value="">No deseo paquete</option>
                        ' . implode('', array_map(function($pkg) {
                            return '<option value="' . $pkg['idPaquete'] . '">' . htmlspecialchars($pkg['nombre']) . ' - Bs. ' . htmlspecialchars($pkg['precio']) . '</option>';
                        }, $paquetes)) . '
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Fecha de Entrada *</label>
                    <input type="date" class="form-control" name="fechaInicio" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha de Salida *</label>
                    <input type="date" class="form-control" name="fechaFin" required>
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
                    ' . implode('', array_map(function($hab) {
                        return '
                            <div class="habitacion-item mb-2 p-2 bg-white rounded shadow-sm" data-tipo="' . $hab['tipo'] . '">
                                <input type="checkbox" name="habitaciones[]" value="' . $hab['idHabitacion'] . '" id="hab_' . $hab['idHabitacion'] . '">
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
                    <input type="number" class="form-control" name="anticipo" step="0.01">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Total (Bs.) *</label>
                    <input type="number" class="form-control" name="total" step="0.01" required readonly>
                </div>
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="panel_recepcionista.php" class="btn btn-cancelar me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Guardar Reserva
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

        // Calcular total dinámicamente
        function calcularTotal() {
            const fechaInicio = document.querySelector("input[name=\'fechaInicio\']").value;
            const fechaFin = document.querySelector("input[name=\'fechaFin\']").value;
            const anticipo = parseFloat(document.querySelector("input[name=\'anticipo\']").value) || 0;

            if (!fechaInicio || !fechaFin) {
                document.querySelector("input[name=\'total\']").value = "";
                return;
            }

            const dias = (new Date(fechaFin) - new Date(fechaInicio)) / (1000 * 60 * 60 * 24);
            if (dias <= 0) {
                document.querySelector("input[name=\'total\']").value = "";
                return;
            }

            let totalHabitaciones = 0;
            const checkboxes = document.querySelectorAll("input[name=\'habitaciones[]\']:checked");
            checkboxes.forEach(checkbox => {
                const precio = parseFloat(checkbox.closest(".habitacion-item").querySelector("label").textContent.match(/Bs\. ([0-9.]+)/)[1]);
                totalHabitaciones += precio * dias;
            });

            const idPaquete = document.querySelector("select[name=\'idPaquete\']").value;
            let precioPaquete = 0;
            if (idPaquete) {
                const option = document.querySelector(`select[name=\'idPaquete\'] option[value="${idPaquete}"]`);
                precioPaquete = parseFloat(option.textContent.match(/Bs\. ([0-9.]+)/)[1]) || 0;
            }

            const total = totalHabitaciones + precioPaquete;
            document.querySelector("input[name=\'total\']").value = total.toFixed(2);
        }

        // Escuchar cambios
        document.querySelector("input[name=\'fechaInicio\']").addEventListener("change", calcularTotal);
        document.querySelector("input[name=\'fechaFin\']").addEventListener("change", calcularTotal);
        document.querySelector("select[name=\'idPaquete\']").addEventListener("change", calcularTotal);
        document.querySelectorAll("input[name=\'habitaciones[]\']").forEach(cb => cb.addEventListener("change", calcularTotal));
    </script>
';

include 'plantilla_recepcionista.php';
?>