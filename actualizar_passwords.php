<?php
// archivo: /hotel-yokoso/actualizar_passwords.php
require_once __DIR__ . '../config/database.php';

// Nuevas contraseñas (texto plano)
$nuevaPassAdmin = 'adminM123!';
$nuevaPassRecep = 'adminN123!';

// Hashearlas
$hashAdmin = password_hash($nuevaPassAdmin, PASSWORD_DEFAULT);
$hashRecep = password_hash($nuevaPassRecep, PASSWORD_DEFAULT);

// Actualizar en BD
$pdo->prepare("UPDATE Usuario SET contrasena = ? WHERE email = 'admin@yokoso.com'")->execute([$hashAdmin]);
$pdo->prepare("UPDATE Usuario SET contrasena = ? WHERE email = 'recepcion@yokoso.com'")->execute([$hashRecep]);

echo "<h2>✅ Contraseñas actualizadas correctamente.</h2>";
echo "<p>Ya puedes iniciar sesión con:</p>";
echo "<ul>";
echo "<li><strong>Admin:</strong> admin@yokoso.com / adminM123!</li>";
echo "<li><strong>Recepcionista:</strong> recepcion@yokoso.com / adminN123!</li>";
echo "</ul>";
echo "<p style='color: red;'>⚠️ ¡Elimina este archivo ahora!</p>";
?>