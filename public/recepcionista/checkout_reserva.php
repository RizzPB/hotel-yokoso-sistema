<?php
define('ACCESO_PERMITIDO', true);
session_start();

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    header("Location: ver_reservas.php?error=id_invalido");
    exit;
}

// Verificar reserva existente
$stmt = $pdo->prepare("SELECT estado FROM Reserva WHERE idReserva = ?");
$stmt->execute([$id]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    header("Location: ver_reservas.php?error=reserva_no_existe");
    exit;
}

// Solo se puede hacer check-out si está confirmada u ocupada
if (!in_array($reserva['estado'], ['confirmada', 'pendiente'])) {
    header("Location: ver_reservas.php?error=estado_invalido");
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Obtener habitaciones asociadas
    $stmt = $pdo->prepare("SELECT idHabitacion FROM ReservaHabitacion WHERE idReserva = ?");
    $stmt->execute([$id]);
    $habitaciones = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Liberar habitaciones
    $updHab = $pdo->prepare("UPDATE Habitacion SET estado = 'disponible' WHERE idHabitacion = ?");
    foreach ($habitaciones as $hab) {
        $updHab->execute([$hab]);
    }

    // 3. Cambiar estado de la reserva
    $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'finalizada' WHERE idReserva = ?");
    $stmt->execute([$id]);

    $pdo->commit();

    header("Location: ver_reservas.php?mensaje=checkout_ok");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: ver_reservas.php?error=checkout_fallo");
    exit;
}
?>
