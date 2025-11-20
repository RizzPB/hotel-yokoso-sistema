<?php
$body_class = 'layout-dashboard'; // para footer fijo
$title = "Completa tus Datos - Hotel Yokoso";
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
    <li class="progress-bar bg-mostaza" style="width: 36%;">Confirmar</li>
  </ol>
</nav>

<div class="container py-4">
  <div class="text-center mb-5">
    <h1 class="display-5 fw-bold" style="font-family: var(--font-heading); color: var(--color-rojo);">
      <i class="fas fa-user-edit me-2"></i> Tus Datos Personales
    </h1>
    <p class="lead" style="font-family: var(--font-body);">
      Solo necesitamos información básica para tu solicitud de reserva.  
      <strong>Los documentos se verificarán al hacer check-in.</strong>
    </p>
  </div>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($errors['general']) ?></div>
  <?php endif; ?>

  <form method="POST" class="needs-validation" novalidate>

    <!-- Fechas -->
    <div class="card mb-4 shadow-sm border-0">
      <div class="card-header bg-mostaza text-dark">
        <h5 class="mb-0" style="font-family: var(--font-heading);">
          <i class="fas fa-calendar-check me-2"></i> Fechas de Estadía
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="fechaInicio" class="form-label">Fecha de Entrada <span class="text-danger">*</span></label>
            <input type="date" class="form-control <?= !empty($errors['fechaInicio']) ? 'is-invalid' : '' ?>" 
                   id="fechaInicio" name="fechaInicio" value="<?= htmlspecialchars($datos['fechaInicio'] ?? '') ?>" required>
            <?php if (!empty($errors['fechaInicio'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['fechaInicio']) ?></div>
            <?php endif; ?>
          </div>
          <div class="col-md-6 mb-3">
            <label for="fechaFin" class="form-label">Fecha de Salida <span class="text-danger">*</span></label>
            <input type="date" class="form-control <?= !empty($errors['fechaFin']) ? 'is-invalid' : '' ?>" 
                   id="fechaFin" name="fechaFin" value="<?= htmlspecialchars($datos['fechaFin'] ?? '') ?>" required>
            <?php if (!empty($errors['fechaFin'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['fechaFin']) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <small class="text-muted">Las fechas deben ser futuras y la salida posterior a la entrada.</small>
      </div>
    </div>
    
    <!-- Información Personal -->
    <div class="card mb-4 shadow-sm border-0">
      <div class="card-header bg-rojo text-white">
        <h5 class="mb-0" style="font-family: var(--font-heading);">
          <i class="fas fa-id-card me-2"></i> Información Personal
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?= !empty($errors['nombre']) ? 'is-invalid' : '' ?>" 
                   id="nombre" name="nombre" value="<?= htmlspecialchars($datos['nombre'] ?? '') ?>" required>
            <?php if (!empty($errors['nombre'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['nombre']) ?></div>
            <?php endif; ?>
          </div>
          <div class="col-md-6 mb-3">
            <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?= !empty($errors['apellido']) ? 'is-invalid' : '' ?>" 
                   id="apellido" name="apellido" value="<?= htmlspecialchars($datos['apellido'] ?? '') ?>" required>
            <?php if (!empty($errors['apellido'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['apellido']) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
          <div>
            <?php foreach (['DNI', 'Pasaporte', 'Carnet'] as $doc): ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input <?= !empty($errors['tipoDocumento']) ? 'is-invalid' : '' ?>" 
                     type="radio" name="tipoDocumento" id="doc<?= $doc ?>" value="<?= $doc ?>"
                     <?= (isset($datos['tipoDocumento']) && $datos['tipoDocumento'] === $doc) ? 'checked' : '' ?>>
              <label class="form-check-label" for="doc<?= $doc ?>"><?= $doc ?></label>
            </div>
            <?php endforeach; ?>
          </div>
          <?php if (!empty($errors['tipoDocumento'])): ?>
            <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['tipoDocumento']) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Contacto -->
    <div class="card mb-4 shadow-sm border-0">
      <div class="card-header bg-mostaza text-dark">
        <h5 class="mb-0" style="font-family: var(--font-heading);">
          <i class="fas fa-envelope me-2"></i> Contacto y Procedencia
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" 
                   id="email" name="email" value="<?= htmlspecialchars($datos['email'] ?? '') ?>" required>
            <?php if (!empty($errors['email'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
          </div>
          <div class="col-md-6 mb-3">
            <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
            <input type="tel" class="form-control <?= !empty($errors['telefono']) ? 'is-invalid' : '' ?>" 
                   id="telefono" name="telefono" value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>" required>
            <?php if (!empty($errors['telefono'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($errors['telefono']) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="mb-3">
          <label for="procedencia" class="form-label">Procedencia (País o Ciudad) <span class="text-danger">*</span></label>
          <input type="text" class="form-control <?= !empty($errors['procedencia']) ? 'is-invalid' : '' ?>" 
                 id="procedencia" name="procedencia" value="<?= htmlspecialchars($datos['procedencia'] ?? '') ?>" required>
          <?php if (!empty($errors['procedencia'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['procedencia']) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Preferencias -->
    <div class="card mb-4 shadow-sm border-0">
      <div class="card-header bg-gris-oscuro text-white">
        <h5 class="mb-0" style="font-family: var(--font-heading);">
          <i class="fas fa-utensils me-2"></i> Preferencias
        </h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label for="motivoVisita" class="form-label">Motivo de la Visita</label>
          <select class="form-select" id="motivoVisita" name="motivoVisita">
            <option value="">Selecciona...</option>
            <option value="Turismo" <?= (isset($datos['motivoVisita']) && $datos['motivoVisita'] === 'Turismo') ? 'selected' : '' ?>>Turismo</option>
            <option value="Trabajo" <?= (isset($datos['motivoVisita']) && $datos['motivoVisita'] === 'Trabajo') ? 'selected' : '' ?>>Trabajo</option>
            <option value="Evento" <?= (isset($datos['motivoVisita']) && $datos['motivoVisita'] === 'Evento') ? 'selected' : '' ?>>Evento</option>
            <option value="Otros" <?= (isset($datos['motivoVisita']) && $datos['motivoVisita'] === 'Otros') ? 'selected' : '' ?>>Otros</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="preferenciaAlimentaria" class="form-label">Preferencias Alimentarias (alergias, dietas, etc.)</label>
          <textarea class="form-control" id="preferenciaAlimentaria" name="preferenciaAlimentaria" rows="2"><?= htmlspecialchars($datos['preferenciaAlimentaria'] ?? '') ?></textarea>
          <small class="form-text text-muted">Ej: vegetariano, sin gluten, alergia al maní, etc.</small>
        </div>
      </div>
    </div>

    <!-- Botones -->
    <div class="d-flex justify-content-between flex-wrap gap-2">
      <a href="packages.php" class="btn btn-outline-secondary px-4 py-2">
        <i class="fas fa-arrow-left me-1"></i> Volver a Paquetes
      </a>
      <button type="submit" class="btn btn-rojo px-4 py-2">
        <i class="fas fa-paper-plane me-1"></i> Enviar Solicitud de Reserva
      </button>
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

<!-- Validación JS -->
<script>
  (function () {
    'use strict';
    const form = document.querySelector('form');
    if (form) {
      form.addEventListener('submit', function (event) {
        // Verificar manualmente
        let valid = true;

        // Campos obligatorios
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
          if (!field.value.trim()) {
            field.classList.add('is-invalid');
            valid = false;
          } else {
            field.classList.remove('is-invalid');
          }
        });

        // Validar email
        const email = form.querySelector('[name="email"]');
        if (email && email.value && !/^\S+@\S+\.\S+$/.test(email.value)) {
          email.classList.add('is-invalid');
          valid = false;
        } else if (email) {
          email.classList.remove('is-invalid');
        }

        // Validar fechas
        const fechaInicio = form.querySelector('[name="fechaInicio"]');
        const fechaFin = form.querySelector('[name="fechaFin"]');
        if (fechaInicio && fechaFin) {
          if (fechaInicio.value && fechaFin.value && fechaFin.value <= fechaInicio.value) {
            fechaFin.classList.add('is-invalid');
            valid = false;
          } else {
            fechaFin.classList.remove('is-invalid');
          }
        }

        if (!valid) {
          event.preventDefault();
          event.stopPropagation();
        }
      });

      // Quitar rojo al escribir
      form.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('input', function() {
          this.classList.remove('is-invalid');
        });
      });
    }
  })();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>