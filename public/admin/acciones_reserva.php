// public/admin/acciones_reserva.php
<?php
define('ACCESO_PERMITIDO', true);
session_start();
if ($_SESSION['rol'] !== 'admin') exit;

require_once __DIR__ . '/../../config/database.php';

$id = intval($_GET['id'] ?? 0);
$accion = $_GET['accion'] ?? '';

if ($id <= 0 || !in_array($accion, ['confirmar', 'rechazar'])) {
    header("Location: ver_reservas.php?error=1");
    exit;
}

try {
    $pdo->beginTransaction();

    if ($accion === 'confirmar') {
        // Cambiar reserva a confirmada
        $pdo->prepare("UPDATE Reserva SET estado = 'confirmada' WHERE idReserva = ? AND estado = 'pendiente'")
            ->execute([$id]);

        // CAMBIAR HABITACIONES DE "reservada" → "ocupada"
        $pdo->prepare("
            UPDATE Habitacion h
            INNER JOIN ReservaHabitacion rh ON h.idHabitacion = rh.idHabitacion
            SET h.estado = 'ocupada'
            WHERE rh.idReserva = ? AND h.estado = 'reservada'
        ")->execute([$id]);

        $msg = "Reserva confirmada y habitaciones marcadas como ocupadas.";
    }

    if ($accion === 'rechazar') {
        $pdo->prepare("UPDATE Reserva SET estado = 'cancelada' WHERE idReserva = ?")->execute([$id]);

        // Liberar habitaciones
        $pdo->prepare("
            UPDATE Habitacion h
            INNER JOIN ReservaHabitacion rh ON h.idHabitacion = rh.idHabitacion
            SET h.estado = 'disponible'
            WHERE rh.idReserva = ? AND h.estado = 'reservada'
        ")->execute([$id]);

        $msg = "Reserva rechazada y habitaciones liberadas.";
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    $msg = "Error: " . $e->getMessage();
}

header("Location: ver_reservas.php?msg=" . urlencode($msg));
exit;