<?php
session_start();
require_once '../config/database.php';

// Si ya está logeado, redirigir
if (isset($_SESSION['idUsuario'])) {
    // Más adelante redirigirá por rol, por ahora al panel
    header("Location: panel.php");
    exit;
}

$error = '';
$bloqueado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT idUsuario, nombreUsuario, contrasena, rol, bloqueadoHasta, intentosFallidos FROM Usuario WHERE nombreUsuario = ? OR email = ?");
    $stmt->execute([$usuario, $usuario]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Usuario o contraseña incorrectos.";
    } else {
        $bloqueadoHasta = $user['bloqueadoHasta'];
        if ($bloqueadoHasta && new DateTime() < new DateTime($bloqueadoHasta)) {
            $error = "Cuenta bloqueada temporalmente. Inténtalo más tarde.";
            $bloqueado = true;
        } else {
            if (password_verify($password, $user['contrasena'])) {
                $resetStmt = $pdo->prepare("UPDATE Usuario SET intentosFallidos = 0, bloqueadoHasta = NULL WHERE idUsuario = ?");
                $resetStmt->execute([$user['idUsuario']]);

                $_SESSION['idUsuario'] = $user['idUsuario'];
                $_SESSION['rol'] = $user['rol'];

                $auditStmt = $pdo->prepare("INSERT INTO AuditoriaLogin (idUsuario, fechaHora) VALUES (?, NOW())");
                $auditStmt->execute([$user['idUsuario']]);

                header("Location: panel.php");
                exit;
            } else {
                $nuevosIntentos = $user['intentosFallidos'] + 1;
                if ($nuevosIntentos >= 3) {
                    $bloqueadoHasta = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $updateStmt = $pdo->prepare("UPDATE Usuario SET intentosFallidos = ?, bloqueadoHasta = ? WHERE idUsuario = ?");
                    $updateStmt->execute([$nuevosIntentos, $bloqueadoHasta, $user['idUsuario']]);
                    $error = "Demasiados intentos fallidos. Cuenta bloqueada por 15 minutos.";
                    $bloqueado = true;
                } else {
                    $updateStmt = $pdo->prepare("UPDATE Usuario SET intentosFallidos = ? WHERE idUsuario = ?");
                    $updateStmt->execute([$nuevosIntentos, $user['idUsuario']]);
                    $error = "Usuario o contraseña incorrectos.";
                }
            }
        }
    }
}

// Pasar variables a la vista
include '../app/views/auth/login.view.php';
?>