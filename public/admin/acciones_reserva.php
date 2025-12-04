<?php
// public/admin/acciones_reserva.php


define('ACCESO_PERMITIDO', true);
session_start();

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$idReserva = $_GET['id'] ?? null;
$accion = $_GET['accion'] ?? null;

if (!$idReserva || !is_numeric($idReserva) || !in_array($accion, ['confirmar', 'rechazar'])) {
    $_SESSION['mensaje_error'] = "Acción no válida.";
    header("Location: ver_reservas.php");
    exit;
}

try {
    // Verificar que la reserva exista y esté pendiente
    $stmt = $pdo->prepare("SELECT estado FROM Reserva WHERE idReserva = ? AND estado = 'pendiente'");
    $stmt->execute([$idReserva]);
    if (!$stmt->fetch()) {
        $_SESSION['mensaje_error'] = "La reserva no existe o ya fue procesada.";
        header("Location: ver_reservas.php");
        exit;
    }

    if ($accion === 'confirmar') {
        $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'confirmada' WHERE idReserva = ?");
        $stmt->execute([$idReserva]);
        $_SESSION['mensaje_exito'] = "Reserva #{$idReserva} confirmada exitosamente.";
    } elseif ($accion === 'rechazar') {
        $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'cancelada' WHERE idReserva = ?");
        $stmt->execute([$idReserva]);
        $_SESSION['mensaje_exito'] = "Reserva #{$idReserva} rechazada (cancelada).";
    }

} catch (Exception $e) {
    $_SESSION['mensaje_error'] = "Error al procesar la acción.";
}

header("Location: ver_reservas.php");
exit;
?>