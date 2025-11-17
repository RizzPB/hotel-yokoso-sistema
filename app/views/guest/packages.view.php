<?php
$title = "Selecciona un Paquete Turístico - Hotel Yokoso";
ob_start();
?>

<!-- Indicador de pasos -->
<nav aria-label="Progreso de reserva" class="mb-4">
  <ol class="progress" style="height: 8px;">
    <li class="progress-bar bg-mostaza" style="width: 33%;">Habitación</li>
    <li class="progress-bar bg-mostaza" style="width: 33%;">Paquete</li>
    <li class="progress-bar bg-light" style="width: 34%;">Confirmar</li>
  </ol>
</nav>

<div class="container py-4">
  <div class="text-center mb-5">
    <h1 class="display-5 fw-bold text-rojo" style="font-family: var(--font-heading);">
      <i class="fas fa-mountain me-2"></i> Paquetes Turísticos al Salar
    </h1>
    <p class="lead" style="font-family: var(--font-body);">
      Enriquiza tu estadía con una experiencia inolvidable en el Salar de Uyuni.  
      <strong>Opcional:</strong> puedes continuar sin paquete.
    </p>
  </div>

  <!-- Opción: Ningún paquete -->
  <div class="row justify-content-center mb-5">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
          <h5 class="card-title text-mostaza" style="font-family: var(--font-heading);">
            <i class="fas fa-times-circle me-2"></i> No, gracias
          </h5>
          <p class="card-text">Prefiero solo alojamiento.</p>
          <div class="form-check d-inline-block">
            <input class="form-check-input" type="radio" name="paquete" id="ninguno" value="" checked>
            <label class="form-check-label" for="ninguno">Seleccionar</label>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Paquetes disponibles -->
  <?php if (!empty($paquetes)): ?>
  <section>
    <h2 class="text-center text-rojo mb-4" style="font-family: var(--font-heading);">elige una experiencia única</h2>
    <div class="row g-4">
      <?php foreach ($paquetes as $p): ?>
      <div class="col-md-6 col-lg-6">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h5 class="card-title mb-1" style="font-family: var(--font-heading);">
                <?= htmlspecialchars($p['nombre']) ?>
              </h5>
              <span class="badge bg-mostaza text-dark"><?= $p['duracionDias'] ?> día<?= $p['duracionDias'] > 1 ? 's' : '' ?></span>
            </div>
            <p class="card-text flex-grow-1" style="font-family: var(--font-body);">
              <?= htmlspecialchars($p['descripcion']) ?>
            </p>
            <div class="mt-2">
              <strong class="text-rojo">Bs <?= number_format($p['precio'], 2) ?></strong>
            </div>
            <div class="mt-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" 
                       name="paquete" value="<?= $p['idPaquete'] ?>" id="p<?= $p['idPaquete'] ?>">
                <label class="form-check-label" for="p<?= $p['idPaquete'] ?>">
                  Seleccionar este paquete
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Botones de acción -->
  <div class="text-center mt-5">
    <button type="button" class="btn btn-rojo btn-lg px-5 py-2" id="btnSiguiente"
            style="font-family: var(--font-body);">
      <i class="fas fa-arrow-right me-2"></i> Siguiente: Datos Personales
    </button>
    <a href="rooms.php" class="d-block mt-3 text-muted" style="font-family: var(--font-body);">
      ← Volver a seleccionar habitaciones
    </a>
  </div>
</div>

<!-- Formulario para enviar -->
<form id="formPaquetes" method="POST" action="personal.php">
  <input type="hidden" name="paquete_seleccionado" id="inputPaquete">
</form>

<script>
document.querySelectorAll('input[name="paquete"]').forEach(radio => {
  radio.addEventListener('change', function() {
    document.getElementById('inputPaquete').value = this.value;
  });
});

document.getElementById('btnSiguiente').addEventListener('click', function() {
  // Siempre permite continuar (paquete es opcional)
  document.getElementById('formPaquetes').submit();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>