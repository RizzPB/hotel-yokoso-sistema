<?php
$title = "Iniciar Sesión - Hotel Yokoso";
$extra_css = "assets/css/auth.css"; 
$body_class = "login-bg";
ob_start();
?>

<div class="auth-container">
    <a href="/" class="back-to-home" style="display:inline-block; margin-bottom:10px; color:#C8102E; text-decoration:none;">← Volver al inicio</a>
    <img src="/assets/img/empresaLogoYokoso.png" alt="Hotel Yokoso Logo" />
    <h2>Iniciar Sesión</h2>

    <?php if ($error ?? false): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($bloqueado ?? false): ?>
        <div class="locked">Cuenta bloqueada temporalmente.</div>
    <?php else: ?>
        <form method="POST">
            <div class="input-group">
                <label>Usuario o correo electrónico:</label>
                <input type="text" name="usuario" required />
            </div>
            <div class="input-group password-container">
                <label>Contraseña:</label>
                <input type="password" name="password" required />
                <span class="toggle-password" onclick="togglePassword()">👁</span>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
    <?php endif; ?>

    <div class="links">
        <p><a href="/recuperar.php">¿Olvidaste tu contraseña?</a></p>
        <p>¿No tienes cuenta? <a href="/registro.php">Regístrate aquí</a></p>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.querySelector('input[name="password"]');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>