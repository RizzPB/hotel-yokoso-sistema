<?php
define('ACCESO_PERMITIDO', true);
session_start();
if ($_SESSION['rol'] !== 'admin') exit;

require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? 0;
$accion = $_GET['accion'] ?? '';

if ($id && in_array($accion, ['confirmar', 'rechazar'])) {
    $nuevoEstado = $accion === 'confirmar' ? 'confirmada' : 'cancelada';
    $stmt = $pdo->prepare("UPDATE Reserva SET estado = ? WHERE idReserva = ?");
    $stmt->execute([$nuevoEstado, $id]);
}
header("Location: ver_reservas.php");
exit;