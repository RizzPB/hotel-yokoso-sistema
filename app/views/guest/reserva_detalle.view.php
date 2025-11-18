<?php
$title = "Reserva #" . $reserva['idReserva'] . " - YokosoStay";
ob_start();
?>

<!-- Navbar responsive -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--color-rojo-quemado);">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="../index.php">
            <img src="../assets/img/empresaLogoYokoso.png" 
                 alt="Logo Hotel Yokoso" 
                 class="logo-navbar">
            <span class="fw-bold">Hotel Yokoso</span>
        </a>

        <!-- Botón hamburguesa para móviles -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavHuésped" 
                aria-controls="navbarNavHuésped" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menú colapsable -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNavHuésped">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <span class="navbar-text text-white me-3">
                        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['nombreUsuario'] ?? 'Huésped') ?>
                    </span>
                </li>
                <?php if (basename($_SERVER['PHP_SELF']) !== 'dashboard.php'): ?>
                <li class="nav-item ms-2">
                    <a href="../guest/dashboard.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item ms-2">
                    <a href="../logout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-door-open me-1"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3" style="font-family: var(--font-heading); color: var(--color-rojo);">
            <i class="fas fa-file-alt me-2"></i> Detalle de Reserva #<?= $reserva['idReserva'] ?>
        </h1>
        <span class="badge 
            <?= match($reserva['estado']) {
                'pendiente' => 'bg-warning text-dark',
                'confirmada' => 'bg-success',
                'cancelada' => 'bg-danger',
                'finalizada' => 'bg-secondary',
                default => 'bg-secondary'
            } ?>">
            <?= ucfirst($reserva['estado']) ?>
        </span>
    </div>

    <!-- Información del huésped -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i> Huésped</h5>
        </div>
        <div class="card-body">
            <p><strong>Nombre:</strong> <?= htmlspecialchars($reserva['huespedNombre'] . ' ' . $reserva['huespedApellido']) ?></p>
            <p><strong>Documento:</strong> <?= htmlspecialchars($reserva['tipoDocumento']) ?> <?= htmlspecialchars($reserva['nroDocumento'] ?? '—') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($reserva['huespedEmail']) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($reserva['huespedTelefono'] ?? '—') ?></p>
        </div>
    </div>

    <!-- Fechas y pagos -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-calendar me-2"></i> Fechas</h5>
                </div>
                <div class="card-body">
                    <p><strong>Entrada:</strong> <?= $reserva['fechaInicio'] ? date('d/m/Y', strtotime($reserva['fechaInicio'])) : '—' ?></p>
                    <p><strong>Salida:</strong> <?= $reserva['fechaFin'] ? date('d/m/Y', strtotime($reserva['fechaFin'])) : '—' ?></p>
                    <?php if ($reserva['fechaCheckIn']): ?>
                        <p><strong>Check-in:</strong> <?= date('d/m/Y', strtotime($reserva['fechaCheckIn'])) ?></p>
                    <?php endif; ?>
                    <?php if ($reserva['fechaCheckOut']): ?>
                        <p><strong>Check-out:</strong> <?= date('d/m/Y', strtotime($reserva['fechaCheckOut'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i> Pago</h5>
                </div>
                <div class="card-body">
                    <p><strong>Total:</strong> Bs <?= number_format($reserva['total'], 2) ?></p>
                    <p><strong>Anticipo:</strong> Bs <?= $reserva['anticipo'] ? number_format($reserva['anticipo'], 2) : 'No aplicado' ?></p>
                    <p><strong>Pendiente:</strong> Bs <?= number_format($reserva['total'] - ($reserva['anticipo'] ?? 0), 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Habitaciones -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-bed me-2"></i> Habitaciones Reservadas</h5>
        </div>
        <div class="card-body">
            <?php if (empty($habitaciones)): ?>
                <p class="text-muted">No se asignaron habitaciones.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($habitaciones as $hab): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="border rounded p-3 text-center">
                            <?php if (!empty($hab['foto'])): ?>
                                <img src="/assets/img/habitaciones/<?= htmlspecialchars($hab['foto']) ?>" 
                                     alt="Habitación <?= htmlspecialchars($hab['numero']) ?>" 
                                     class="img-fluid rounded mb-2"
                                     style="height: 120px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light border d-flex align-items-center justify-content-center mb-2" style="height: 120px;">
                                    <i class="fas fa-bed text-muted" style="font-size: 2rem;"></i>
                                </div>
                            <?php endif; ?>
                            <h6 class="mb-1">Hab. <?= htmlspecialchars($hab['numero']) ?></h6>
                            <p class="mb-1"><small><?= ucfirst($hab['tipo']) ?></small></p>
                            <p class="mb-0"><strong>Bs <?= number_format($hab['precioNoche'], 2) ?>/noche</strong></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Paquete turístico (si existe) -->
    <?php if (!empty($reserva['paqueteNombre'])): ?>
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-gift me-2"></i> Paquete Turístico</h5>
        </div>
        <div class="card-body">
            <h5><?= htmlspecialchars($reserva['paqueteNombre']) ?></h5>
            <p><?= htmlspecialchars($reserva['paqueteDescripcion'] ?? '—') ?></p>
            <p><strong>Precio:</strong> Bs <?= number_format($reserva['paquetePrecio'], 2) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="/guest/dashboard.php" class="btn btn-outline-rojo">
            <i class="fas fa-arrow-left me-1"></i> Volver a Mis Reservas
        </a>
    </div>
</div>

<!-- Footer -->
<footer class="bg-black text-white text-center py-3 small">
    <div class="container">
        <p class="mb-1">© <?= date('Y') ?> Hotel Yokoso. Todos los derechos reservados.</p>
        <p class="mb-0"><i class="fas fa-phone me-1"></i> +591 7000 0000</p>
    </div>
</footer>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>