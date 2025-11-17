<?php
session_start();
require_once '../../config/database.php';

// Proteger ruta: solo huéspedes
if (!isset($_SESSION['idUsuario']) || ($_SESSION['rol'] ?? '') !== 'huésped') {
    header('Location: /login.php');
    exit;
}

// Obtener habitaciones disponibles (solo las que NO están: ocupadas, mantenimiento, eliminadas)
$stmt = $pdo->prepare("
    SELECT idHabitacion, numero, tipo, precioNoche, foto 
    FROM Habitacion 
    WHERE estado = 'disponible'
    ORDER BY 
        CASE 
            WHEN numero LIKE 'S%' THEN 1
            WHEN numero IN ('5A','5B','10A','10B') THEN 2
            ELSE 3
        END,
        numero
");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separar en dos grupos
$habitacionesSal = array_filter($habitaciones, fn($h) => str_starts_with($h['numero'], 'S'));
$habitacionesNormales = array_filter($habitaciones, fn($h) => !str_starts_with($h['numero'], 'S'));

// Pasar a la vista
include '../../app/views/guest/rooms.view.php';
?>