<?php
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

// Obtener todas las habitaciones disponibles
$stmt = $pdo->prepare("SELECT idHabitacion, numero, tipo, precioNoche FROM Habitacion WHERE estado = 'disponible'");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener paquetes turísticos activos
$stmt = $pdo->prepare("SELECT idPaquete, nombre, descripcion, precio FROM PaqueteTuristico WHERE activo = 1");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $tipoDocumento = $_POST['tipoDocumento'];
    $nroDocumento = trim($_POST['nroDocumento']);
    $procedencia = trim($_POST['procedencia']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $motivoVisita = trim($_POST['motivoVisita']);
    $preferenciaAlimentaria = trim($_POST['preferenciaAlimentaria']);
    $idHabitacion = $_POST['idHabitacion'];
    $idPaquete = $_POST['idPaquete'] ?? null; // Opcional

    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($tipoDocumento) || empty($nroDocumento)) {
        $error = "Los campos nombre, apellido, tipo y número de documento son obligatorios.";
    } else {
        // Insertar nuevo huésped
        $stmt = $pdo->prepare("
            INSERT INTO Huesped (nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria, fechaRegistro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if ($stmt->execute([$nombre, $apellido, $tipoDocumento, $nroDocumento, $procedencia, $email, $telefono, $motivoVisita, $preferenciaAlimentaria])) {
            $idHuesped = $pdo->lastInsertId();

            // Obtener el precio de la habitación para calcular el total
            $stmt = $pdo->prepare("SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?");
            $stmt->execute([$idHabitacion]);
            $habitacion = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($habitacion) {
                // Supongamos que la estadía es de 1 noche por defecto
                $noches = 1;
                $precioHabitacion = $habitacion['precioNoche'];
                $totalHabitacion = $precioHabitacion * $noches;

                // Si hay paquete turístico, sumarlo al total
                $totalPaquete = 0;
                if ($idPaquete) {
                    $stmt = $pdo->prepare("SELECT precio FROM PaqueteTuristico WHERE idPaquete = ?");
                    $stmt->execute([$idPaquete]);
                    $paquete = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($paquete) {
                        $totalPaquete = $paquete['precio'];
                    }
                }

                $total = $totalHabitacion + $totalPaquete;

                // Fecha de inicio y fin (por defecto, hoy y mañana)
                $fechaInicio = date('Y-m-d');
                $fechaFin = date('Y-m-d', strtotime('+1 day'));

                // Insertar la reserva en la tabla Reserva (sin idHabitacion)
                $stmt = $pdo->prepare("
                    INSERT INTO Reserva (idHuesped, idPaquete, fechaInicio, fechaFin, anticipo, total, estado)
                    VALUES (?, ?, ?, ?, 0, ?, 'confirmada')
                ");
                if ($stmt->execute([$idHuesped, $idPaquete, $fechaInicio, $fechaFin, $total])) {
                    $idReserva = $pdo->lastInsertId();

                    // Ahora, insertar la relación en la tabla intermedia ReservaHabitacion
                    $stmt = $pdo->prepare("
                        INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche)
                        VALUES (?, ?, ?)
                    ");
                    if ($stmt->execute([$idReserva, $idHabitacion, $precioHabitacion])) {
                        // Actualizar el estado de la habitación a 'ocupada'
                        $stmt = $pdo->prepare("UPDATE Habitacion SET estado = 'ocupada' WHERE idHabitacion = ?");
                        $stmt->execute([$idHabitacion]);

                        $mensaje = "Huésped registrado exitosamente. Se le asignó la habitación y se creó la reserva.";
                    } else {
                        $error = "Error al asociar la habitación a la reserva.";
                    }
                } else {
                    $error = "Error al crear la reserva.";
                }
            } else {
                $error = "La habitación seleccionada no existe.";
            }
        } else {
            $error = "Error al registrar el huésped.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Huésped - Hotel Yokoso</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .reserva-form-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-yokoso {
            background-color: var(--color-rojo);
            color: white;
            border: none;
            font-weight: bold;
        }
        .btn-yokoso:hover {
            background-color: var(--color-rojo-oscuro);
        }
        .btn-volver {
            background-color: var(--color-gris-oscuro);
            color: white;
            border: none;
            font-weight: bold;
        }
        .btn-volver:hover {
            background-color: #2a2a2a;
            color: white;
        }
        .btn-cancelar {
            background-color: var(--color-gris-medio);
            color: white;
            border: none;
            font-weight: bold;
        }
        .btn-cancelar:hover {
            background-color: #8a8a8a;
            color: white;
        }
        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important;
        }
        .habitacion-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .habitacion-item input[type="radio"] {
            margin-right: 10px;
        }
        .habitacion-item label {
            margin: 0;
        }
        .paquete-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            background: white;
        }
        .paquete-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .paquete-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--color-rojo);
        }
        .paquete-price {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--color-mostaza);
        }
        .paquete-description {
            margin-top: 10px;
            font-size: 0.9rem;
        }
        .paquete-includes {
            margin-top: 10px;
            font-size: 0.9rem;
            color: green;
        }
        .paquete-excludes {
            margin-top: 10px;
            font-size: 0.9rem;
            color: red;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--color-rojo-quemado);">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../../index.php">
                <img src="../../assets/img/empresaLogoYokoso.png" alt="Logo" width="40" class="me-2">
                <span class="fw-bold">YokosoStay</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <span class="nav-link text-white me-3" style="font-weight: 600;">Hola, Recepcionista</span>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="../../logout.php" class="btn btn-warning btn-sm text-dark">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-rojo fw-bold">Registrar Nuevo Huésped</h2>
            <a href="panel_recepcionista.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver al Panel
            </a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="reserva-form-container">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellido *</label>
                        <input type="text" class="form-control" name="apellido" required>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Tipo de Documento *</label>
                        <select class="form-select" name="tipoDocumento" required>
                            <option value="">Seleccionar...</option>
                            <option value="DNI">DNI</option>
                            <option value="Pasaporte">Pasaporte</option>
                            <option value="Carnet">Carnet</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Número de Documento *</label>
                        <input type="text" class="form-control" name="nroDocumento" required>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Procedencia</label>
                        <input type="text" class="form-control" name="procedencia">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="telefono">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Motivo de Visita</label>
                        <input type="text" class="form-control" name="motivoVisita">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Preferencias Alimentarias</label>
                    <textarea class="form-control" rows="3" name="preferenciaAlimentaria" placeholder="Ej. Vegetariano, sin gluten, alérgico a la leche..."></textarea>
                </div>

                <div class="mt-4">
                    <h4>Asignar Habitación *</h4>
                    <div class="border p-3 rounded">
                        <?php foreach ($habitaciones as $habitacion): ?>
                            <div class="habitacion-item">
                                <input type="radio" name="idHabitacion" value="<?= $habitacion['idHabitacion'] ?>" id="habitacion_<?= $habitacion['idHabitacion'] ?>" required>
                                <label for="habitacion_<?= $habitacion['idHabitacion'] ?>">
                                    <?= htmlspecialchars($habitacion['numero']) ?> (<?= htmlspecialchars($habitacion['tipo']) ?>) - Bs. <?= htmlspecialchars($habitacion['precioNoche']) ?>/noche
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-4">
                    <h4>Paquete Turístico (Opcional)</h4>
                    <div class="border p-3 rounded">
                        <select class="form-select" name="idPaquete">
                            <option value="">No deseo paquete</option>
                            <?php foreach ($paquetes as $paquete): ?>
                                <option value="<?= $paquete['idPaquete'] ?>">
                                    <?= htmlspecialchars($paquete['nombre']) ?> - Bs. <?= htmlspecialchars($paquete['precio']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="panel_recepcionista.php" class="btn btn-cancelar me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                        <i class="fas fa-user-plus me-2"></i>Registrar Huésped
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>