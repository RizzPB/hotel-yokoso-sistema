<?php
$title = "Mi Cuenta - Hotel Yokoso";
ob_start();
?>

<div class="container py-4">
  <!-- Mensaje de éxito (nueva reserva) -->
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
    <h1 class="h3 text-rojo" style="font-family: var(--font-heading);">
      <i class="fas fa-home me-2"></i> Mis Reservas
    </h1>
    <a href="rooms.php" class="btn btn-mostaza text-dark">
      <i class="fas fa-plus me-1"></i> Nueva Reserva
    </a>
  </div>

  <?php if (empty($reservas)): ?>
    <div class="text-center py-5">
      <i class="fas fa-inbox text-gris-medio" style="font-size: 3rem;"></i>
      <h4 class="mt-3">No tienes reservas aún</h4>
      <p class="text-muted">¡Empieza tu aventura en el Salar de Uyuni!</p>
      <a href="rooms.php" class="btn btn-rojo mt-3">Crear mi primera reserva</a>
    </div>
  <?php else: ?>
    <div class="row">
      <?php foreach ($reservas as $r): ?>
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between">
              <h5 class="card-title">#<?= $r['idReserva'] ?></h5>
              <span class="badge 
                <?php if ($r['estado'] === 'pendiente') echo 'bg-warning text-dark';
                      elseif ($r['estado'] === 'confirmada') echo 'bg-success';
                      elseif ($r['estado'] === 'cancelada') echo 'bg-danger';
                      else echo 'bg-secondary'; ?>">
                <?= ucfirst($r['estado']) ?>
              </span>
            </div>
            <p class="card-text">
              <i class="fas fa-bed me-1"></i> <?= htmlspecialchars($r['habitaciones']) ?><br>
              <i class="fas fa-calendar me-1"></i> 
                <?= $r['fechaInicio'] ? date('d/m/Y', strtotime($r['fechaInicio'])) : 'Sin fechas' ?> 
                - 
                <?= $r['fechaFin'] ? date('d/m/Y', strtotime($r['fechaFin'])) : 'Sin fechas' ?><br>
              <i class="fas fa-tag me-1"></i> Bs <?= number_format($r['total'], 2) ?>
            </p>
            <div class="mt-auto">
              <?php if ($r['estado'] === 'pendiente'): ?>
                <form method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta reserva?');">
                  <input type="hidden" name="idReserva" value="<?= $r['idReserva'] ?>">
                  <input type="hidden" name="cancelar_reserva" value="1">
                  <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-times me-1"></i> Cancelar
                  </button>
                </form>
              <?php endif; ?>
              <!-- Más acciones aquí si se necesitan -->
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Menú de usuario -->
  <div class="text-center mt-5 pt-4 border-top">
    <a href="/logout.php" class="btn btn-outline-rojo">
      <i class="fas fa-door-open me-1"></i> Cerrar Sesión
    </a>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>