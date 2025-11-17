<?php
$title = "Recuperar Contraseña - Hotel Yokoso";
$extra_css = "/assets/css/auth.css";
$body_class = "recuperar-bg";
ob_start();
?>

<div class="auth-wrapper">
  <!-- Navbar -->
  <nav class="auth-navbar navbar navbar-dark" style="background-color: var(--color-rojo-quemado);">
    <div class="container d-flex justify-content-between align-items-center">
      <a class="navbar-brand d-flex align-items-center text-white text-decoration-none" href="/">
        <img src="/assets/img/empresaLogoYokoso.png" alt="Hotel Yokoso" width="36" class="me-2">
        <span class="fw-bold">Hotel Yokoso</span>
      </a>
      <a href="/" class="text-white text-decoration-none">
        <i class="fas fa-home me-1"></i> Inicio
      </a>
    </div>
  </nav>

  <div class="auth-container">
    <a href="/" class="back-to-home">← Volver al inicio</a>
    <img src="/assets/img/logoYOKOSO2.png" alt="Hotel Yokoso Logo" />
    <h2>Recuperar Contraseña</h2>
    <p>Ingresa tu correo para restablecer tu contraseña.</p>

    <?php if ($mensaje ?? false): ?>
        <div class="success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label for="correo">Correo Electrónico</label>
            <input type="email" id="correo" name="correo" required />
        </div>
        <button type="submit">Enviar Enlace</button>
    </form>

    <div class="links">
        ¿Recordaste tu contraseña? <a href="/login.php">Inicia sesión</a>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>