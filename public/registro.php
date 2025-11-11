<?php
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $confirmar = $_POST['confirmar'];

    if (empty($usuario) || empty($correo) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($password !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[@$!%*?&]/', $password)) {
        $error = "La contraseña debe tener al menos 8 caracteres, mayúsculas, minúsculas, números y símbolos.";
    } else {
        $stmt = $pdo->prepare("SELECT idUsuario FROM Usuario WHERE nombreUsuario = ? OR email = ?");
        $stmt->execute([$usuario, $correo]);
        if ($stmt->fetch()) {
            $error = "El usuario o correo ya está registrado.";
        } else {
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

// Pasar variables a la vista
include '../app/views/auth/registro.view.php';
?>