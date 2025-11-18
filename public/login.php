<?php
session_start();
require_once '../config/database.php';

// Si ya está logeado, redirigir según su ROL EN SESIÓN
if (isset($_SESSION['idUsuario'])) {
    switch ($_SESSION['rol']) { 
        case 'admin':
            header('Location: ../admin/panel_admin.php');
            exit;
        case 'empleado': 
            header('Location: ../receptionista/panel_recepcionista.php');
            exit;
        case 'huésped':
        default:
            header('Location: ../guest/dashboard.php');
            exit;
    }
}

$error = '';
$bloqueado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT idUsuario, nombreUsuario, contrasena, rol, bloqueadoHasta, intentosFallidos FROM Usuario WHERE (nombreUsuario = ? OR email = ?) AND activo = 1");
    $stmt->execute([$usuario, $usuario]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Usuario o contraseña incorrectos.";
    } else {
        $bloqueadoHasta = $user['bloqueadoHasta'];

        // ssi el bloqueo YA EXPIRÓ, resetearlo
        if ($bloqueadoHasta && new DateTime() >= new DateTime($bloqueadoHasta)) {
            $pdo->prepare("UPDATE Usuario SET intentosFallidos = 0, bloqueadoHasta = NULL WHERE idUsuario = ?")
                ->execute([$user['idUsuario']]);
            $bloqueadoHasta = null;
        }

        // Verificar si aún está bloqueado
        if ($bloqueadoHasta && new DateTime() < new DateTime($bloqueadoHasta)) {
            $error = "Cuenta bloqueada temporalmente. Inténtalo más tarde.";
            $bloqueado = true;
        } else {
            if (password_verify($password, $user['contrasena'])) {
                // Resetear intentos tras login exitoso
                $pdo->prepare("UPDATE Usuario SET intentosFallidos = 0, bloqueadoHasta = NULL WHERE idUsuario = ?")
                    ->execute([$user['idUsuario']]);

                $_SESSION['idUsuario'] = $user['idUsuario'];
                $_SESSION['rol'] = $user['rol'];

                $pdo->prepare("INSERT INTO AuditoriaLogin (idUsuario, fechaHora) VALUES (?, NOW())")
                    ->execute([$user['idUsuario']]);

                switch ($user['rol']) {
                    case 'admin':
                        header('Location: ../admin/dashboard.php');
                        break;
                    case 'empleado':
                        header('Location: ../receptionist/checkin.php');
                        break;
                    case 'huésped':
                    default:
                        header('Location: ../guest/dashboard.php');
                        break;
                }
                exit;
            } else {
                // Incrementar intentos
                $nuevosIntentos = $user['intentosFallidos'] + 1;
                if ($nuevosIntentos >= 3) {
                    $bloqueadoHasta = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $pdo->prepare("UPDATE Usuario SET intentosFallidos = ?, bloqueadoHasta = ? WHERE idUsuario = ?")
                        ->execute([$nuevosIntentos, $bloqueadoHasta, $user['idUsuario']]);
                    $error = "Demasiados intentos fallidos. Cuenta bloqueada por 15 minutos.";
                    $bloqueado = true;
                } else {
                    $pdo->prepare("UPDATE Usuario SET intentosFallidos = ? WHERE idUsuario = ?")
                        ->execute([$nuevosIntentos, $user['idUsuario']]);
                    $error = "Usuario o contraseña incorrectos.";
                }
            }
        }
    }
}

include '../app/views/auth/login.view.php';
?>