<?php
// public/admin/buscar_reservas_ajax.php


define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado');
}

require_once __DIR__ . '/../../config/database.php';

$filtroEstado = $_GET['estado'] ?? 'todas';
$buscar = trim($_GET['buscar'] ?? '');

$sql = "
    SELECT r.*, h.nombre, h.apellido, h.nroDocumento,
           GROUP_CONCAT(ha.numero SEPARATOR ', ') AS habitaciones
    FROM Reserva r
    JOIN Huesped h ON r.idHuesped = h.idHuesped
    LEFT JOIN ReservaHabitacion rh ON r.idReserva = rh.idReserva
    LEFT JOIN Habitacion ha ON rh.idHabitacion = ha.idHabitacion
    WHERE 1=1
";

$params = [];

if ($filtroEstado !== 'todas') {
    $sql .= " AND r.estado = ?";
    $params[] = $filtroEstado;
}

if (!empty($buscar)) {
    $sql .= " AND (h.nombre LIKE ? OR h.apellido LIKE ? OR h.nroDocumento LIKE ? OR CAST(r.idReserva AS CHAR) LIKE ?)";
    $like = "%$buscar%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " GROUP BY r.idReserva ORDER BY r.idReserva DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para generar tarjetas (solo con confirmar/rechazar)
function generarTarjetasReservas($reservas) {
    if (empty($reservas)) {
        return '
        <div class="col-12 text-center py-5">
            <i class="fas fa-calendar-times fa-5x text-muted mb-4"></i>
            <h4 class="text-muted">No se encontraron reservas con estos filtros</h4>
        </div>';
    }

    return implode('', array_map(function($r) {
        $badge = match($r['estado']) {
            'pendiente'   => 'warning',
            'confirmada'  => 'success',
            'cancelada'   => 'danger',
            'finalizada'  => 'secondary',
            default       => 'info'
        };

        $acciones = '';
        if ($r['estado'] === 'pendiente') {
            $acciones = '
            <div class="btn-group mt-3" role="group">
                <button type="button" class="btn btn-success btn-sm" 
                        onclick="confirmarReserva('.$r['idReserva'].')">
                    Confirmar
                </button>
                <button type="button" class="btn btn-danger btn-sm" 
                        onclick="rechazarReserva('.$r['idReserva'].')">
                    Rechazar
                </button>
            </div>';
        }

        return '
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow hover-lift border-0 position-relative overflow-hidden">
                <div class="card-header bg-rojo-quemado text-black text-center py-3">
                    <h5 class="mb-0">Reserva #'.$r['idReserva'].'</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-2">
                        '.htmlspecialchars($r['nombre'].' '.$r['apellido']).'
                    </h6>
                    <p class="small text-muted mb-1">
                        <i class="fas fa-id-card"></i> '.htmlspecialchars($r['nroDocumento'] ?? 'Sin documento').'
                    </p>
                    <p class="small text-muted mb-2">
                        <i class="fas fa-bed"></i> Hab: '.($r['habitaciones'] ?: 'Sin asignar').'
                    </p>
                    <p class="small mb-3">
                        <i class="fas fa-calendar-alt text-rojo"></i>
                        '.date('d/m/Y', strtotime($r['fechaInicio'])).' → '.date('d/m/Y', strtotime($r['fechaFin'])).'
                    </p>
                    <div class="d-flex justify-content-between align-items-end">
                        <h4 class="text-success fw-bold mb-0">Bs. '.number_format($r['total'], 2).'</h4>
                        <span class="badge bg-'.$badge.' fs-6 px-3 py-2">'.ucfirst($r['estado']).'</span>
                    </div>
                    '.$acciones.'
                </div>
                <div class="card-footer bg-light text-center">
                    <a href="editar_reserva_admin.php?id='.$r['idReserva'].'" 
                       class="btn btn-yokoso btn-sm w-100 rounded-pill">
                        Gestionar
                    </a>
                </div>
            </div>
        </div>';
    }, $reservas));
}

echo generarTarjetasReservas($reservas);
?>