<?php
$title = "Selecciona tu Habitación - Hotel Yokoso";
ob_start();
?>

<!-- Indicador de pasos (Criterio 7) -->
<nav aria-label="Progreso de reserva" class="mb-4">
  <ol class="progress" style="height: 8px;">
    <li class="progress-bar bg-mostaza" style="width: 33%;">Habitación</li>
    <li class="progress-bar bg-light" style="width: 33%;">Paquete</li>
    <li class="progress-bar bg-light" style="width: 34%;">Confirmar</li>
  </ol>
</nav>

<div class="container py-4">
  <div class="text-center mb-5">
    <h1 class="display-5 fw-bold text-rojo" style="font-family: var(--font-heading);">
      Elige tu Habitación en Uyuni
    </h1>
    <p class="lead" style="font-family: var(--font-body);">
      Selecciona una o más habitaciones para tu estadía. Solo se muestran las disponibles.
    </p>
  </div>

  <!-- Sección: Habitaciones de Sal (Criterio 4) -->
  <?php if (!empty($habitacionesSal)): ?>
  <section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-mostaza" style="font-family: var(--font-heading);">✨ Habitaciones de Sal</h2>
      <span class="badge bg-mostaza text-dark"><?= count($habitacionesSal) ?> disponibles</span>
    </div>
    <div class="row g-4">
      <?php foreach ($habitacionesSal as $h): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <img src="/assets/img/habitaciones/<?= htmlspecialchars($h['foto'] ?? 'sal-placeholder.jpg') ?>" 
               class="card-img-top" alt="Habitación <?= $h['numero'] ?>" 
               style="height: 200px; object-fit: cover;">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title" style="font-family: var(--font-heading);">Habitación <?= $h['numero'] ?></h5>
            <p class="card-text flex-grow-1" style="font-family: var(--font-body);">
              <span class="d-block mb-1"><i class="fas fa-mountain text-mostaza me-1"></i> <strong>Suite de Sal Única</strong></span>
              <span class="d-block"><i class="fas fa-tag text-mostaza me-1"></i> <strong>Bs <?= number_format($h['precioNoche'], 2) ?></strong> / noche</span>
            </p>
            <!-- Campo de selección (Criterio 1) -->
            <div class="form-check mt-auto">
              <input class="form-check-input" type="checkbox" 
                     name="habitaciones[]" value="<?= $h['idHabitacion'] ?>" id="h<?= $h['idHabitacion'] ?>">
              <label class="form-check-label" for="h<?= $h['idHabitacion'] ?>">
                Seleccionar esta habitación
              </label>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Sección: Habitaciones Normales (Criterio 4) -->
  <?php if (!empty($habitacionesNormales)): ?>
  <section>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-rojo" style="font-family: var(--font-heading);">🏡 Habitaciones Normales / Rústicas</h2>
      <span class="badge bg-rojo text-white"><?= count($habitacionesNormales) ?> disponibles</span>
    </div>
    <div class="row g-4">
      <?php foreach ($habitacionesNormales as $h): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <img src="/assets/img/habitaciones/<?= htmlspecialchars($h['foto'] ?? 'normal-placeholder.jpg') ?>" 
               class="card-img-top" alt="Habitación <?= $h['numero'] ?>" 
               style="height: 200px; object-fit: cover;">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title" style="font-family: var(--font-heading);">Habitación <?= $h['numero'] ?></h5>
            <p class="card-text flex-grow-1" style="font-family: var(--font-body);">
              <span class="d-block mb-1">
                <i class="fas fa-home text-mostaza me-1"></i> 
                <strong><?= $h['tipo'] === 'doble' ? 'Doble' : 'Simple' ?></strong>
                <?php if (in_array($h['numero'], ['5A','5B','10A','10B'])): ?>
                  <span class="badge bg-warning text-dark ms-1">Departamento</span>
                <?php endif; ?>
              </span>
              <span class="d-block"><i class="fas fa-tag text-mostaza me-1"></i> <strong>Bs <?= number_format($h['precioNoche'], 2) ?></strong> / noche</span>
            </p>
            <div class="form-check mt-auto">
              <input class="form-check-input" type="checkbox" 
                     name="habitaciones[]" value="<?= $h['idHabitacion'] ?>" id="h<?= $h['idHabitacion'] ?>">
              <label class="form-check-label" for="h<?= $h['idHabitacion'] ?>">
                Seleccionar esta habitación
              </label>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Botones de acción (Criterio 2) -->
  <div class="text-center mt-5">
    <button type="button" class="btn btn-rojo btn-lg px-5 py-2" id="btnSiguiente" disabled
            style="font-family: var(--font-body);">
      <i class="fas fa-arrow-right me-2"></i> Siguiente: Paquetes Turísticos
    </button>
    <a href="/logout.php" class="d-block mt-3 text-muted" style="font-family: var(--font-body);">
      ¿No eres tú? Cerrar sesión
    </a>
  </div>
</div>

<!-- Formulario oculto para enviar selección -->
<form id="formHabitaciones" method="POST" action="packages.php">
  <input type="hidden" name="habitaciones_seleccionadas" id="inputHabitaciones">
</form>

<!-- Validación y envío (Criterio 3) -->
<script>
document.querySelectorAll('input[name="habitaciones[]"]').forEach(checkbox => {
  checkbox.addEventListener('change', function() {
    const seleccionadas = Array.from(document.querySelectorAll('input[name="habitaciones[]"]:checked'))
                               .map(cb => cb.value);
    document.getElementById('inputHabitaciones').value = JSON.stringify(seleccionadas);
    document.getElementById('btnSiguiente').disabled = seleccionadas.length === 0;
  });
});

document.getElementById('btnSiguiente').addEventListener('click', function() {
  const seleccionadas = JSON.parse(document.getElementById('inputHabitaciones').value);
  if (seleccionadas.length === 0) {
    alert("⚠️ Por favor, selecciona al menos una habitación.");
    return;
  }
  document.getElementById('formHabitaciones').submit();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>