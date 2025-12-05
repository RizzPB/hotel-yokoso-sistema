<?php
session_start();
require_once '../../config/database.php';

// Proteger: solo huéspedes
if (!isset($_SESSION['idUsuario']) || ($_SESSION['rol'] ?? '') !== 'huésped') {
    header('Location: /login.php');
    exit;
}

// Asegurar email en sesión
if (!isset($_SESSION['email'])) {
    header('Location: /login.php');
    exit;
}

// Recuperar selecciones anteriores
$habitaciones = $_SESSION['habitaciones_seleccionadas'] ?? [];
$paquete = $_POST['paquete_seleccionado'] ?? ($_SESSION['paquete_seleccionado'] ?? '');

if (empty($habitaciones)) {
    header('Location: rooms.php');
    exit;
}

// === PRELLENAR CON DATOS DE SESIÓN ===
$datos = [
    'nombre' => $_SESSION['nombreUsuario'] ?? '',
    'apellido' => $_SESSION['apellidoUsuario'] ?? '',
    'tipoDocumento' => '',
    'email' => $_SESSION['email'],
    'telefono' => $_SESSION['telefonoUsuario'] ?? '',
    'procedencia' => '',
    'motivoVisita' => '',
    'preferenciaAlimentaria' => '',
    'fechaInicio' => $_POST['fechaInicio'] ?? '',
    'fechaFin' => $_POST['fechaFin'] ?? ''
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar (email siempre de sesión)
    $datos = [
        'nombre' => trim($_POST['nombre'] ?? $_SESSION['nombreUsuario'] ?? ''),
        'apellido' => trim($_POST['apellido'] ?? $_SESSION['apellidoUsuario'] ?? ''),
        'tipoDocumento' => $_POST['tipoDocumento'] ?? '',
        'email' => $_SESSION['email'],
        'telefono' => trim($_POST['telefono'] ?? $_SESSION['telefonoUsuario'] ?? ''),
        'procedencia' => trim($_POST['procedencia'] ?? ''),
        'motivoVisita' => trim($_POST['motivoVisita'] ?? ''),
        'preferenciaAlimentaria' => trim($_POST['preferenciaAlimentaria'] ?? ''),
        'fechaInicio' => trim($_POST['fechaInicio'] ?? ''),
        'fechaFin' => trim($_POST['fechaFin'] ?? '')
    ];

    // Validaciones
    if (empty($datos['nombre'])) $errors['nombre'] = "El nombre es obligatorio.";
    if (empty($datos['apellido'])) $errors['apellido'] = "El apellido es obligatorio.";
    if (!in_array($datos['tipoDocumento'], ['DNI', 'Pasaporte', 'Carnet'])) $errors['tipoDocumento'] = "Selecciona un tipo de documento válido.";
    if (empty($datos['telefono'])) $errors['telefono'] = "El teléfono es obligatorio.";
    if (empty($datos['procedencia'])) $errors['procedencia'] = "La procedencia es obligatoria.";

    // Validar fechas
    if (empty($datos['fechaInicio'])) {
        $errors['fechaInicio'] = "La fecha de entrada es obligatoria.";
    } else {
        $hoy = new DateTime();
        $inicio = new DateTime($datos['fechaInicio']);
        if ($inicio <= $hoy) {
            $errors['fechaInicio'] = "La fecha de entrada debe ser futura.";
        }
    }

    if (empty($datos['fechaFin'])) {
        $errors['fechaFin'] = "La fecha de salida es obligatoria.";
    } elseif (!empty($datos['fechaInicio']) && $datos['fechaFin'] <= $datos['fechaInicio']) {
        $errors['fechaFin'] = "La fecha de salida debe ser posterior a la entrada.";
    }

    // Si no hay errores, procesar
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Buscar huésped por email de sesión
            $stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE email = ?");
            $stmt->execute([$_SESSION['email']]);
            $huespedExistente = $stmt->fetch();

            if ($huespedExistente) {
                $idHuesped = $huespedExistente['idHuesped'];
                $stmt = $pdo->prepare("
                    UPDATE Huesped 
                    SET nombre = ?, apellido = ?, tipoDocumento = ?, procedencia = ?, 
                        telefono = ?, motivoVisita = ?, preferenciaAlimentaria = ?
                    WHERE idHuesped = ?
                ");
                $stmt->execute([
                    $datos['nombre'],
                    $datos['apellido'],
                    $datos['tipoDocumento'],
                    $datos['procedencia'],
                    $datos['telefono'],
                    $datos['motivoVisita'],
                    $datos['preferenciaAlimentaria'],
                    $idHuesped
                ]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO Huesped (nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria, activo)
                    VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([
                    $datos['nombre'],
                    $datos['apellido'],
                    $datos['tipoDocumento'],
                    $datos['procedencia'],
                    $_SESSION['email'],
                    $datos['telefono'],
                    $datos['motivoVisita'],
                    $datos['preferenciaAlimentaria']
                ]);
                $idHuesped = $pdo->lastInsertId();
            }

            // Calcular total
            $total = 0;
            $stmtPrecio = $pdo->prepare("SELECT precioNoche FROM Habitacion WHERE idHabitacion = ? AND estado = 'disponible'");
            foreach ($habitaciones as $idHab) {
                $stmtPrecio->execute([$idHab]);
                $hab = $stmtPrecio->fetch();
                if ($hab) $total += $hab['precioNoche'];
            }

            if (!empty($paquete)) {
                $stmtPrecio = $pdo->prepare("SELECT precio FROM PaqueteTuristico WHERE idPaquete = ? AND activo = 1");
                $stmtPrecio->execute([$paquete]);
                $pkg = $stmtPrecio->fetch();
                if ($pkg) $total += $pkg['precio'];
            }

            // Insertar reserva
            $stmt = $pdo->prepare("
                INSERT INTO Reserva (idHuesped, idPaquete, fechaInicio, fechaFin, total, estado)
                VALUES (?, ?, ?, ?, ?, 'pendiente')
            ");
            $stmt->execute([
                $idHuesped,
                $paquete ?: null,
                $datos['fechaInicio'],
                $datos['fechaFin'],
                $total
            ]);
            $idReserva = $pdo->lastInsertId();

            // Insertar habitaciones
            foreach ($habitaciones as $idHab) {
                $stmtHab = $pdo->prepare("
                    INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche)
                    SELECT ?, idHabitacion, precioNoche 
                    FROM Habitacion 
                    WHERE idHabitacion = ?
                ");
                $stmtHab->execute([$idReserva, $idHab]);
            }

            $pdo->commit();
            header('Location: dashboard.php?reserva=' . urlencode($idReserva));
            exit;

        } catch (Exception $e) {
            $pdo->rollback();
            error_log("Error en reserva: " . $e->getMessage());
            $errors['general'] = "Error al procesar la reserva. Por favor, inténtalo más tarde.";
        }
    }
}

// Guardar paquete en sesión para persistencia
$_SESSION['paquete_seleccionado'] = $paquete;

// Incluir la vista
include '../../app/views/guest/personal.view.php';
?>