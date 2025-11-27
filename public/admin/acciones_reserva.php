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

if (!$idReserva || !in_array($accion, ['confirmar', 'rechazar'])) {
    $_SESSION['error'] = "Parámetros inválidos.";
    header("Location: ver_reservas.php");
    exit;
}

try {
    $pdo->beginTransaction();

    if ($accion === 'confirmar') {
        // Actualizar estado a 'confirmada'
        $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'confirmada' WHERE idReserva = ? AND estado = 'pendiente'");
        $stmt->execute([$idReserva]);
    } elseif ($accion === 'rechazar') {
        // Actualizar estado a 'cancelada'
        $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'cancelada' WHERE idReserva = ? AND estado IN ('pendiente', 'confirmada')");
        $stmt->execute([$idReserva]);
    }

    // Si no se actualizó ninguna fila, la acción no era válida (ej: ya estaba confirmada)
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "No se pudo realizar la acción. La reserva ya fue procesada.";
    } else {
        $_SESSION['mensaje'] = $accion === 'confirmar' ? "Reserva confirmada exitosamente." : "Reserva cancelada exitosamente.";
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollback();
    $_SESSION['error'] = "Error interno: " . $e->getMessage();
}

header("Location: ver_reservas.php");
exit;
?>