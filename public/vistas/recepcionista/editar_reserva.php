<?php
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

// Verificar que se haya pasado un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ver_reservas.php");
    exit;
}

$idReserva = $_GET['id'];

require_once __DIR__ . '/../../../config/database.php';

// Obtener la reserva actual con información del huésped
$stmt = $pdo->prepare("
    SELECT r.*, h.nombre, h.apellido
    FROM Reserva r
    JOIN Huesped h ON r.idHuesped = h.idHuesped
    WHERE r.idReserva = ?
");
$stmt->execute([$idReserva]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    die("Reserva no encontrada.");
}

// Obtener todos los huéspedes activos
$stmt = $pdo->prepare("SELECT idHuesped, nombre, apellido FROM Huesped WHERE activo = 1");
$stmt->execute();
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener paquetes turísticos activos
$stmt = $pdo->prepare("SELECT idPaquete, nombre, descripcion, precio FROM PaqueteTuristico WHERE activo = 1");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si el formulario fue enviado, procesarlo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idHuesped = $_POST['idHuesped'];
    $idPaquete = $_POST['idPaquete'] ?: null;
    $fechaInicio = $_POST['fechaInicio'];
    $fechaFin = $_POST['fechaFin'];
    $anticipo = $_POST['anticipo'] ?: 0;
    $total = $_POST['total'];
    $estado = $_POST['estado'];

    // Validaciones básicas
    if (empty($idHuesped) || empty($fechaInicio) || empty($fechaFin) || empty($total)) {
        $error = "Los campos huésped, fechas y total son obligatorios.";
    } elseif (strtotime($fechaFin) <= strtotime($fechaInicio)) {
        $error = "La fecha de salida debe ser mayor a la fecha de entrada.";
    } else {
        // Actualizar en la base de datos
        $stmt = $pdo->prepare("
            UPDATE Reserva
            SET idHuesped = ?, idPaquete = ?, fechaInicio = ?, fechaFin = ?, anticipo = ?, total = ?, estado = ?
            WHERE idReserva = ?
        ");
        if ($stmt->execute([$idHuesped, $idPaquete, $fechaInicio, $fechaFin, $anticipo, $total, $estado, $idReserva])) {
            $mensaje = "Reserva actualizada exitosamente.";
            // Recargar los datos después de guardar
            $stmt = $pdo->prepare("
                SELECT r.*, h.nombre, h.apellido
                FROM Reserva r
                JOIN Huesped h ON r.idHuesped = h.idHuesped
                WHERE r.idReserva = ?
            ");
            $stmt->execute([$idReserva]);
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Error al actualizar la reserva.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reserva - Hotel Yokoso</title>
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
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--color-rojo-quemado);">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../../index.php">
                <img src="../../assets/img/empresaLogoYokoso.png" alt="Logo Hotel Yokoso" width="40" class="me-2">
                <span class="fw-bold">Hotel Yokoso</span>
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
            <h2 class="text-rojo fw-bold">Editar Reserva #<?= htmlspecialchars($reserva['idReserva']) ?></h2>
            <a href="ver_reservas.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver a Reservas
            </a>
        </div>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="reserva-form-container">
            <form id="reservaForm" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Huésped *</label>
                        <select class="form-select" name="idHuesped" required>
                            <?php foreach ($huespedes as $huesped): ?>
                                <option value="<?= $huesped['idHuesped'] ?>" <?= $reserva['idHuesped'] == $huesped['idHuesped'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($huesped['nombre'] . ' ' . $huesped['apellido']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Paquete Turístico (Opcional)</label>
                        <select class="form-select" name="idPaquete">
                            <option value="">No deseo paquete</option>
                            <?php foreach ($paquetes as $paquete): ?>
                                <option value="<?= $paquete['idPaquete'] ?>" <?= $reserva['idPaquete'] == $paquete['idPaquete'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($paquete['nombre']) ?> - Bs. <?= htmlspecialchars($paquete['precio']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Entrada *</label>
                        <input type="date" class="form-control" name="fechaInicio" value="<?= htmlspecialchars($reserva['fechaInicio']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Salida *</label>
                        <input type="date" class="form-control" name="fechaFin" value="<?= htmlspecialchars($reserva['fechaFin']) ?>" required>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Anticipo (Bs.)</label>
                        <input type="number" class="form-control" name="anticipo" step="0.01" value="<?= htmlspecialchars($reserva['anticipo']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total (Bs.) *</label>
                        <input type="number" class="form-control" name="total" step="0.01" value="<?= htmlspecialchars($reserva['total']) ?>" required readonly>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" name="estado">
                        <option value="pendiente" <?= $reserva['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="confirmada" <?= $reserva['estado'] === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                        <option value="cancelada" <?= $reserva['estado'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        <option value="finalizada" <?= $reserva['estado'] === 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
                    </select>
                </div>

                <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="ver_reservas.php" class="btn btn-cancelar me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Aquí irá el JavaScript para calcular total, validar fechas, etc.
    </script>
</body>
</html>