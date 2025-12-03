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

// ❌ Ya no incluimos "eliminar" en las acciones permitidas
if (!$idReserva || !in_array($accion, ['confirmar', 'rechazar'])) {
    $_SESSION['error'] = "Parámetros inválidos.";
    header("Location: ver_reservas.php");
    exit;
}

try {
    $pdo->beginTransaction();

    if ($accion === 'confirmar') {
        $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'confirmada' WHERE idReserva = ? AND estado = 'pendiente'");
        $stmt->execute([$idReserva]);
    } elseif ($accion === 'rechazar') {
        $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'cancelada' WHERE idReserva = ? AND estado = 'pendiente'");
        $stmt->execute([$idReserva]);
    }

    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "No se pudo realizar la acción. La reserva ya fue procesada o no está pendiente.";
    } else {
        $_SESSION['mensaje'] = $accion === 'confirmar' 
            ? "¡Reserva confirmada con éxito! 🎉" 
            : "Reserva cancelada correctamente. 😔";
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollback();
    $_SESSION['error'] = "Hubo un problema al procesar la acción. Por favor, inténtalo de nuevo.";
    error_log("Error en acciones_reserva.php: " . $e->getMessage());
}

header("Location: ver_reservas.php");
exit;
?>