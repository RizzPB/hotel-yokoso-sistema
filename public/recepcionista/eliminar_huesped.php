<?php
// public/recepcionista/eliminar_huesped.php

define('ACCESO_PERMITIDO', true);
session_start();

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$idHuesped = $_GET['id'] ?? null;

if (!$idHuesped || !is_numeric($idHuesped)) {
    $_SESSION['mensaje_error'] = "Huésped no válido.";
    header("Location: ver_huespedes.php");
    exit;
}

try {
    // 🔒 En lugar de eliminar, desactivamos (mejor práctica)
    $stmt = $pdo->prepare("UPDATE Huesped SET activo = 0 WHERE idHuesped = ?");
    $resultado = $stmt->execute([$idHuesped]);

    if ($resultado) {
        $_SESSION['mensaje_exito'] = "Huésped desactivado correctamente.";
    } else {
        $_SESSION['mensaje_error'] = "No se pudo desactivar al huésped.";
    }
} catch (Exception $e) {
    $_SESSION['mensaje_error'] = "Error al desactivar el huésped.";
}

header("Location: ver_huespedes.php");
exit;
?>