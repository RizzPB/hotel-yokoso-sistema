<?php
// Middleware de autenticación (solo huésped)
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Obtener id de la reserva desde la URL
$idReserva = $_GET['id'] ?? null;

if (!$idReserva || !ctype_digit($idReserva)) {
    header('Location: /guest/dashboard.php');
    exit;
}

// Obtener idHuesped del usuario logueado
$stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE email = (SELECT email FROM Usuario WHERE idUsuario = ?)");
$stmt->execute([$_SESSION['idUsuario']]);
$huesped = $stmt->fetch();
$idHuesped = $huesped['idHuesped'] ?? null;

if (!$idHuesped) {
    die("Error: No se encontró tu perfil de huésped.");
}

// Obtener la reserva + datos relacionados
$stmt = $pdo->prepare("
    SELECT 
        r.idReserva, r.fechaInicio, r.fechaFin, r.total, r.estado, r.anticipo, r.fechaCheckIn, r.fechaCheckOut,
        p.idPaquete, p.nombre AS paqueteNombre, p.descripcion AS paqueteDescripcion, p.precio AS paquetePrecio,
        h.idHuesped, h.nombre AS huespedNombre, h.apellido AS huespedApellido, h.email AS huespedEmail,
        h.telefono AS huespedTelefono, h.nroDocumento, h.tipoDocumento
    FROM Reserva r
    LEFT JOIN PaqueteTuristico p ON r.idPaquete = p.idPaquete
    LEFT JOIN Huesped h ON r.idHuesped = h.idHuesped
    WHERE r.idReserva = ? AND r.idHuesped = ?
");
$stmt->execute([$idReserva, $idHuesped]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    header('Location: /guest/dashboard.php?error=reserva_no_encontrada');
    exit;
}

// Obtener habitaciones de esta reserva
$stmt = $pdo->prepare("
    SELECT hab.numero, hab.tipo, hab.precioNoche, hab.foto
    FROM ReservaHabitacion rh
    JOIN Habitacion hab ON rh.idHabitacion = hab.idHabitacion
    WHERE rh.idReserva = ?
");
$stmt->execute([$idReserva]);
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pasar datos a la vista
$GLOBALS['reserva'] = $reserva;
$GLOBALS['habitaciones'] = $habitaciones;

include __DIR__ . '/../../app/views/guest/reserva_detalle.view.php';
?>