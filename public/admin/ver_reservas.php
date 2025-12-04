<?php
// public/admin/ver_reservas.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'reservas';
require_once __DIR__ . '/../../config/database.php';

// ✅ Procesar acciones de confirmar/rechazar (sin archivo aparte)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $idReserva = $_POST['idReserva'] ?? null;
    $accion = $_POST['accion'] ?? null;

    if ($idReserva && in_array($accion, ['confirmar', 'rechazar'])) {
        try {
            // Verificar que la reserva exista y esté pendiente
            $stmt = $pdo->prepare("SELECT estado FROM Reserva WHERE idReserva = ? AND estado = 'pendiente'");
            $stmt->execute([$idReserva]);
            if ($stmt->fetch()) {
                $nuevoEstado = ($accion === 'confirmar') ? 'confirmada' : 'cancelada';
                $stmt = $pdo->prepare("UPDATE Reserva SET estado = ? WHERE idReserva = ?");
                $stmt->execute([$nuevoEstado, $idReserva]);
                $_SESSION['mensaje_exito'] = "Reserva #$idReserva " . ($accion === 'confirmar' ? 'confirmada' : 'rechazada') . " exitosamente.";
            } else {
                $_SESSION['mensaje_error'] = "La reserva no existe o ya fue procesada.";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje_error'] = "Error al procesar la acción.";
        }
        header("Location: ver_reservas.php");
        exit;
    }
}

// Mensajes de sesión
$mensaje_exito = $_SESSION['mensaje_exito'] ?? null;
$mensaje_error = $_SESSION['mensaje_error'] ?? null;
if ($mensaje_exito) unset($_SESSION['mensaje_exito']);
if ($mensaje_error) unset($_SESSION['mensaje_error']);

// Filtros
$filtroEstado = $_GET['estado'] ?? 'todas';
$buscar = trim($_GET['buscar'] ?? '');

// Consulta principal
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
    $params = array_merge($params, [$like, $like, $like, $like]);
}
$sql .= " GROUP BY r.idReserva ORDER BY r.idReserva DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Reservas - Panel Administrador";
?>

<!-- ✅ Script para modal de confirmación -->
<script>
function abrirModal(accion, idReserva) {
    // Establecer los datos en el formulario oculto del modal
    document.getElementById('modalFormAccion').value = accion;
    document.getElementById('modalFormId').value = idReserva;
    
    // Actualizar el texto del modal
    const titulo = document.getElementById('modalTitulo');
    const cuerpo = document.getElementById('modalCuerpo');
    const btn = document.getElementById('modalSubmitBtn');
    
    if (accion === 'confirmar') {
        titulo.textContent = 'Confirmar reserva';
        cuerpo.innerHTML = '¿Estás seguro de que deseas <strong>confirmar</strong> la reserva #' + idReserva + '?';
        btn.className = 'btn btn-success';
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Confirmar';
    } else {
        titulo.textContent = 'Rechazar reserva';
        cuerpo.innerHTML = '¿Estás seguro de que deseas <strong>rechazar</strong> la reserva #' + idReserva + '?<br>Esta acción no se puede deshacer.';
        btn.className = 'btn btn-danger';
        btn.innerHTML = '<i class="fas fa-times me-1"></i>Rechazar';
    }
    
    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
    modal.show();
}
</script>

<?php
$contenido_principal = '
<div class="container py-5">

    <!-- ✅ Mensajes de éxito/error -->
    ' . ($mensaje_exito ? '<div class="alert alert-success alert-dismissible fade show text-center" role="alert">
        <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($mensaje_exito) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>' : '') . '

    ' . ($mensaje_error ? '<div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($mensaje_error) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>' : '') . '

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5 gap-3">
        <h2 class="text-rojo fw-bold mb-0">
            Gestión de Reservas
        </h2>
    </div>

    <!-- FILTROS Y BUSCADOR -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Filtrar por estado</label>
                    <select name="estado" class="form-select form-select-lg rounded-pill" onchange="this.form.submit()">
                        <option value="todas" '.($filtroEstado==='todas'?'selected':'').'>Todas las reservas</option>
                        <option value="pendiente" '.($filtroEstado==='pendiente'?'selected':'').'>Pendientes</option>
                        <option value="confirmada" '.($filtroEstado==='confirmada'?'selected':'').'>Confirmadas</option>
                        <option value="cancelada" '.($filtroEstado==='cancelada'?'selected':'').'>Canceladas</option>
                        <option value="finalizada" '.($filtroEstado==='finalizada'?'selected':'').'>Finalizadas</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Buscar huésped o ID</label>
                    <input type="text" name="buscar" class="form-control form-control-lg rounded-pill" 
                           placeholder="Nombre, apellido, documento o ID..." value="'.htmlspecialchars($buscar).'">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-rojo-quemado btn-lg w-100 rounded-pill shadow">
                        Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTA DE RESERVAS -->
    <div class="row g-4">
        ' . (empty($reservas) ? '
        <div class="col-12 text-center py-5">
            <i class="fas fa-calendar-times fa-5x text-muted mb-4"></i>
            <h4 class="text-muted">No se encontraron reservas con estos filtros</h4>
        </div>' : '') . '

        ' . implode('', array_map(function($r) {
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
                <div class="btn-group mt-3 d-flex gap-2" role="group">
                    <button type="button" onclick="abrirModal(\'confirmar\', '.$r['idReserva'].')" 
                       class="btn btn-success btn-sm w-100">
                        Confirmar
                    </button>
                    <button type="button" onclick="abrirModal(\'rechazar\', '.$r['idReserva'].')" 
                       class="btn btn-danger btn-sm w-100">
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
                </div>
            </div>';
        }, $reservas)) . '
    </div>
</div>

<!-- ✅ MODAL DE CONFIRMACIÓN INTEGRADO -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitulo"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalCuerpo"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <!-- Formulario oculto para enviar la acción -->
        <form method="POST" style="display:inline;">
            <input type="hidden" name="accion" id="modalFormAccion">
            <input type="hidden" name="idReserva" id="modalFormId">
            <button type="submit" class="btn" id="modalSubmitBtn">Confirmar</button>
        </form>
      </div>
    </div>
  </div>
</div>
';

include 'plantilla_admin.php';
?>