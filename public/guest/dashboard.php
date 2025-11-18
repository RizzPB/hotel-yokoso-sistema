<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once '../../config/database.php';

// Proteger: solo huéspedes
if (!isset($_SESSION['idUsuario']) || ($_SESSION['rol'] ?? '') !== 'huésped') {
    header('Location: /login.php');
    exit;
}

// Obtener ID del huésped (desde la sesión o BD)
$stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE email = (SELECT email FROM Usuario WHERE idUsuario = ?)");
$stmt->execute([$_SESSION['idUsuario']]);
$huesped = $stmt->fetch();
$idHuesped = $huesped['idHuesped'] ?? null;

$reservas = [];
if ($idHuesped) {
    // Obtener todas las reservas del huésped (activas)
    $stmt = $pdo->prepare("
        SELECT r.idReserva, r.fechaInicio, r.fechaFin, r.total, r.estado,
               GROUP_CONCAT(h.numero SEPARATOR ', ') AS habitaciones
        FROM Reserva r
        LEFT JOIN ReservaHabitacion rh ON r.idReserva = rh.idReserva
        LEFT JOIN Habitacion h ON rh.idHabitacion = h.idHabitacion
        WHERE r.idHuesped = ?
        GROUP BY r.idReserva
        ORDER BY r.fechaInicio DESC
    ");
    $stmt->execute([$idHuesped]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mensaje de éxito (si viene de nueva reserva)
$mensajeExito = '';
if (isset($_GET['reserva'])) {
    $mensajeExito = "Tu solicitud de reserva #" . htmlspecialchars($_GET['reserva']) . " ha sido enviada y está pendiente de aprobación.";
} elseif (isset($_GET['reserva_editada'])) {
    $mensajeExito = "La reserva #" . htmlspecialchars($_GET['reserva_editada']) . " ha sido actualizada exitosamente.";
}

// Acción: cancelar reserva
if (isset($_POST['cancelar_reserva'])) {
    $idReserva = $_POST['idReserva'];
    // Solo permitir cancelar si es 'pendiente'
    $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'cancelada' WHERE idReserva = ? AND estado = 'pendiente' AND idHuesped = ?");
    $stmt->execute([$idReserva, $idHuesped]);
    header('Location: dashboard.php?cancelada=1');
    exit;
}

include '../../app/views/guest/dashboard.view.php';
?>