<?php
$body_class = 'layout-dashboard'; // para footer fijo
$title = "Selecciona un Paquete Turístico - Hotel Yokoso";
ob_start();
?>

<!-- Navbar igual al dashboard -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--color-rojo-quemado);">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="../index.php">
            <img src="../assets/img/empresaLogoYokoso.png" 
                 alt="Logo Hotel Yokoso" 
                 class="logo-navbar">
            <span class="fw-bold">Hotel Yokoso</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavGuest"
                aria-controls="navbarNavGuest" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNavGuest">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <span class="navbar-text text-white me-3">
                        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['nombreUsuario'] ?? 'Huésped') ?>
                    </span>
                </li>
                <li class="nav-item ms-2">
                    <a href="../logout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-door-open me-1"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Indicador de pasos -->
<nav aria-label="Progreso de reserva" class="mb-4">
  <ol class="progress">
    <li class="progress-bar bg-mostaza" style="width: 33%; margin-left: -33px;">Habitación</li>
    <li class="progress-bar bg-mostaza" style="width: 33%;">Paquete</li>
    <li class="progress-bar bg-light" style="width: 36%;">Confirmar</li>
  </ol>
</nav>

<div class="container py-4">
  <div class="text-center mb-5">
    <h1 class="display-5 fw-bold" style="font-family: var(--font-heading); color: var(--color-rojo);">
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
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center d-flex flex-column justify-content-center">
          <h5 class="card-title" style="font-family: var(--font-heading); color: var(--color-mostaza);">
            <i class="fas fa-times-circle me-2"></i> No, gracias
          </h5>
          <p class="card-text mb-3">Prefiero solo alojamiento.</p>
          <div class="form-check d-flex justify-content-center">
            <input class="form-check-input" type="radio" name="paquete" id="ninguno" value="" checked>
            <label class="form-check-label" for="ninguno">Seleccionar esta opción</label>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Paquetes disponibles -->
  <?php if (!empty($paquetes)): ?>
  <section class="mb-5">
    <h2 class="text-center" style="font-family: var(--font-heading); color: var(--color-rojo);">Elige una experiencia única</h2>
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
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
    <a href="rooms.php" class="btn btn-outline-secondary px-4 py-2">
      <i class="fas fa-arrow-left me-1"></i> Volver a Habitaciones
    </a>
    <button type="button" class="btn btn-mostaza text-dark btn-lg px-5 py-2" id="btnSiguiente"
            style="font-family: var(--font-body); font-weight: 600;">
      <i class="fas fa-arrow-right me-2"></i> Siguiente: Datos Personales
    </button>
  </div>
</div>

<!-- Footer -->
<footer class="bg-black text-white text-center py-3 small">
    <div class="container">
        <p class="mb-1">© <?= date('Y') ?> Hotel Yokoso. Todos los derechos reservados.</p>
        <p class="mb-0"><i class="fas fa-phone me-1"></i> +591 7000 0000</p>
    </div>
</footer>

<!-- Formulario oculto -->
<form id="formPaquetes" method="POST" action="personal.php">
  <input type="hidden" name="paquete_seleccionado" id="inputPaquete" value="">
</form>

<script>
// Actualizar valor al cambiar selección
document.querySelectorAll('input[name="paquete"]').forEach(radio => {
  radio.addEventListener('change', function() {
    document.getElementById('inputPaquete').value = this.value;
  });
});

// Enviar formulario
document.getElementById('btnSiguiente').addEventListener('click', function() {
  document.getElementById('formPaquetes').submit();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>