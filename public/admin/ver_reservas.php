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

// Filtros
$filtroEstado = $_GET['estado'] ?? 'todas';
$buscar = trim($_GET['buscar'] ?? '');

// Consulta segura
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

$titulo_pagina = "Reservas - Panel Administrador";

$contenido_principal = '
<div class="container py-5">
    <h2 class="text-rojo fw-bold mb-4">Gestión de Reservas</h2>

    <!-- FILTROS Y BUSCADOR -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Filtrar por estado</label>
                    <select id="filtroEstado" class="form-select form-select-lg rounded-pill">
                        <option value="todas">Todas las reservas</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="confirmada">Confirmadas</option>
                        <option value="cancelada">Canceladas</option>
                        <option value="finalizada">Finalizadas</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Buscar huésped</label>
                    <input type="text" id="buscarReservas" class="form-control form-control-lg rounded-pill" 
                           placeholder="Escribe nombre, apellido, documento o ID...">
                </div>
            </div>
        </div>
    </div>

    <!-- LISTA DE RESERVAS -->
    <div id="listaReservas" class="row g-4">
        ' . (empty($reservas) ? '
        <div class="col-12 text-center py-5">
            <i class="fas fa-calendar-times fa-5x text-muted mb-4"></i>
            <h4 class="text-muted">No se encontraron reservas con estos filtros</h4>
        </div>' : implode('', array_map(function($r) {
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
        }, $reservas))) . '
    </div>
</div>

<!-- SweetAlert2 para mensajes lindos -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Establecer estado inicial
document.getElementById("filtroEstado").value = "' . addslashes($filtroEstado) . '";
document.getElementById("buscarReservas").value = "' . addslashes($buscar) . '";

// Cargar reservas con AJAX (búsqueda en tiempo real)
function cargarReservas() {
    const estado = document.getElementById("filtroEstado").value;
    const buscar = document.getElementById("buscarReservas").value;

    fetch("buscar_reservas_ajax.php?estado=" + encodeURIComponent(estado) + "&buscar=" + encodeURIComponent(buscar))
        .then(response => response.text())
        .then(html => {
            document.getElementById("listaReservas").innerHTML = html;
        })
        .catch(err => {
            console.error("Error:", err);
            Swal.fire("Error", "No se pudieron cargar las reservas.", "error");
        });
}

// Eventos en tiempo real
document.getElementById("buscarReservas").addEventListener("input", cargarReservas);
document.getElementById("filtroEstado").addEventListener("change", cargarReservas);

// Acciones con alertas personalizadas
function confirmarReserva(id) {
    Swal.fire({
        title: "¿Confirmar reserva?",
        text: "La reserva pasará a estado confirmada.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, confirmar",
        cancelButtonText: "Cancelar",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "acciones_reserva.php?id=" + id + "&accion=confirmar";
        }
    });
}

function rechazarReserva(id) {
    Swal.fire({
        title: "¿Rechazar reserva?",
        text: "La reserva se marcará como cancelada.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, rechazar",
        cancelButtonText: "Cancelar",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "acciones_reserva.php?id=" + id + "&accion=rechazar";
        }
    });
}
</script>
';

include 'plantilla_admin.php';
?>