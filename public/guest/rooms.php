<?php
session_start();
require_once '../../config/database.php';

// Proteger ruta: solo huéspedes
if (!isset($_SESSION['idUsuario']) || ($_SESSION['rol'] ?? '') !== 'huésped') {
    header('Location: ../login.php');
    exit;
}

// Obtener habitaciones disponibles
$stmt = $pdo->prepare("
    SELECT idHabitacion, numero, tipo, precioNoche, foto 
    FROM Habitacion 
    WHERE estado = 'disponible'
    ORDER BY tipo, numero
");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por tipo
$habitacionesPorTipo = [
    'simple' => [],
    'doble' => [],
    'suite' => []
];

foreach ($habitaciones as $h) {
    if (isset($habitacionesPorTipo[$h['tipo']])) {
        $habitacionesPorTipo[$h['tipo']][] = $h;
    }
}

include '../../app/views/guest/rooms.view.php';
?>