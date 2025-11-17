<?php
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

// Obtener todos los huéspedes activos
$stmt = $pdo->prepare("
    SELECT idHuesped, nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria, fechaRegistro
    FROM Huesped
    WHERE activo = 1
    ORDER BY fechaRegistro DESC
");
$stmt->execute();
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Huéspedes - Hotel Yokoso</title>
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
        }
        .status-activo { background-color: #28a745; color: white; }
        .status-inactivo { background-color: #6c757d; color: white; }
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
            <h2 class="text-rojo fw-bold">Huéspedes Registrados</h2>
            <a href="panel_recepcionista.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Panel
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered border-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Tipo Doc.</th>
                        <th>Nro. Doc.</th>
                        <th>Procedencia</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($huespedes)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">No hay huéspedes registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($huespedes as $huesped): ?>
                            <tr>
                                <td><?= htmlspecialchars($huesped['idHuesped']) ?></td>
                                <td><?= htmlspecialchars($huesped['nombre']) ?></td>
                                <td><?= htmlspecialchars($huesped['apellido']) ?></td>
                                <td><?= htmlspecialchars($huesped['tipoDocumento']) ?></td>
                                <td><?= htmlspecialchars($huesped['nroDocumento']) ?></td>
                                <td><?= htmlspecialchars($huesped['procedencia']) ?></td>
                                <td><?= htmlspecialchars($huesped['email']) ?></td>
                                <td><?= htmlspecialchars($huesped['telefono']) ?></td>
                                <td><?= htmlspecialchars($huesped['fechaRegistro']) ?></td>
                                <td class="text-center">
                                    <a href="editar_huesped.php?id=<?= $huesped['idHuesped'] ?>" class="btn btn-outline-primary btn-sm me-1" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm" onclick="eliminarHuesped(<?= $huesped['idHuesped'] ?>)" title="Eliminar">
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
                    ¿Estás seguro de que deseas eliminar este huésped? Esta acción no se puede deshacer.
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
        let huespedAEliminar = null;

        function eliminarHuesped(id) {
            huespedAEliminar = id;
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            modal.show();
        }

        document.getElementById('confirmarEliminarBtn').addEventListener('click', function() {
            if (huespedAEliminar) {
                // Aquí iría la lógica para eliminar (por ejemplo, con fetch o redirección)
                // Por ahora, redirigimos a una URL que manejará la eliminación
                window.location.href = 'eliminar_huesped.php?id=' + huespedAEliminar;
            }
        });
    </script>
</body>
</html>