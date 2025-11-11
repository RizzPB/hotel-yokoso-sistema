<?php
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = "Se ha enviado un enlace de recuperación a tu correo.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Hotel Yokoso</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <img src="assets/img/logoYOKOSO2.png" alt="Hotel Yokoso Logo" />
        <h2>Recuperar Contraseña</h2>
        <p>Ingresa tu correo para restablecer tu contraseña.</p>

        <?php if ($mensaje): ?>
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
            ¿Recordaste tu contraseña? <a href="login.php">Inicia sesión</a>
        </div>
    </div>
</body>
</html>