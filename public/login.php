<?php
session_start();
require_once '../config/database.php';

// Redirigir si ya está logueado
if (isset($_SESSION['idUsuario'])) {
    // Redirigir según rol (más adelante lo ajustamos)
    header("Location: panel.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    // 1. Buscar usuario por nombre o email
    $stmt = $pdo->prepare("SELECT idUsuario, nombreUsuario, contrasena, rol, bloqueadoHasta, intentosFallidos FROM Usuario WHERE nombreUsuario = ? OR email = ?");
    $stmt->execute([$usuario, $usuario]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Usuario o contraseña incorrectos.";
    } else {
        // 2. Verificar si está bloqueado
        $bloqueadoHasta = $user['bloqueadoHasta'];
        if ($bloqueadoHasta && new DateTime() < new DateTime($bloqueadoHasta)) {
            $error = "Cuenta bloqueada temporalmente. Inténtalo más tarde.";
        } else {
            // 3. Verificar contraseña
            if (password_verify($password, $user['contrasena'])) {
                // ✅ Login exitoso → resetear intentos y bloqueo
                $resetStmt = $pdo->prepare("UPDATE Usuario SET intentosFallidos = 0, bloqueadoHasta = NULL WHERE idUsuario = ?");
                $resetStmt->execute([$user['idUsuario']]);

                // Iniciar sesión
                $_SESSION['idUsuario'] = $user['idUsuario'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['login_time'] = time();

                // Auditoría
                $auditStmt = $pdo->prepare("INSERT INTO AuditoriaLogin (idUsuario, fechaHora) VALUES (?, NOW())");
                $auditStmt->execute([$user['idUsuario']]);

                header("Location: panel.php");
                exit;
            } else {
                // ❌ Contraseña incorrecta → incrementar intentos
                $nuevosIntentos = $user['intentosFallidos'] + 1;
                $bloquear = ($nuevosIntentos >= 3);

                if ($bloquear) {
                    // Bloquear por 15 minutos
                    $bloqueadoHasta = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $updateStmt = $pdo->prepare("UPDATE Usuario SET intentosFallidos = ?, bloqueadoHasta = ? WHERE idUsuario = ?");
                    $updateStmt->execute([$nuevosIntentos, $bloqueadoHasta, $user['idUsuario']]);
                    $error = "Demasiados intentos fallidos. Cuenta bloqueada por 15 minutos.";
                } else {
                    // Solo aumentar intentos
                    $updateStmt = $pdo->prepare("UPDATE Usuario SET intentosFallidos = ? WHERE idUsuario = ?");
                    $updateStmt->execute([$nuevosIntentos, $user['idUsuario']]);
                    $error = "Usuario o contraseña incorrectos.";
                }
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
    <title>Iniciar Sesión - Hotel Yokoso</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <img src="assets/img/empresaLogoYokoso.png" alt="Hotel Yokoso Logo" />
        <h2>Iniciar Sesión</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label for="usuario">Usuario o correo electrónico:</label>
                <input type="text" id="usuario" name="usuario" required />
            </div>

            <div class="input-group password-container">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required />
                <span class="toggle-password" onclick="togglePassword()">👁</span>
            </div>

            <button type="submit">Iniciar Sesión</button>
        </form>

        <div class="links">
            <p><a href="recuperar.php">¿Olvidaste tu contraseña?</a></p>
            <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
        </div>
        <div class="m-2">
            <a href="index.php" class="back-to-home" style="display:inline-block; margin-bottom:10px; color:#C8102E; text-decoration:none;">
                ← Volver al inicio
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>