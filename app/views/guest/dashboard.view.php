<?php
// Obtener nombre del usuario logueado
$nombreUsuario = $_SESSION['nombreUsuario'] ?? 'Huésped';
$title = "Mi Cuenta - Hotel Yokoso";
$body_class = 'layout-dashboard';
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
  <?php if ($mensajeExito): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i> <?= $mensajeExito ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['cancelada'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <i class="fas fa-ban me-2"></i> Reserva cancelada exitosamente.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3" style="font-family: var(--font-heading); color: var(--color-rojo);">
      <i class="fas fa-list me-2"></i> Mis Reservas
    </h1>
    <a href="/guest/rooms.php" class="btn btn-mostaza text-dark">
      <i class="fas fa-plus me-1"></i> Nueva Reserva
    </a>
  </div>

  <?php if (empty($reservas)): ?>
    <div class="text-center py-5">
      <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
      <h4 class="mt-3">No tienes reservas aún</h4>
      <p class="text-muted">¡Empieza tu aventura en el Salar de Uyuni!</p>
      <a href="/guest/rooms.php" class="btn btn-rojo">Crear mi primera reserva</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Habitaciones</th>
            <th>Paquete</th>
            <th>Fechas</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reservas as $r): ?>
          <tr>
            <td><?= $r['idReserva'] ?></td>
            <td><?= htmlspecialchars($r['habitaciones'] ?? '—') ?></td>
            <td><?= htmlspecialchars($r['paquete'] ?? 'Sin paquete') ?></td>
            <td>
              <?= $r['fechaInicio'] ? date('d/m/Y', strtotime($r['fechaInicio'])) : '—' ?><br>
              <?= $r['fechaFin'] ? date('d/m/Y', strtotime($r['fechaFin'])) : '—' ?>
            </td>
            <td>Bs <?= number_format($r['total'], 2) ?></td>
            <td>
              <span class="badge 
                <?= match($r['estado']) {
                    'pendiente' => 'bg-warning text-dark',
                    'confirmada' => 'bg-success',
                    'cancelada' => 'bg-danger',
                    default => 'bg-secondary'
                } ?>">
                <?= ucfirst($r['estado']) ?>
              </span>
            </td>
            <td>
              <a href="/guest/reserva_detalle.php?id=<?= $r['idReserva'] ?>" 
                 class="btn btn-sm btn-outline-info me-1" title="Ver">
                <i class="fas fa-eye"></i>
              </a>
              <?php if ($r['estado'] === 'pendiente'): ?>
                <a href="/guest/editar_reserva.php?id=<?= $r['idReserva'] ?>" 
                   class="btn btn-sm btn-outline-primary me-1" title="Editar">
                  <i class="fas fa-edit"></i>
                </a>
                <form method="POST" class="d-inline" onsubmit="return confirmarAccion('¿Cancelar esta reserva?');">
                  <input type="hidden" name="idReserva" value="<?= $r['idReserva'] ?>">
                  <input type="hidden" name="cancelar_reserva" value="1">
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancelar">
                    <i class="fas fa-times"></i>
                  </button>
                </form>
              <?php else: ?>
                <button class="btn btn-sm btn-outline-secondary me-1" disabled><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-secondary" disabled><i class="fas fa-times"></i></button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Footer personalizado -->
<footer class="bg-black text-white text-center py-3 small">
    <div class="container">
        <p class="mb-1">© <?= date('Y') ?> Hotel Yokoso. Todos los derechos reservados.</p>
        <p class="mb-0"><i class="fas fa-phone me-1"></i> +591 7000 0000</p>
    </div>
</footer>

<?php $body_class = 'prueba-footer'; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>