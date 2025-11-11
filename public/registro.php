<?php
require_once '../config/database.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $confirmar = $_POST['confirmar'];

    // Validaciones
    if (empty($usuario) || empty($correo) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($password !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[@$!%*?&]/', $password)) {
        $error = "La contraseña debe tener al menos 8 caracteres, mayúsculas, minúsculas, números y símbolos.";
    } else {
        // Verificar si ya existe
        $stmt = $pdo->prepare("SELECT idUsuario FROM Usuario WHERE nombreUsuario = ? OR email = ?");
        $stmt->execute([$usuario, $correo]);
        if ($stmt->fetch()) {
            $error = "El usuario o correo ya está registrado.";
        } else {
            // Registrar
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO Usuario (nombreUsuario, contrasena, rol, email) VALUES (?, ?, 'huésped', ?)");
            if ($stmt->execute([$usuario, $hash, $correo])) {
                $success = "Registro exitoso. ¡Bienvenido a Hotel Yokoso!";
            } else {
                $error = "Error al registrar. Intente más tarde.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Hotel Yokoso</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <img src="assets/img/empresaLogoYokoso.png" alt="Hotel Yokoso Logo" />
        <h2>Registro de Usuario</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
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
            ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
        </div>
    </div>
</body>
</html>