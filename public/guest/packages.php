<?php
session_start();
require_once '../../config/database.php';

// Proteger: solo huéspedes logeados
if (!isset($_SESSION['idUsuario']) || ($_SESSION['rol'] ?? '') !== 'huésped') {
    header('Location: /login.php');
    exit;
}

// Obtener habitaciones seleccionadas (de rooms.php)
$habitacionesSeleccionadas = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['habitaciones_seleccionadas'])) {
    $habitacionesSeleccionadas = json_decode($_POST['habitaciones_seleccionadas'], true);
    $_SESSION['habitaciones_seleccionadas'] = $habitacionesSeleccionadas;
} elseif (!empty($_SESSION['habitaciones_seleccionadas'])) {
    $habitacionesSeleccionadas = $_SESSION['habitaciones_seleccionadas'];
} else {
    // Si no hay selección, redirigir a rooms.php
    header('Location: rooms.php');
    exit;
}

// Obtener paquetes activos
$stmt = $pdo->prepare("SELECT idPaquete, nombre, descripcion, precio, duracionDias FROM PaqueteTuristico WHERE activo = 1 ORDER BY duracionDias");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../app/views/guest/packages.view.php';
?>