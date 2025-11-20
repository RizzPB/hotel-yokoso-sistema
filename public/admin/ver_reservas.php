<?php
// public/admin/ver_reservas.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'reservas'; // Para resaltar en el sidebar
require_once __DIR__ . '/../../config/database.php';

// Filtros
$filtroEstado = $_GET['estado'] ?? 'todas';
$buscar = trim($_GET['buscar'] ?? '');

// Consulta base (todas las reservas, incluso las hechas por huéspedes)
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
    $sql .= " AND (h.nombre LIKE ? OR h.apellido LIKE ? OR h.nroDocumento LIKE ? OR r.idReserva LIKE ?)";
    $like = "%$buscar%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

/* $sql .= " GROUP BY r.idReserva ORDER BY r.fechaRegistro DESC"; */

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Reservas - Panel Administrador";

$contenido_principal = '
<div class="container py-5">

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
                    <label class="form-label fw-bold">Estado</label>
                    <select name="estado" class="form-select form-select-lg rounded-pill" onchange="this.form.submit()">
                        <option value="todas" '.($filtroEstado==='todas'?'selected':'').'>Todas las reservas</option>
                        <option value="pendiente" '.($filtroEstado==='pendiente'?'selected':'').'>Pendientes de confirmar</option>
                        <option value="confirmada" '.($filtroEstado==='confirmada'?'selected':'').'>Confirmadas</option>
                        <option value="cancelada" '.($filtroEstado==='cancelada'?'selected':'').'>Canceladas</option>
                        <option value="finalizada" '.($filtroEstado==='finalizada'?'selected':'').'>Finalizadas</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Buscar</label>
                    <input type="text" name="buscar" class="form-control form-control-lg rounded-pill" placeholder="Nombre, documento o ID reserva..." value="'.htmlspecialchars($buscar).'">
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
            <h4 class="text-muted">No se encontraron reservas</h4>
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
                <div class="btn-group" role="group">
                    <a href="acciones_reserva.php?id='.$r['idReserva'].'&accion=confirmar" class="btn btn-success btn-sm" onclick="return confirm(\'¿Confirmar esta reserva?\')">
                        Confirmar
                    </a>
                    <a href="acciones_reserva.php?id='.$r['idReserva'].'&accion=rechazar" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Rechazar esta reserva?\')">
                        Rechazar
                    </a>
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
                            <i class="fas fa-id-card"></i> '.htmlspecialchars($r['nroDocumento']).'
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
                        '.($r['estado']==='pendiente' ? '<div class="mt-3 text-center">'.$acciones.'</div>' : '').'
                    </div>
                    <div class="card-footer bg-light text-center">
                        <a href="editar_reserva_admin.php?id='.$r['idReserva'].'" class="btn btn-yokoso btn-sm w-100 rounded-pill">
                            Gestionar
                        </a>
                    </div>
                </div>
            </div>';
        }, $reservas)) . '
    </div>
</div>
';

include 'plantilla_admin.php';
?>