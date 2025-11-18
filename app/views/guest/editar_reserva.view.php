<?php
$title = "Editar Reserva #" . $reserva['idReserva'];
ob_start();
?>

<!-- Navbar responsive  -->
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
    <h1 class="h3 mb-4" style="font-family: var(--font-heading); color: var(--color-rojo);">
        <i class="fas fa-edit me-2"></i> Editar Reserva #<?= $reserva['idReserva'] ?>
    </h1>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" class="needs-validation" novalidate>
        <!-- Fechas -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-calendar me-2"></i> Fechas de Estadía</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de Entrada <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                               value="<?= htmlspecialchars($reserva['fechaInicio']) ?>" required>
                        <div class="invalid-feedback">Ingresa una fecha de entrada válida.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_fin" class="form-label">Fecha de Salida <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                               value="<?= htmlspecialchars($reserva['fechaFin']) ?>" required>
                        <div class="invalid-feedback">Ingresa una fecha de salida válida.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Habitaciones -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-bed me-2"></i> Seleccionar Habitaciones <span class="text-danger">*</span></h5>
            </div>
            <div class="card-body">
                <?php if (empty($habitacionesDisponibles)): ?>
                    <p class="text-muted">No hay habitaciones disponibles en este momento.</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($habitacionesDisponibles as $hab): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <?php if (!empty($hab['foto'])): ?>
                                    <img src="../assets/img/habitaciones/<?= htmlspecialchars($hab['foto']) ?>" 
                                         alt="Hab. <?= htmlspecialchars($hab['numero']) ?>" 
                                         class="img-fluid rounded mb-2"
                                         style="height: 100px; object-fit: cover; width: 100%;">
                                <?php else: ?>
                                    <div class="bg-light border d-flex align-items-center justify-content-center mb-2" style="height: 100px;">
                                        <i class="fas fa-bed text-muted" style="font-size: 1.5rem;"></i>
                                    </div>
                                <?php endif; ?>
                                <h6 class="mb-1">Hab. <?= htmlspecialchars($hab['numero']) ?></h6>
                                <p class="mb-1"><small><?= ucfirst($hab['tipo']) ?></small></p>
                                <p class="mb-2"><strong>Bs <?= number_format($hab['precioNoche'], 2) ?>/noche</strong></p>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="habitaciones[]" 
                                           value="<?= $hab['idHabitacion'] ?>" 
                                           id="hab_<?= $hab['idHabitacion'] ?>"
                                           <?= in_array($hab['idHabitacion'], $habitacionesActuales) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="hab_<?= $hab['idHabitacion'] ?>">
                                        Seleccionar
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Paquete turístico -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-gift me-2"></i> Paquete Turístico (Opcional)</h5>
            </div>
            <div class="card-body">
                <select class="form-select" name="paquete" id="paquete">
                    <option value="">— Sin paquete —</option>
                    <?php foreach ($paquetes as $p): ?>
                    <option value="<?= $p['idPaquete'] ?>" 
                            <?= ($reserva['idPaquete'] == $p['idPaquete']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nombre']) ?> — Bs <?= number_format($p['precio'], 2) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-rojo">
                <i class="fas fa-save me-1"></i> Guardar Cambios
            </button>
            <a href="../guest/dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<!-- Footer -->
<footer class="bg-black text-white text-center py-3 small">
    <div class="container">
        <p class="mb-1">© <?= date('Y') ?> Hotel Yokoso. Todos los derechos reservados.</p>
        <p class="mb-0"><i class="fas fa-phone me-1"></i> +591 7000 0000</p>
    </div>
</footer>

<!-- Validación Bootstrap -->
<script>
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>