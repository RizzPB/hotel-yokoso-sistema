<?php
session_start();
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

$rol = $_SESSION['rol'];

// Redirigir según el rol
if ($rol === 'empleado') {
    // Si es empleado, ir al panel del recepcionista
    header("Location: vistas/recepcionista/panel_recepcionista.php");
    exit;
} elseif ($rol === 'admin') {
    // Si es admin, ir a su panel (puedes crearlo después)
    header("Location: vistas/admin/panel_admin.php");
    exit;
} else {
    // Si es huésped u otro rol, ir a su panel
    header("Location: vistas/cliente/panel_cliente.php");
    exit;
}
?>