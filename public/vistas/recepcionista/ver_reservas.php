<?php
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

// Si se pidió hacer check-in
if (isset($_GET['checkin']) && !empty($_GET['checkin'])) {
    $idReserva = $_GET['checkin'];

    // Iniciar transacción para garantizar consistencia
    $pdo->beginTransaction();

    try {
        // 1. Actualizar el estado de la reserva a 'confirmada'
        $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'confirmada' WHERE idReserva = ?");
        $stmt->execute([$idReserva]);

        // 2. Obtener la habitación asignada a esta reserva
        $stmt = $pdo->prepare("SELECT idHabitacion FROM ReservaHabitacion WHERE idReserva = ?");
        $stmt->execute([$idReserva]);
        $habitacion = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($habitacion) {
            // 3. Actualizar el estado de la habitación a 'ocupada'
            $stmt = $pdo->prepare("UPDATE Habitacion SET estado = 'ocupada' WHERE idHabitacion = ?");
            $stmt->execute([$habitacion['idHabitacion']]);
        }

        $pdo->commit();
        $mensaje = "Check-in realizado exitosamente. ¡Bienvenido al hotel!";
    } catch (Exception $e) {
        $pdo->rollback();
        $error = "Error al realizar el check-in: " . $e->getMessage();
    }

    // Redirigir para evitar reenvíos
    header("Location: ver_reservas.php?mensaje=" . urlencode($mensaje ?? '') . "&error=" . urlencode($error ?? ''));
    exit;
}

// Si se pidió hacer check-out
if (isset($_GET['checkout']) && !empty($_GET['checkout'])) {
    $idReserva = $_GET['checkout'];

    // Iniciar transacción para garantizar consistencia
    $pdo->beginTransaction();

    try {
        // 1. Actualizar el estado de la reserva a 'finalizada'
        $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'finalizada' WHERE idReserva = ?");
        $stmt->execute([$idReserva]);

        // 2. Obtener la habitación asignada a esta reserva
        $stmt = $pdo->prepare("SELECT idHabitacion FROM ReservaHabitacion WHERE idReserva = ?");
        $stmt->execute([$idReserva]);
        $habitacion = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($habitacion) {
            // 3. Actualizar el estado de la habitación a 'disponible'
            $stmt = $pdo->prepare("UPDATE Habitacion SET estado = 'disponible' WHERE idHabitacion = ?");
            $stmt->execute([$habitacion['idHabitacion']]);
        }

        $pdo->commit();
        $mensaje = "Check-out realizado exitosamente. ¡Gracias por su visita!";
    } catch (Exception $e) {
        $pdo->rollback();
        $error = "Error al realizar el check-out: " . $e->getMessage();
    }

    // Redirigir para evitar reenvíos
    header("Location: ver_reservas.php?mensaje=" . urlencode($mensaje ?? '') . "&error=" . urlencode($error ?? ''));
    exit;
}

// Obtener todas las reservas con información del huésped y habitación
$stmt = $pdo->prepare("
    SELECT r.idReserva, h.nombre, h.apellido, rh.idHabitacion, r.fechaInicio, r.fechaFin, r.total, r.estado
    FROM Reserva r
    JOIN Huesped h ON r.idHuesped = h.idHuesped
    JOIN ReservaHabitacion rh ON r.idReserva = rh.idReserva
    ORDER BY r.fechaInicio DESC
");
$stmt->execute();
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Reservas - Hotel Yokoso</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pendiente { background-color: #ffc107; color: black; }
        .status-confirmada { background-color: #28a745; color: white; }
        .status-cancelada { background-color: #dc3545; color: white; }
        .status-finalizada { background-color: #6c757d; color: white; }
        .btn-checkin {
            background-color: #28a745;
            color: white;
            border: none;
            font-weight: bold;
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        .btn-checkin:hover {
            background-color: #218838;
        }
        .btn-checkout {
            background-color: #17a2b8;
            color: white;
            border: none;
            font-weight: bold;
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        .btn-checkout:hover {
            background-color: #138496;
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
            <h2 class="text-rojo fw-bold">Reservas Registradas</h2>
            <a href="panel_recepcionista.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Panel
            </a>
        </div>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered border-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Huésped</th>
                        <th>Habitación</th>
                        <th>Fechas</th>
                        <th>Total (Bs.)</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservas)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">No hay reservas registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reservas as $reserva): ?>
                            <tr>
                                <td><?= htmlspecialchars($reserva['idReserva']) ?></td>
                                <td><?= htmlspecialchars($reserva['nombre'] . ' ' . $reserva['apellido']) ?></td>
                                <td><?= htmlspecialchars($reserva['idHabitacion']) ?></td>
                                <td>
                                    <?= htmlspecialchars($reserva['fechaInicio']) ?> - <?= htmlspecialchars($reserva['fechaFin']) ?>
                                </td>
                                <td><?= htmlspecialchars($reserva['total']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $reserva['estado'] ?>">
                                        <?= ucfirst($reserva['estado']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <!-- Botón Editar -->
                                    <a href="editar_reserva.php?id=<?= $reserva['idReserva'] ?>" class="btn btn-outline-primary btn-sm me-1" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Botón Check-in (solo si está pendiente) -->
                                    <?php if ($reserva['estado'] === 'pendiente'): ?>
                                        <a href="ver_reservas.php?checkin=<?= $reserva['idReserva'] ?>" class="btn btn-checkin btn-sm me-1" title="Realizar Check-in">
                                            <i class="fas fa-door-open"></i> Check-in
                                        </a>
                                    <?php endif; ?>

                                    <!-- Botón Check-out (solo si está confirmada) -->
                                    <?php if ($reserva['estado'] === 'confirmada'): ?>
                                        <a href="ver_reservas.php?checkout=<?= $reserva['idReserva'] ?>" class="btn btn-checkout btn-sm me-1" title="Realizar Check-out">
                                            <i class="fas fa-door-closed"></i> Check-out
                                        </a>
                                    <?php endif; ?>

                                    <!-- Botón Eliminar -->
                                    <button class="btn btn-outline-danger btn-sm" onclick="eliminarReserva(<?= $reserva['idReserva'] ?>)" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar esta reserva? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminarBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let reservaAEliminar = null;

        function eliminarReserva(id) {
            reservaAEliminar = id;
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            modal.show();
        }

        document.getElementById('confirmarEliminarBtn').addEventListener('click', function() {
            if (reservaAEliminar) {
                window.location.href = 'eliminar_reserva.php?id=' + reservaAEliminar;
            }
        });
    </script>
</body>
</html>