<?php
session_start();
if (!isset($_SESSION['idUsuario']) || ($_SESSION['rol'] ?? '') !== 'huésped') {
    header('Location: /login.php');
    exit;
}
if (!isset($_SESSION['reserva_id'])) {
    header('Location: rooms.php');
    exit;
}
include '../../app/views/guest/confirmacion.view.php';
?>