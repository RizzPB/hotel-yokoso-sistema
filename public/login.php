<?php
session_start();
require_once '../config/database.php';

// Redirigir si ya está logueado
if (isset($_SESSION['idUsuario'])) {
    header("Location: panel.php");
    exit;
}

$error = '';
$bloqueado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    // Verificar si la cuenta está bloqueada (simulado con sesión por simplicidad)
    if (isset($_SESSION['intentos']) && $_SESSION['intentos'] >= 3) {
        $bloqueado = true;
        $error = "Cuenta bloqueada temporalmente. Intente más tarde.";
    } else {
        // Buscar usuario por nombre o email
        $stmt = $pdo->prepare("SELECT idUsuario, nombreUsuario, contrasena, rol FROM Usuario WHERE nombreUsuario = ? OR email = ?");
        $stmt->execute([$usuario, $usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['contrasena'])) {
            // Login exitoso
            $_SESSION['idUsuario'] = $user['idUsuario'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['login_time'] = time();
            $_SESSION['ultimo_acceso'] = time();

            // Registrar en auditoría
            $stmt = $pdo->prepare("INSERT INTO AuditoriaLogin (idUsuario, fechaHora) VALUES (?, NOW())");
            $stmt->execute([$user['idUsuario']]);

            header("Location: panel.php");
            exit;
        } else {
            // Fallo de login
            $_SESSION['intentos'] = ($_SESSION['intentos'] ?? 0) + 1;
            if ($_SESSION['intentos'] >= 3) {
                $bloqueado = true;
                $error = "Cuenta bloqueada temporalmente.";
            } else {
                $error = "Usuario o contraseña incorrectos.";
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
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <img src="img/empresaLogoYokoso.png" alt="Hotel Yokoso Logo" />
        <h2>Iniciar Sesión</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($bloqueado): ?>
            <div class="locked">Cuenta bloqueada temporalmente.</div>
        <?php else: ?>
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
        <?php endif; ?>

        <div class="links">
            <p><a href="recuperar.php">¿Olvidaste tu contraseña?</a></p>
            <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
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
