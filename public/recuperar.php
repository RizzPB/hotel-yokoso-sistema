<?php
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = "Se ha enviado un enlace de recuperación a tu correo.";
}

// Pasar variables a la vista
include '../app/views/auth/recuperar.view.php';
?>