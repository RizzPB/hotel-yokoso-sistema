<?php
// public/recepcionista/ver_reservas.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || ($_SESSION['rol'] !== 'empleado' && $_SESSION['rol'] !== 'admin')) {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'ver_reservas';
require_once __DIR__ . '/../../config/database.php';

// TRAEMOS TODAS LAS RESERVAS NO CANCELADAS 
$stmt = $pdo->prepare("
    SELECT r.*, h.nombre, h.apellido,
           GROUP_CONCAT(ha.numero SEPARATOR ', ') AS numeros_habitacion
    FROM Reserva r
    JOIN Huesped h ON r.idHuesped = h.idHuesped
    LEFT JOIN ReservaHabitacion rh ON r.idReserva = rh.idReserva
    LEFT JOIN Habitacion ha ON rh.idHabitacion = ha.idHabitacion
    WHERE r.estado != 'cancelada'
    GROUP BY r.idReserva
    ORDER BY r.fechaInicio DESC, r.idReserva DESC
");
$stmt->execute();
$todas_reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Reservas - Hotel Yokoso";

$contenido_principal = '
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-rojo fw-bold">
            <i class="fas fa-calendar-alt me-3"></i>
            Gestión de Reservas
        </h2>
        <a href="crear_reserva.php" class="btn btn-yokoso btn-lg rounded-pill px-5 shadow-lg">
            <i class="fas fa-plus me-2"></i>Nueva Reserva
        </a>
    </div>

    <!-- FILTRO PARA RESERVAS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark mb-0">Filtrar reservas:</label>
                </div>
                <div class="col-md-9">
                    <select class="form-select form-select-lg rounded-pill w-auto d-inline-block" id="filtroEstado">
                        <option value="todas">Todas las reservas</option>
                        <option value="pendiente">Solo pendientes</option>
                        <option value="confirmada">Solo confirmadas</option>
                        <option value="activas" selected>Pendientes + Confirmadas</option>
                        <option value="finalizada">Finalizadas</option>
                    </select>
                    <small class="text-muted ms-3">Total: <strong id="contador">'.count($todas_reservas).'</strong> reservas</small>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTA DE RESERVAS -->
    <div class="row g-4" id="listaReservas">
        ' . (empty($todas_reservas) ? '<div class="col-12 text-center py-5"><i class="fas fa-calendar fa-4x text-muted mb-3"></i><h4>No hay reservas activas</h4></div>' : '') . '

        ' . implode('', array_map(function($r) {
            $badge = match($r['estado']) {
                'pendiente'   => 'warning',
                'confirmada'  => 'success',
                'finalizada'  => 'secondary',
                'cancelada'   => 'danger',
                default       => 'info'
            };
            $estadoTexto = ucfirst(str_replace('_', ' ', $r['estado']));

            return '
            <div class="col-md-6 col-lg-4 reserva-item" data-estado="'.$r['estado'].'">
                <div class="card h-100 shadow hover-lift border-0 transition">
                    <div class="card-header bg-rojo-quemado text-black">
                        <h5 class="mb-0">Reserva #'.$r['idReserva'].'</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-2">'.htmlspecialchars($r['nombre'].' '.$r['apellido']).'</h6>
                        <p class="small text-muted mb-2">
                            <i class="fas fa-bed text-primary"></i> 
                            Hab: '.htmlspecialchars($r['numeros_habitacion'] ?? 'Sin asignar').'
                        </p>
                        <p class="small mb-3">
                            <i class="fas fa-calendar-alt text-rojo"></i> 
                            '.date('d/m/Y', strtotime($r['fechaInicio'])).' → '.date('d/m/Y', strtotime($r['fechaFin'])).'
                        </p>
                        <div class="d-flex justify-content-between align-items-end">
                            <h4 class="text-success fw-bold mb-0">Bs. '.number_format($r['total'], 2).'</h4>
                            <span class="badge bg-'.$badge.' fs-6 px-3 py-2">'.$estadoTexto.'</span>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0">
                        <a href="editar_reserva.php?id='.$r['idReserva'].'" class="btn btn-yokoso btn-sm w-100 rounded-pill">
                            <i class="fas fa-edit"></i> Gestionar
                        </a>
                    </div>
                </div>
            </div>';
        }, $todas_reservas)) . '
    </div>
</div>

<script>
// FILTRO EN TIEMPO REAL (sin recargar página)
document.getElementById("filtroEstado").addEventListener("change", function() {
    const filtro = this.value;
    const items = document.querySelectorAll(".reserva-item");
    let visibles = 0;

    items.forEach(item => {
        const estado = item.dataset.estado;

        let mostrar = false;
        if (filtro === "todas") mostrar = true;
        else if (filtro === "pendiente" && estado === "pendiente") mostrar = true;
        else if (filtro === "confirmada" && estado === "confirmada") mostrar = true;
        else if (filtro === "finalizada" && estado === "finalizada") mostrar = true;
        else if (filtro === "activas" && (estado === "pendiente" || estado === "confirmada")) mostrar = true;

        item.style.display = mostrar ? "block" : "none";
        if (mostrar) visibles++;
    });

    // Actualizar contador
    document.getElementById("contador").textContent = visibles;
});
</script>
';

include 'plantilla_recepcionista.php';
?>