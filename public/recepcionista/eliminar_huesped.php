<?php
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

// Verificar que se haya pasado un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ver_huespedes.php");
    exit;
}

$idHuesped = $_GET['id'];

require_once __DIR__ . '/../../config/database.php';

// Marcar como inactivo en lugar de eliminar físicamente
$stmt = $pdo->prepare("UPDATE Huesped SET activo = 0 WHERE idHuesped = ?");
if ($stmt->execute([$idHuesped])) {
    $mensaje = "Huésped eliminado exitosamente (marcado como inactivo).";
} else {
    $error = "Error al eliminar el huésped.";
}

// Redirigir de vuelta a la lista con un mensaje
header("Location: ver_huespedes.php?mensaje=" . urlencode($mensaje ?? '') . "&error=" . urlencode($error ?? ''));
exit;
?>