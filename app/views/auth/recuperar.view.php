<?php
$title = "Recuperar Contraseña - Hotel Yokoso";
$extra_css = "/assets/css/auth.css"; 
$body_class = "recuperar-bg";
ob_start();
?>

<div class="auth-container">
    <a href="/" class="back-to-home" style="display:inline-block; margin-bottom:10px; color:#C8102E; text-decoration:none;">← Volver al inicio</a>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>