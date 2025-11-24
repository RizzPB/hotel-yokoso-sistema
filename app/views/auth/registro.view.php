<?php
$title = "Registro - Hotel Yokoso";
$extra_css = "/assets/css/auth.css"; 
$body_class = "registro-bg";
ob_start();
?>

<!-- NAVBAR FIJO IGUAL QUE LA LANDING -->
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

<!-- FORMULARIO DE REGISTRO PERFECTO -->
<div class="d-flex align-items-center justify-content-center min-vh-100" style="padding-top: 80px;">
    <div style="max-width: 440px; width: 92%; background: rgba(255,255,255,0.97); border-radius: 28px; padding: 3rem 2.3rem; box-shadow: 0 20px 50px rgba(0,0,0,0.3);">
        
        <!-- LOGO CENTRADO -->
        <div class="text-center mb-4">
            <img src="/assets/img/logoSistemaYokoso.png" alt="YokosoStay" style="height: 85px; width: auto;">
        </div>

        <!-- TÍTULO -->
        <h2 class="text-center mb-5" style="color: var(--color-rojo); font-family: 'Playfair Display', serif; font-size: 2.1rem; font-weight: 600;">
            Registro de Usuario
        </h2>

        <!-- MENSAJES -->
        <?php if ($error ?? false): ?>
            <div class="text-danger text-center mb-3 fw-semibold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success ?? false): ?>
            <div class="text-success text-center mb-3 fw-bold"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- FORMULARIO -->
        <form method="POST">
            <!-- NOMBRE DE USUARIO -->
            <div class="mb-4">
                <div style="color: #555; margin-bottom: 8px; font-size: 0.95rem;">Nombre de Usuario</div>
                <input type="text" name="usuario" id="usuario" class="form-control form-control-lg" 
                       style="border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #ddd;" 
                       value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>" required />
            </div>

            <!-- CORREO -->
            <div class="mb-4">
                <div style="color: #555; margin-bottom: 8px; font-size: 0.95rem;">Correo Electrónico</div>
                <input type="email" name="correo" id="correo" class="form-control form-control-lg" 
                       style="border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #ddd;" 
                       value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required />
            </div>

            <!-- CONTRASEÑA -->
            <div class="mb-3">
                <div style="color: #555; margin-bottom: 8px; font-size: 0.95rem;">Contraseña</div>
                <div class="position-relative">
                    <input type="password" name="password" id="password" class="form-control form-control-lg" 
                           style="border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #ddd; padding-right: 60px;" required />
                    <span onclick="togglePass('password', this)" 
                          style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--color-rojo); font-weight: 600;">
                        Ver
                    </span>
                </div>
                <small style="color: #888; font-size: 0.8rem; display: block; margin-top: 6px;">
                    Mínimo 8 caracteres, con mayúsculas, minúsculas, números y símbolos.
                </small>
            </div>

            <!-- CONFIRMAR CONTRASEÑA -->
            <div class="mb-4">
                <div style="color: #555; margin-bottom: 8px; font-size: 0.95rem;">Confirmar Contraseña</div>
                <div class="position-relative">
                    <input type="password" name="confirmar" id="confirmar" class="form-control form-control-lg" 
                           style="border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #ddd; padding-right: 60px;" required />
                    <span onclick="togglePass('confirmar', this)" 
                          style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--color-rojo); font-weight: 600;">
                        Ver
                    </span>
                </div>
            </div>

            <!-- BOTÓN REGISTRARSE -->
            <button type="submit" class="btn btn-danger w-100 fw-bold" 
                    style="border-radius: 50px; padding: 0.9rem; font-size: 1.1rem; background: var(--color-rojo); border: none;">
                Registrarse
            </button>
        </form>

        <!-- ENLACE LOGIN -->
        <div class="text-center mt-4">
            <p style="color: #555; font-size: 0.95rem;">
                ¿Ya tienes una cuenta? 
                <a href="/login.php" style="color: var(--color-rojo); font-weight: 600; text-decoration: none;">Inicia sesión aquí</a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePass(id, span) {
    const input = document.getElementById(id);
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