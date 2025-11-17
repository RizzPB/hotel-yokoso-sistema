<?php
$title = "Registro - Hotel Yokoso";
$extra_css = "/assets/css/auth.css"; 
$body_class = "registro-bg";
ob_start();
?>

<!-- Navbar -->
<nav class="navbar navbar-dark" style="background-color: var(--color-rojo-quemado); padding: 0.8rem 1rem;">
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
    <a href="/" class="back-to-home" style="display:inline-block; margin-bottom:10px; color:#C8102E; text-decoration:none;">← Volver al inicio</a>
    <img src="/assets/img/empresaLogoYokoso.png" alt="Hotel Yokoso Logo" />
    <h2>Registro de Usuario</h2>

    <?php if ($error ?? false): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success ?? false): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label for="usuario">Nombre de Usuario</label>
            <input type="text" id="usuario" name="usuario" required />
        </div>

        <div class="input-group">
            <label for="correo">Correo Electrónico</label>
            <input type="email" id="correo" name="correo" required />
        </div>

        <div class="input-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required />
            <div class="password-hint" style="font-size:12px;color:#666;margin-top:3px;">
                Mínimo 8 caracteres, con mayúsculas, minúsculas, números y símbolos.
            </div>
        </div>

        <div class="input-group">
            <label for="confirmar">Confirmar Contraseña</label>
            <input type="password" id="confirmar" name="confirmar" required />
        </div>

        <button type="submit">Registrarse</button>
    </form>

    <div class="links">
        ¿Ya tienes una cuenta? <a href="/login.php">Inicia sesión aquí</a>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>