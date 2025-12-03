<?php
// public/recepcionista/checkout_reserva.php


define('ACCESO_PERMITIDO', true);
session_start();

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$idReserva = $_GET['id'] ?? null;

if (!$idReserva) {
    $_SESSION['error'] = "ID de reserva no válido.";
    header("Location: ver_reservas.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Verificar que la reserva existe y está en estado "confirmada"
    $stmt = $pdo->prepare("SELECT estado, fechaFin FROM Reserva WHERE idReserva = ?");
    $stmt->execute([$idReserva]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        $_SESSION['error'] = "Reserva no encontrada.";
        throw new Exception();
    }

    if ($reserva['estado'] !== 'confirmada') { // ✅ CORREGIDO: era "ocupada"
        $_SESSION['error'] = "Solo se puede hacer check-out de reservas en estado 'confirmada'.";
        throw new Exception();
    }

    // 2. Opcional: verificar que la fechaFin ya haya pasado
    $hoy = new DateTime();
    $fechaFin = new DateTime($reserva['fechaFin']);
    if ($fechaFin > $hoy) {
        $_SESSION['error'] = "La fecha de salida aún no ha llegado. ¿Está seguro de que el huésped ya se fue?";
        // Comenta esta línea si quieres permitir check-out anticipado
        throw new Exception();
    }

    // 3. Actualizar la reserva a "finalizada"
    $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'finalizada' WHERE idReserva = ?");
    $stmt->execute([$idReserva]);

    // 4. Liberar todas las habitaciones de esta reserva
    $stmt = $pdo->prepare("
        UPDATE Habitacion h
        JOIN ReservaHabitacion rh ON h.idHabitacion = rh.idHabitacion
        SET h.estado = 'disponible'
        WHERE rh.idReserva = ?
    ");
    $stmt->execute([$idReserva]);

    $pdo->commit();
    $_SESSION['mensaje'] = "Check-out completado. La(s) habitación(es) ya están disponibles.";

} catch (Exception $e) {
    $pdo->rollBack();
    if (!isset($_SESSION['error'])) {
        $_SESSION['error'] = "Error al procesar el check-out.";
    }
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'ver_reservas.php'));
exit;
?>