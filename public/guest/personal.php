<?php
session_start();
require_once '../../config/database.php';

// Proteger: solo huéspedes
if (!isset($_SESSION['idUsuario']) || ($_SESSION['rol'] ?? '') !== 'huésped') {
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

// Variables para la vista
$errors = [];
$datos = [
    'nombre' => '',
    'apellido' => '',
    'tipoDocumento' => '',
    'email' => '',
    'telefono' => '',
    'procedencia' => '',
    'motivoVisita' => '',
    'preferenciaAlimentaria' => '',
    'fechaInicio' => $_POST['fechaInicio'] ?? '',
    'fechaFin' => $_POST['fechaFin'] ?? ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar
    $datos = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'apellido' => trim($_POST['apellido'] ?? ''),
        'tipoDocumento' => $_POST['tipoDocumento'] ?? '',
        'email' => trim($_POST['email'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
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
    if (empty($datos['email']) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = "Ingresa un correo válido.";
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

            // 1. Insertar huésped
            $stmt = $pdo->prepare("
                INSERT INTO Huesped (nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria, activo)
                VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $datos['nombre'],
                $datos['apellido'],
                $datos['tipoDocumento'],
                $datos['procedencia'],
                $datos['email'],
                $datos['telefono'],
                $datos['motivoVisita'],
                $datos['preferenciaAlimentaria']
            ]);
            $idHuesped = $pdo->lastInsertId();

            // 2. Calcular total
            $total = 0;
            $stmtPrecio = $pdo->prepare("SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?");
            foreach ($habitaciones as $idHab) {
                $stmtPrecio->execute([$idHab]);
                $hab = $stmtPrecio->fetch();
                if ($hab) $total += $hab['precioNoche'];
            }

            if (!empty($paquete)) {
                $stmtPrecio = $pdo->prepare("SELECT precio FROM PaqueteTuristico WHERE idPaquete = ?");
                $stmtPrecio->execute([$paquete]);
                $pkg = $stmtPrecio->fetch();
                if ($pkg) $total += $pkg['precio'];
            }

            // 3. Insertar reserva (¡SOLO UNA VEZ!)
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

            // 4. Insertar habitaciones en ReservaHabitacion
            foreach ($habitaciones as $idHab) {
                $stmtHab = $pdo->prepare("
                    INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche)
                    SELECT ?, idHabitacion, precioNoche FROM Habitacion WHERE idHabitacion = ?
                ");
                $stmtHab->execute([$idReserva, $idHab]);
            }

            $_SESSION['reserva_id'] = $idReserva;
            $_SESSION['reserva_total'] = $total;

            $pdo->commit();
            header('Location: confirmacion.php');
            exit;
            /* 
            header('Location: dashboard.php?reserva=' . $idReserva);
            exit;
            */

        } catch (Exception $e) {
            $pdo->rollback();
            $errors['general'] = "Error técnico: " . $e->getMessage();
           // $errors['general'] = "Error al procesar la reserva. Intente más tarde por favor.";
        }
    }
}

$_SESSION['paquete_seleccionado'] = $paquete;

include '../../app/views/guest/personal.view.php';
?>