<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';

$idReserva = $_GET['id'] ?? null;
if (!$idReserva || !ctype_digit($idReserva)) {
    header('Location: ../guest/dashboard.php');
    exit;
}

// Obtener idHuesped del usuario logueado
$stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE email = (SELECT email FROM Usuario WHERE idUsuario = ?)");
$stmt->execute([$_SESSION['idUsuario']]);
$huesped = $stmt->fetch();
$idHuesped = $huesped['idHuesped'] ?? null;

if (!$idHuesped) {
    die("Error: No se encontró tu perfil.");
}

// Verificar que la reserva existe y es suya
$stmt = $pdo->prepare("SELECT * FROM Reserva WHERE idReserva = ? AND idHuesped = ? AND estado = 'pendiente'");
$stmt->execute([$idReserva, $idHuesped]);
$reserva = $stmt->fetch();

if (!$reserva) {
    header('Location: ../guest/dashboard.php?error=reserva_no_editable');
    exit;
}

// Cargar datos actuales
$habitacionesActuales = [];
$stmt = $pdo->prepare("SELECT idHabitacion FROM ReservaHabitacion WHERE idReserva = ?");
$stmt->execute([$idReserva]);
$idsHabitacionesActuales = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Cargar listas para el formulario
// Habitaciones disponibles (no ocupadas ni en mantenimiento)
$stmt = $pdo->prepare("
    SELECT idHabitacion, numero, tipo, precioNoche, foto 
    FROM Habitacion 
    WHERE estado = 'disponible' OR idHabitacion IN (" . str_repeat('?,', count($idsHabitacionesActuales) - 1) . "?)"
);
$params = $idsHabitacionesActuales ?: [0]; // evita error si vacío
$stmt->execute($params);
$habitacionesDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Paquetes activos
$stmt = $pdo->prepare("SELECT idPaquete, nombre, descripcion, precio FROM PaqueteTuristico WHERE activo = 1");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$exito = false;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fechaInicio = $_POST['fecha_inicio'] ?? '';
    $fechaFin = $_POST['fecha_fin'] ?? '';
    $habitacionesSeleccionadas = $_POST['habitaciones'] ?? [];
    $idPaquete = !empty($_POST['paquete']) ? (int)$_POST['paquete'] : null;

    // Validaciones
    if (!$fechaInicio || !$fechaFin) {
        $error = "Las fechas de inicio y fin son obligatorias.";
    } elseif (strtotime($fechaFin) <= strtotime($fechaInicio)) {
        $error = "La fecha de fin debe ser posterior a la de inicio.";
    } elseif (empty($habitacionesSeleccionadas)) {
        $error = "Debes seleccionar al menos una habitación.";
    } else {
        try {
            $pdo->beginTransaction();

            // Calcular nuevo total
            $totalHabitaciones = 0;
            foreach ($habitacionesSeleccionadas as $idHab) {
                $stmt = $pdo->prepare("SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?");
                $stmt->execute([$idHab]);
                $precio = $stmt->fetchColumn();
                if ($precio === false) {
                    throw new Exception("Habitación no válida.");
                }
                $totalHabitaciones += (float)$precio;
            }

            $totalPaquete = 0;
            if ($idPaquete) {
                $stmt = $pdo->prepare("SELECT precio FROM PaqueteTuristico WHERE idPaquete = ? AND activo = 1");
                $stmt->execute([$idPaquete]);
                $totalPaquete = $stmt->fetchColumn();
                if ($totalPaquete === false) $idPaquete = null;
            }

            $dias = max(1, (strtotime($fechaFin) - strtotime($fechaInicio)) / (60 * 60 * 24));
            $total = $totalHabitaciones * $dias + (float)$totalPaquete;

            // Actualizar Reserva
            $stmt = $pdo->prepare("
                UPDATE Reserva 
                SET fechaInicio = ?, fechaFin = ?, idPaquete = ?, total = ?
                WHERE idReserva = ? AND idHuesped = ?
            ");
            $stmt->execute([$fechaInicio, $fechaFin, $idPaquete, $total, $idReserva, $idHuesped]);

            // Eliminar habitaciones antiguas
            $pdo->prepare("DELETE FROM ReservaHabitacion WHERE idReserva = ?")->execute([$idReserva]);

            // Insertar nuevas habitaciones
            foreach ($habitacionesSeleccionadas as $idHab) {
                $stmt = $pdo->prepare("SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?");
                $stmt->execute([$idHab]);
                $precio = $stmt->fetchColumn();
                $pdo->prepare("
                    INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche) 
                    VALUES (?, ?, ?)
                ")->execute([$idReserva, $idHab, $precio]);
            }

            $pdo->commit();
            $exito = true;

            // Redirigir con mensaje de éxito
            header("Location: ../guest/dashboard.php?reserva_editada=$idReserva");
            exit;

        } catch (Exception $e) {
            $pdo->rollback();
            $error = "Error al actualizar la reserva. Inténtalo de nuevo.";
        }
    }
}

// Pasar variables a la vista
$GLOBALS['reserva'] = $reserva;
$GLOBALS['habitacionesDisponibles'] = $habitacionesDisponibles;
$GLOBALS['habitacionesActuales'] = $idsHabitacionesActuales;
$GLOBALS['paquetes'] = $paquetes;
$GLOBALS['error'] = $error ?? '';
$GLOBALS['exito'] = $exito;

include __DIR__ . '/../../app/views/guest/editar_reserva.view.php';
?>