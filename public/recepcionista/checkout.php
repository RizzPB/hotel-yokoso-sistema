<?php
// public/recepcionista/checkout.php

define('ACCESO_PERMITIDO', true);
session_start();

// Protección de acceso
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$mensaje = $error = '';

$idReserva = $_GET['id'] ?? null;

if (!$idReserva || !is_numeric($idReserva)) {
    $_SESSION['mensaje_error'] = "Reserva no válida.";
    header("Location: ver_reservas.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    try {
        $pdo->beginTransaction();

        // 1. Verificar que la reserva exista y esté 'ocupada'
        $stmt = $pdo->prepare("
            SELECT r.idReserva, r.estado 
            FROM Reserva r 
            WHERE r.idReserva = ? AND r.estado = 'ocupada'
        ");
        $stmt->execute([$idReserva]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reserva) {
            throw new Exception("La reserva no existe o ya fue finalizada.");
        }

        // 2. Registrar fecha de check-out y finalizar reserva
        $stmt = $pdo->prepare("
            UPDATE Reserva 
            SET fechaCheckOut = CURDATE(), 
                estado = 'finalizada' 
            WHERE idReserva = ?
        ");
        $stmt->execute([$idReserva]);

        // 3. Liberar todas las habitaciones asociadas
        $stmt = $pdo->prepare("
            UPDATE Habitacion h
            JOIN ReservaHabitacion rh ON h.idHabitacion = rh.idHabitacion
            SET h.estado = 'disponible'
            WHERE rh.idReserva = ?
        ");
        $stmt->execute([$idReserva]);

        $pdo->commit();
        $_SESSION['mensaje_exito'] = "Check-out completado. La(s) habitación(es) están disponibles.";
        header("Location: ver_reservas.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensaje_error'] = "Error al procesar el check-out: " . $e->getMessage();
        header("Location: ver_reservas.php");
        exit;
    }
}

// Si no es POST, mostramos la página de confirmación
$stmt = $pdo->prepare("
    SELECT 
        r.idReserva,
        CONCAT(hu.nombre, ' ', hu.apellido) AS huesped,
        r.fechaInicio,
        r.fechaFin,
        r.total
    FROM Reserva r
    JOIN Huesped hu ON r.idHuesped = hu.idHuesped
    WHERE r.idReserva = ? AND r.estado = 'ocupada'
");
$stmt->execute([$idReserva]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    $_SESSION['mensaje_error'] = "No se puede hacer check-out: la reserva no está activa.";
    header("Location: ver_reservas.php");
    exit;
}

// Cargar habitaciones de la reserva
$stmt = $pdo->prepare("
    SELECT h.numero, h.tipo, rh.precioNoche
    FROM ReservaHabitacion rh
    JOIN Habitacion h ON rh.idHabitacion = h.idHabitacion
    WHERE rh.idReserva = ?
");
$stmt->execute([$idReserva]);
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Confirmar Check-out - Hotel Yokoso";

$contenido_principal = '
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5">

                    <div class="text-center mb-4">
                        <i class="fas fa-door-open fa-3x text-rojo mb-3"></i>
                        <h2 class="text-rojo fw-bold">Confirmar Check-out</h2>
                    </div>

                    <div class="alert alert-warning text-center">
                        <strong>¿Finalizar la estadía de este huésped?</strong><br>
                        Esta acción liberará las habitaciones para nuevas reservas.
                    </div>

                    <div class="mb-4 p-4 bg-light rounded-3">
                        <h5 class="fw-bold mb-3">Detalles de la Reserva</h5>
                        <p><strong>Huésped:</strong> ' . htmlspecialchars($reserva['huesped']) . '</p>
                        <p><strong>Reserva #:</strong> ' . $reserva['idReserva'] . '</p>
                        <p><strong>Periodo:</strong> ' . $reserva['fechaInicio'] . ' → ' . $reserva['fechaFin'] . '</p>
                        <p><strong>Total pagado:</strong> Bs. ' . number_format($reserva['total'], 2) . '</p>

                        <h6 class="mt-3 fw-bold">Habitaciones:</h6>
                        <ul class="list-unstyled">
                            ' . implode('', array_map(function($h) {
                                return '<li>Hab. ' . htmlspecialchars($h['numero']) . ' (' . $h['tipo'] . ') - Bs. ' . number_format($h['precioNoche'], 2) . '/noche</li>';
                            }, $habitaciones)) . '
                        </ul>
                    </div>

                    <form method="POST" class="text-center">
                        <input type="hidden" name="confirmar" value="1">
                        <a href="ver_reservas.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill me-3">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-danger btn-lg px-5 rounded-pill">
                            <i class="fas fa-check me-2"></i>Confirmar Check-out
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
';

include 'plantilla_recepcionista.php';
?>