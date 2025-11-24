<?php
$title = "Iniciar Sesión - Hotel Yokoso";
$extra_css = "/assets/css/auth.css"; 
$body_class = "login-bg";
ob_start();
?>

<!-- NAVBAR FIJO -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--color-rojo-quemado); z-index: 1050;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="/assets/img/empresaLogoYokoso.png" alt="Logo Hotel Yokoso" class="logo-navbar">
            <span class="fw-bold ms-2">Hotel Yokoso</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item"><a class="nav-link text-white" href="/#bienvenida">Inicio</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="/#habitaciones">Habitaciones</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="/#paquetes">Paquetes</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="/#contacto">Contacto</a></li>
                <li class="nav-item ms-2"><a href="/login.php" class="btn btn-outline-light btn-sm">Iniciar Sesión</a></li>
                <li class="nav-item ms-2"><a href="/registro.php" class="btn btn-warning btn-sm text-dark">Regístrate</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- FORMULARIO CENTRADO Y PERFECTO -->
<div class="d-flex align-items-center justify-content-center min-vh-100" style="padding-top: 80px;">
    <div style="max-width: 420px; width: 92%; background: rgba(255,255,255,0.97); border-radius: 28px; padding: 2.8rem 2.2rem; box-shadow: 0 20px 50px rgba(0,0,0,0.3);">
        
        <!-- LOGO -->
        <div class="text-center mb-4">
            <img src="/assets/img/logoSistemaYokoso.png" alt="YokosoStay" style="height: 85px; width: auto;">
        </div>

        <!-- TÍTULO -->
        <h2 class="text-center mb-5" style="color: var(--color-rojo); font-family: 'Playfair Display', serif; font-size: 2.1rem; font-weight: 600;">
            Iniciar Sesión
        </h2>

        <?php if ($error ?? false): ?>
            <div class="text-danger text-center mb-3 fw-semibold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($bloqueado ?? false): ?>
            <div class="text-danger text-center mb-3 fw-semibold">Cuenta bloqueada temporalmente.</div>
        <?php else: ?>
            <form method="POST">
                <!-- EMAIL -->
                <div class="mb-4">
                    <div style="color: #555; margin-bottom: 8px; font-size: 0.95rem;">Usuario o correo electrónico:</div>
                    <input type="text" name="usuario" class="form-control form-control-lg" 
                           style="border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #ddd;" 
                           placeholder="admin@yokoso.com" required />
                </div>

                <!-- CONTRASEÑA -->
                <div class="mb-4">
                    <div style="color: #555; margin-bottom: 8px; font-size: 0.95rem;">Contraseña:</div>
                    <div class="position-relative">
                        <input type="password" name="password" id="passInput" class="form-control form-control-lg" 
                               style="border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #ddd; padding-right: 60px;" required />
                        <span onclick="togglePassword()" 
                              style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #C8102E; font-weight: 600;">
                            Ver
                        </span>
                    </div>
                </div>

                <!-- BOTÓN -->
                <button type="submit" class="btn btn-danger w-100 fw-bold" 
                        style="border-radius: 50px; padding: 0.9rem; font-size: 1.1rem; background: var(--color-rojo); border: none;">
                    Iniciar Sesión
                </button>
            </form>
        <?php endif; ?>

        <!-- ENLACES -->
        <div class="text-center mt-4">
            <p class="mb-2"><a href="/recuperar.php" style="color: #666; text-decoration: underline; font-size: 0.95rem;">¿Olvidaste tu contraseña?</a></p>
            <p style="color: #555; font-size: 0.95rem;">
                ¿No tienes cuenta? 
                <a href="/registro.php" style="color: var(--color-rojo); font-weight: 600; text-decoration: none;">Regístrate aquí</a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('passInput');
    const span = input.parentElement.querySelector('span');
    if (input.type === 'password') {
        input.type = 'text';
        span.textContent = 'Ocultar';
    } else {
        input.type = 'password';
        span.textContent = 'Ver';
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>