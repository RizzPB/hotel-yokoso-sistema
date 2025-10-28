<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Yokoso</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Iniciar Sesión</h2>
    <form action="../app/controllers/AuthController.php" method="POST">
        <input type="text" name="username" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>