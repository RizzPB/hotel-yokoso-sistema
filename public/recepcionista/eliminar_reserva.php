<?php
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

// Verificar que se haya pasado un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ver_reservas.php");
    exit;
}

$idReserva = $_GET['id'];

require_once __DIR__ . '/../../config/database.php';

// Eliminar la reserva y sus habitaciones asociadas (por clave foránea CASCADE)
$stmt = $pdo->prepare("DELETE FROM Reserva WHERE idReserva = ?");
if ($stmt->execute([$idReserva])) {
    $mensaje = "Reserva eliminada exitosamente.";
} else {
    $error = "Error al eliminar la reserva.";
}

// Redirigir de vuelta a la lista con un mensaje
header("Location: ver_reservas.php?mensaje=" . urlencode($mensaje ?? '') . "&error=" . urlencode($error ?? ''));
exit;
?>