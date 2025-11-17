<?php
// Recuperar desde sesión
$reservaId = $_SESSION['reserva_id'] ?? 'N/A';
$total = $_SESSION['reserva_total'] ?? 0;

$title = "Solicitud Enviada - Hotel Yokoso";
ob_start();
?>

<div class="container py-5">
  <div class="text-center">
    <div class="mb-4">
      <i class="fas fa-check-circle" style="font-size: 4rem;"></i>
    </div>
    <h1 class="display-5 fw-bold text-rojo" style="font-family: var(--font-heading);">
      ¡Solicitud de Reserva Enviada!
    </h1>
    <p class="lead" style="font-family: var(--font-body); max-width: 700px; margin: 0 auto;">
      Tu solicitud ha sido recibida y está <strong>pendiente de aprobación</strong> por el administrador de Hotel Yokoso.
    </p>
    <div class="alert alert-info mt-4" style="font-family: var(--font-body);">
      <i class="fas fa-envelope me-2"></i>
      <strong>Próximos pasos:</strong> 
      El administrador revisará la disponibilidad de las habitaciones y te enviará un mensaje a tu correo con la confirmación o sugerencias alternativas.
    </div>
    <div class="mt-4 p-4 bg-gris-claro rounded">
      <h5 class="text-rojo mb-3" style="font-family: var(--font-heading);">Resumen de tu solicitud</h5>
      <ul class="list-group list-group-flush">
        <li class="list-group-item"><strong>Reserva #:</strong> <?= htmlspecialchars($reservaId) ?></li>
        <li class="list-group-item"><strong>Total estimado:</strong> Bs <?= number_format($total, 2) ?></li>
        <li class="list-group-item"><strong>Estado:</strong> <span class="badge bg-warning text-dark">Pendiente</span></li>
      </ul>
    </div>
    <div class="mt-5">
      <a href="/" class="btn btn-outline-rojo me-2">
        <i class="fas fa-home me-1"></i> Volver al Inicio
      </a>
      <a href="/logout.php" class="btn btn-rojo">
        <i class="fas fa-door-open me-1"></i> Cerrar Sesión
      </a>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>