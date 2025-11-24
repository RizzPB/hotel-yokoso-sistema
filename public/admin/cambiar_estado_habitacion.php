<?php
// public/admin/cambiar_estado_habitacion.php
define('ACCESO_PERMITIDO', true);
session_start();
if ($_SESSION['rol'] !== 'admin') exit;

require_once __DIR__ . '/../../config/database.php';

$idHab = intval($_POST['idHabitacion'] ?? 0);
$estado = $_POST['estado'] ?? '';

if ($idHab <= 0 || !in_array($estado, ['disponible', 'ocupada', 'mantenimiento', 'limpieza'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Actualizar estado
    $pdo->prepare("UPDATE Habitacion SET estado = ? WHERE idHabitacion = ?")
        ->execute([$estado, $idHab]);

    // Si es mantenimiento → crear registro
    if ($estado === 'mantenimiento') {
        $existe = $pdo->prepare("SELECT 1 FROM Mantenimiento WHERE idHabitacion = ? AND fechaFin IS NULL");
        $existe->execute([$idHab]);
        if (!$existe->fetch()) {
            $pdo->prepare("
                INSERT INTO Mantenimiento (idHabitacion, fechaInicio, motivo, estado)
                VALUES (?, CURDATE(), 'Mantenimiento por administrador', 'en_curso')
            ")->execute([$idHab]);
        }
    }

    // Si sale de mantenimiento → cerrar registro
    if ($estado !== 'mantenimiento') {
        $pdo->prepare("
            UPDATE Mantenimiento 
            SET fechaFin = CURDATE(), estado = 'finalizado'
            WHERE idHabitacion = ? AND fechaFin IS NULL
        ")->execute([$idHab]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'OK']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;