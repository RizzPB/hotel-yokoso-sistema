<?php
// public/recepcionista/editar_reserva.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'ver_reservas';
require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header("Location: ver_reservas.php");
    exit;
}

// Obtener reserva
$stmt = $pdo->prepare("SELECT * FROM Reserva WHERE idReserva = ?");
$stmt->execute([$id]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$reserva) {
    header("Location: ver_reservas.php?error=no_encontrada");
    exit;
}

// Habitaciones actuales
$stmt = $pdo->prepare("SELECT idHabitacion FROM ReservaHabitacion WHERE idReserva = ?");
$stmt->execute([$id]);
$habitacionesReserva = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Datos para formularios
$tiposHabitacion = $pdo->query("SELECT DISTINCT tipo FROM Habitacion WHERE estado = 'disponible' ORDER BY tipo")->fetchAll(PDO::FETCH_COLUMN);
$habitaciones    = $pdo->query("SELECT idHabitacion, numero, tipo, precioNoche FROM Habitacion WHERE estado = 'disponible' ORDER BY numero")->fetchAll(PDO::FETCH_ASSOC);
$paquetes        = $pdo->query("SELECT idPaquete, nombre, precio FROM PaqueteTuristico WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);
$huespedes       = $pdo->query("SELECT idHuesped, nombre, apellido FROM Huesped WHERE activo = 1 ORDER BY apellido")->fetchAll(PDO::FETCH_ASSOC);

$mensaje = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idHuesped   = $_POST['idHuesped'] ?? null;
    $idPaquete   = !empty($_POST['idPaquete']) ? $_POST['idPaquete'] : null;
    $fechaInicio = $_POST['fechaInicio'] ?? '';
    $fechaFin    = $_POST['fechaFin'] ?? '';
    $anticipo    = $_POST['anticipo'] ?? 0;
    $total       = $_POST['total'] ?? 0;
    $estado      = $_POST['estado'] ?? 'pendiente';

    if (empty($idHuesped) || empty($fechaInicio) || empty($fechaFin) || $total <= 0) {
        $error = "Faltan campos obligatorios o el total es inválido.";
    } elseif (strtotime($fechaFin) <= strtotime($fechaInicio)) {
        $error = "La fecha de salida debe ser posterior a la entrada.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE Reserva SET idHuesped=?, idPaquete=?, fechaInicio=?, fechaFin=?, anticipo=?, total=?, estado=? WHERE idReserva=?");
            $stmt->execute([$idHuesped, $idPaquete, $fechaInicio, $fechaFin, $anticipo, $total, $estado, $id]);

            // Eliminar habitaciones anteriores
            $pdo->prepare("DELETE FROM ReservaHabitacion WHERE idReserva = ?")->execute([$id]);

            // Asignar nuevas
            if (!empty($_POST['habitaciones'])) {
                $stmtIns = $pdo->prepare("INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche) VALUES (?, ?, (SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?))");
                $stmtUpd = $pdo->prepare("UPDATE Habitacion SET estado = 'ocupada' WHERE idHabitacion = ?");
                foreach ($_POST['habitaciones'] as $idHab) {
                    $stmtIns->execute([$id, $idHab, $idHab]);
                    $stmtUpd->execute([$idHab]);
                }
            }

            $pdo->commit();
            $mensaje = "Reserva actualizada con éxito.";

            // Recargar datos actualizados
            $stmt = $pdo->prepare("SELECT * FROM Reserva WHERE idReserva = ?");
            $stmt->execute([$id]);
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al guardar. Puede haber conflicto de habitaciones.";
        }
    }
}

$titulo_pagina = "Editar Reserva #{$reserva['idReserva']} - Hotel Yokoso";

$contenido_principal = '
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-rojo fw-bold">
            <i class="fas fa-edit me-3"></i>
            Editar Reserva #' . htmlspecialchars($reserva['idReserva']) . '
        </h2>
        <a href="ver_reservas.php" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    ' . ($mensaje ? '<div class="alert alert-success text-center mx-auto mb-4" style="max-width:900px;"><i class="fas fa-check-circle fa-2x"></i><br>' . $mensaje . '</div>' : '') . '
    ' . ($error ? '<div class="alert alert-danger text-center mx-auto mb-4" style="max-width:900px;"><i class="fas fa-times-circle fa-2x"></i><br>' . $error . '</div>' : '') . '

    <!-- FORMULARIO PREMIUM, FIJO Y CENTRADO -->
    <div class="row justify-content-center">
        <div class="col-xl-10 col-xxl-9">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5 p-lg-6">
                    <form method="POST">

                        <!-- HUÉSPED Y PAQUETE -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Huésped *</label>
                                <select class="form-select form-select-lg rounded-pill" name="idHuesped" required>
                                    ' . implode('', array_map(fn($h) => '<option value="'.$h['idHuesped'].'" '.($h['idHuesped'] == $reserva['idHuesped'] ? 'selected' : '').'>'.htmlspecialchars($h['nombre'].' '.$h['apellido']).'</option>', $huespedes)) . '
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Paquete Turístico (Opcional)</label>
                                <select class="form-select form-select-lg rounded-pill" name="idPaquete">
                                    <option value="">Sin paquete</option>
                                    ' . implode('', array_map(fn($p) => '<option value="'.$p['idPaquete'].'" '.($p['idPaquete'] == $reserva['idPaquete'] ? 'selected' : '').'>'.htmlspecialchars($p['nombre']).' - Bs. '.number_format($p['precio'],2).'</option>', $paquetes)) . '
                                </select>
                            </div>
                        </div>

                        <!-- FECHAS -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Fecha Entrada *</label>
                                <input type="date" class="form-control form-control-lg rounded-pill" name="fechaInicio" value="'.htmlspecialchars($reserva['fechaInicio']).'" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Fecha Salida *</label>
                                <input type="date" class="form-control form-control-lg rounded-pill" name="fechaFin" value="'.htmlspecialchars($reserva['fechaFin']).'" required>
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

                        <div class="row g-4" id="listaHabitaciones">
                            ' . implode('', array_map(function($hab) use ($habitacionesReserva) {
                                $checked = in_array($hab['idHabitacion'], $habitacionesReserva) ? 'checked' : '';
                                return '
                                <div class="col-md-6 col-lg-4 habitacion-item" data-tipo="'.htmlspecialchars($hab['tipo']).'">
                                    <div class="card h-100 border-0 shadow hover-lift">
                                        <div class="card-body text-center p-4">
                                            <h3 class="text-rojo fw-bold">Hab. '.$hab['numero'].'</h3>
                                            <p class="text-muted fw-bold">'.ucwords($hab['tipo']).'</p>
                                            <h5 class="text-success fw-bold">Bs. '.number_format($hab['precioNoche'],2).'</h5>
                                            <div class="mt-3">
                                                <input type="checkbox" name="habitaciones[]" value="'.$hab['idHabitacion'].'" id="hab_'.$hab['idHabitacion'].'" class="btn-check" '.$checked.'>
                                                <label for="hab_'.$hab['idHabitacion'].'" class="btn btn-yokoso btn-lg w-100 rounded-pill shadow-sm">
                                                    <i class="fas fa-bed me-2"></i>Seleccionar
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>';
                            }, $habitaciones)) . '
                        </div>

                        <!-- ANTICIPO, TOTAL Y ESTADO -->
                        <hr class="my-5 border-secondary">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Anticipo (Bs.)</label>
                                <input type="number" step="0.01" class="form-control form-control-lg rounded-pill" name="anticipo" value="'.htmlspecialchars($reserva['anticipo']).'">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Total (Bs.) *</label>
                                <input type="number" step="0.01" class="form-control form-control-lg rounded-pill bg-warning-subtle fw-bold" name="total" value="'.htmlspecialchars($reserva['total']).'" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Estado</label>
                                <select class="form-select form-select-lg rounded-pill" name="estado">
                                    <option value="pendiente"  '.($reserva['estado']==='pendiente'?'selected':'').'>Pendiente</option>
                                    <option value="confirmada" '.($reserva['estado']==='confirmada'?'selected':'').'>Confirmada</option>
                                    <option value="cancelada"  '.($reserva['estado']==='cancelada'?'selected':'').'>Cancelada</option>
                                    <option value="finalizada" '.($reserva['estado']==='finalizada'?'selected':'').'>Finalizada</option>
                                </select>
                            </div>
                        </div>

                        <!-- BOTONES FINALES -->
                        <div class="mt-5 pt-4 border-top text-end">
                            <a href="ver_reservas.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill me-3">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-yokoso btn-lg px-5 rounded-pill shadow-lg">
                                <i class="fas fa-save fa-lg me-2"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// FILTRO DE HABITACIONES 100% FUNCIONAL
document.getElementById("filtroHabitacion")?.addEventListener("change", function() {
    const tipo = this.value.toLowerCase();
    document.querySelectorAll(".habitacion-item").forEach(item => {
        const tipoHab = item.dataset.tipo.toLowerCase();
        item.style.display = (tipo === "" || tipoHab === tipo) ? "block" : "none";
    });
});
</script>
';

include 'plantilla_recepcionista.php';
?>