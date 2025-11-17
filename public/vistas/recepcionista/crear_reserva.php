<?php
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

// Obtener todos los huéspedes activos
$stmt = $pdo->prepare("SELECT idHuesped, nombre, apellido FROM Huesped WHERE activo = 1");
$stmt->execute();
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener paquetes turísticos activos
$stmt = $pdo->prepare("SELECT idPaquete, nombre, descripcion, precio FROM PaqueteTuristico WHERE activo = 1");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener habitaciones disponibles
$stmt = $pdo->prepare("SELECT idHabitacion, numero, tipo, precioNoche, foto FROM Habitacion WHERE estado = 'disponible'");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si el formulario fue enviado, procesarlo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idHuesped = $_POST['idHuesped'];
    $idPaquete = $_POST['idPaquete'] ?: null;
    $fechaInicio = $_POST['fechaInicio'];
    $fechaFin = $_POST['fechaFin'];

    // Calcular días
    $dias = (strtotime($fechaFin) - strtotime($fechaInicio)) / (60 * 60 * 24);
    if ($dias <= 0) {
        $error = "La fecha de salida debe ser mayor a la fecha de entrada.";
    } else {
        // Calcular total de las habitaciones
        $totalHabitaciones = 0;
        $habitacionesSeleccionadas = [];

        // Procesar cada habitación seleccionada
        if (isset($_POST['cantidades'])) {
            foreach ($_POST['cantidades'] as $idHabitacion => $cantidad) {
                if ($cantidad > 0) {
                    $stmt = $pdo->prepare("SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?");
                    $stmt->execute([$idHabitacion]);
                    $habitacion = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($habitacion) {
                        $precioTotal = $habitacion['precioNoche'] * $dias * $cantidad;
                        $totalHabitaciones += $precioTotal;
                        $habitacionesSeleccionadas[] = [
                            'idHabitacion' => $idHabitacion,
                            'cantidad' => $cantidad,
                            'precioNoche' => $habitacion['precioNoche'],
                            'precioTotal' => $precioTotal
                        ];
                    }
                }
            }
        }

        // Sumar el precio del paquete si existe
        $precioPaquete = 0;
        if ($idPaquete) {
            $stmt = $pdo->prepare("SELECT precio FROM PaqueteTuristico WHERE idPaquete = ?");
            $stmt->execute([$idPaquete]);
            $paquete = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($paquete) {
                $precioPaquete = $paquete['precio'];
            }
        }

        $total = $totalHabitaciones + $precioPaquete;
        $anticipo = $_POST['anticipo'] ?: 0;
        $estado = $_POST['estado'] ?? 'pendiente';

        // Validaciones básicas
        if (empty($idHuesped) || empty($fechaInicio) || empty($fechaFin) || empty($habitacionesSeleccionadas)) {
            $error = "Los campos huésped, fechas y habitaciones son obligatorios.";
        } else {
            // Insertar reserva en la base de datos
            $stmt = $pdo->prepare("
                INSERT INTO Reserva (idHuesped, idPaquete, fechaInicio, fechaFin, anticipo, total, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$idHuesped, $idPaquete, $fechaInicio, $fechaFin, $anticipo, $total, $estado])) {
                $idReserva = $pdo->lastInsertId();

                // Insertar cada habitación en la tabla intermedia ReservaHabitacion
                foreach ($habitacionesSeleccionadas as $hab) {
                    for ($i = 0; $i < $hab['cantidad']; $i++) {
                        $stmt = $pdo->prepare("
                            INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche)
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$idReserva, $hab['idHabitacion'], $hab['precioNoche']]);
                    }
                }

                $mensaje = "Reserva creada exitosamente.";
            } else {
                $error = "Error al crear la reserva.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Reserva - Hotel Yokoso</title>
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
        .habitacion-table {
            width: 100%;
            border-collapse: collapse;
        }
        .habitacion-table th, .habitacion-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .habitacion-table th {
            background-color: var(--color-gris-claro);
        }
        .habitacion-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        .habitacion-cantidad {
            width: 60px;
            text-align: center;
        }
        .total-calculado {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--color-rojo);
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
            <h2 class="text-rojo fw-bold">Hacer Nueva Reserva</h2>
            <a href="panel_recepcionista.php" class="btn btn-volver btn-lg shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver al Panel
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
                        <select class="form-select" id="idHuesped" name="idHuesped" required>
                            <option value="">Seleccionar huésped</option>
                            <?php foreach ($huespedes as $huesped): ?>
                                <option value="<?= $huesped['idHuesped'] ?>">
                                    <?= htmlspecialchars($huesped['nombre'] . ' ' . $huesped['apellido']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Paquete Turístico (Opcional)</label>
                        <select class="form-select" id="idPaquete" name="idPaquete">
                            <option value="">No deseo paquete</option>
                            <?php foreach ($paquetes as $paquete): ?>
                                <option value="<?= $paquete['idPaquete'] ?>">
                                    <?= htmlspecialchars($paquete['nombre']) ?> - Bs. <?= htmlspecialchars($paquete['precio']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Entrada *</label>
                        <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Salida *</label>
                        <input type="date" class="form-control" id="fechaFin" name="fechaFin" required>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Habitaciones Disponibles</label>
                    <div class="border p-3 rounded">
                        <table class="habitacion-table">
                            <thead>
                                <tr>
                                    <th>Imagen</th>
                                    <th>Número</th>
                                    <th>Tipo</th>
                                    <th>Precio por Noche</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($habitaciones as $habitacion): ?>
                                    <tr>
                                        <td>
                                            <img src="/assets/img/habitaciones/<?= htmlspecialchars($habitacion['foto']) ?>" alt="Foto de habitación" class="habitacion-image">
                                        </td>
                                        <td><?= htmlspecialchars($habitacion['numero']) ?></td>
                                        <td><?= htmlspecialchars($habitacion['tipo']) ?></td>
                                        <td>Bs. <?= htmlspecialchars($habitacion['precioNoche']) ?></td>
                                        <td>
                                            <input type="number" class="form-control habitacion-cantidad" name="cantidades[<?= $habitacion['idHabitacion'] ?>]" min="0" max="10" value="0" step="1" onchange="calcularTotal()">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Anticipo (Bs.)</label>
                        <input type="number" class="form-control" id="anticipo" name="anticipo" step="0.01">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total (Bs.) *</label>
                        <input type="number" class="form-control" id="total" name="total" step="0.01" required readonly>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Preferencias Alimentarias</label>
                    <textarea class="form-control" rows="3" placeholder="Ej. Vegetariano, sin gluten, alérgico a la leche..."></textarea>
                </div>

                <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="ver_reservas.php" class="btn btn-cancelar me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                        <i class="fas fa-save me-2"></i>Guardar Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para calcular el total automáticamente
        function calcularTotal() {
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            const anticipo = parseFloat(document.getElementById('anticipo').value) || 0;

            if (!fechaInicio || !fechaFin) {
                document.getElementById('total').value = '';
                return;
            }

            const dias = (new Date(fechaFin) - new Date(fechaInicio)) / (1000 * 60 * 60 * 24);
            if (dias <= 0) {
                document.getElementById('total').value = '';
                return;
            }

            let totalHabitaciones = 0;
            const inputs = document.querySelectorAll('input[name^="cantidades"]');
            inputs.forEach(input => {
                const cantidad = parseInt(input.value) || 0;
                if (cantidad > 0) {
                    const precioNoche = parseFloat(input.closest('tr').querySelector('td:nth-child(4)').textContent.replace('Bs. ', '')) || 0;
                    totalHabitaciones += precioNoche * dias * cantidad;
                }
            });

            const idPaquete = document.getElementById('idPaquete').value;
            let precioPaquete = 0;
            if (idPaquete) {
                const option = document.querySelector(`#idPaquete option[value="${idPaquete}"]`);
                if (option) {
                    precioPaquete = parseFloat(option.textContent.split('-')[1].trim().replace('Bs.', '')) || 0;
                }
            }

            const total = totalHabitaciones + precioPaquete;
            document.getElementById('total').value = total.toFixed(2);
        }

        // Escuchar cambios en los campos
        document.getElementById('fechaInicio').addEventListener('change', calcularTotal);
        document.getElementById('fechaFin').addEventListener('change', calcularTotal);
        document.getElementById('idPaquete').addEventListener('change', calcularTotal);
        document.addEventListener('input', function(e) {
            if (e.target.name.startsWith('cantidades')) {
                calcularTotal();
            }
        });
    </script>
</body>
</html>