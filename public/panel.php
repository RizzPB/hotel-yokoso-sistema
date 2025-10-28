<?php
session_start();
// Verificar si está logueado
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel - Hotel Yokoso</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 30px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .logout { margin-top: 20px; }
        .logout a { color: white; background: #C8102E; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏨 Panel de Usuario - Hotel Yokoso</h1>
        <p>¡Hola! Has iniciado sesión correctamente.</p>
        <p><strong>Rol:</strong> <?= htmlspecialchars($_SESSION['rol'] ?? 'huésped') ?></p>
        <p>Este es tu panel. Aquí irán las funcionalidades del sistema (reservas, reportes, etc.).</p>
        
        <div class="logout">
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>
</body>
</html>