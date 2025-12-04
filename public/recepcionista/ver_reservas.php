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

// Mensajes de sesión (de checkout.php, registrar_huesped.php, etc.)
$mensaje_exito = $_SESSION['mensaje_exito'] ?? null;
$mensaje_error = $_SESSION['mensaje_error'] ?? null;
if ($mensaje_exito) unset($_SESSION['mensaje_exito']);
if ($mensaje_error) unset($_SESSION['mensaje_error']);

// TRAEMOS TODAS LAS RESERVAS CON FECHA Y HORA DE CREACIÓN
$stmt = $pdo->prepare("
    SELECT r.*, h.nombre, h.apellido, r.fechaCreacion, 
           GROUP_CONCAT(ha.numero SEPARATOR ', ') AS numeros_habitacion
    FROM Reserva r
    JOIN Huesped h ON r.idHuesped = h.idHuesped
    LEFT JOIN ReservaHabitacion rh ON r.idReserva = rh.idReserva
    LEFT JOIN Habitacion ha ON rh.idHabitacion = ha.idHabitacion
    WHERE r.estado != 'cancelada'
    GROUP BY r.idReserva
    ORDER BY r.idReserva DESC
");
$stmt->execute();
$todas_reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Reservas - Hotel Yokoso";

$contenido_principal = '
<div class="container py-5">

    <!-- Mensajes de éxito o error -->
    ' . ($mensaje_exito ? '<div class="alert alert-success alert-dismissible fade show text-center" role="alert">
        <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($mensaje_exito) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>' : '') . '
    ' . ($mensaje_error ? '<div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($mensaje_error) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>' : '') . '

    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-rojo fw-bold">
            Gestión de Reservas
        </h2>
        <a href="crear_reserva.php" class="btn btn-yokoso btn-lg rounded-pill px-5 shadow-lg">
            Nueva Reserva
        </a>
    </div>

    <!-- 🔍 BUSCADOR EN TIEMPO REAL -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark mb-0">Buscar reserva:</label>
                </div>
                <div class="col-md-9">
                    <input type="text" 
                           id="buscadorReservas" 
                           class="form-control form-control-lg rounded-pill" 
                           placeholder="Ej: #123, María López, Hab. 5A...">
                </div>
            </div>
        </div>
    </div>

    <!-- FILTRO POR ESTADO -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark mb-0">Filtrar reservas:</label>
                </div>
                <div class="col-md-9">
                    <select class="form-select form-select-lg rounded-pill w-auto d-inline-block" id="filtroEstado">
                        <option value="todas">Todas las reservas</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="confirmada">Confirmadas</option>
                        <option value="ocupada">Ocupadas (check-in)</option>
                        <option value="activas" selected>Activas (Pendientes + Confirmadas + Ocupadas)</option>
                        <option value="finalizada">Finalizadas</option>
                    </select>
                    <small class="text-muted ms-3">Total: <strong id="contador">' . count($todas_reservas) . '</strong> reservas</small>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTA DE RESERVAS -->
    <div class="row g-4" id="listaReservas">
        ' . (empty($todas_reservas) ? '<div class="col-12 text-center py-5"><h4>No hay reservas</h4></div>' : '') . '
        ' . implode('', array_map(function($r) {
            $badge = match($r['estado']) {
                'pendiente'     => 'warning',
                'confirmada'    => 'info',
                'ocupada'       => 'primary',
                'finalizada'    => 'secondary',
                default         => 'light'
            };
            $estadoTexto = ucfirst($r['estado']);

            // Formatear fecha y hora de creación
            $fechaHoraCreacion = 'Sin fecha';
            if (!empty($r['fechaCreacion']) && $r['fechaCreacion'] !== '0000-00-00 00:00:00') {
                try {
                    $dt = new DateTime($r['fechaCreacion']);
                    $fechaHoraCreacion = $dt->format('d/m/Y H:i');
                } catch (Exception $e) {
                    $fechaHoraCreacion = 'Error en fecha';
                }
            }

            // Botón de acción principal
            $botonAccion = '';
            if ($r['estado'] === 'ocupada') {
                $botonAccion = '<a href="checkout.php?id=' . $r['idReserva'] . '" class="btn btn-danger btn-sm w-100 rounded-pill">
                    <i class="fas fa-door-open me-1"></i>Check-out
                </a>';
            } elseif ($r['estado'] === 'confirmada') {
                $botonAccion = '<a href="registrar_huesped.php?reserva=' . $r['idReserva'] . '" class="btn btn-success btn-sm w-100 rounded-pill">
                    <i class="fas fa-sign-in-alt me-1"></i>Registrar llegada
                </a>';
            } else {
                $botonAccion = '<a href="editar_reserva.php?id=' . $r['idReserva'] . '" class="btn btn-yokoso btn-sm w-100 rounded-pill">
                    Gestionar
                </a>';
            }

            return '
            <div class="col-md-6 col-lg-4 reserva-item" data-estado="' . $r['estado'] . '">
                <div class="card h-100 shadow hover-lift border-0 transition">
                    <div class="card-header bg-light text-black">
                        <h5 class="mb-0">
                            Reserva #<strong>' . $r['idReserva'] . '</strong>
                        </h5>
                        <small class="text-muted d-block mt-1">
                            Creada el ' . $fechaHoraCreacion . '
                        </small>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-2">' . htmlspecialchars($r['nombre'] . ' ' . $r['apellido']) . '</h6>
                        <p class="small text-muted mb-2">
                            Hab: ' . htmlspecialchars($r['numeros_habitacion'] ?? 'Sin asignar') . '
                        </p>
                        <p class="small mb-3">
                            ' . date('d/m/Y', strtotime($r['fechaInicio'])) . ' → ' . date('d/m/Y', strtotime($r['fechaFin'])) . '
                        </p>
                        <div class="d-flex justify-content-between align-items-end">
                            <h4 class="text-success fw-bold mb-0">Bs. ' . number_format($r['total'], 2) . '</h4>
                            <span class="badge bg-' . $badge . ' fs-6 px-2 py-1">' . $estadoTexto . '</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        ' . $botonAccion . '
                    </div>
                </div>
            </div>';
        }, $todas_reservas)) . '
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const buscador = document.getElementById("buscadorReservas");
    const filtroSelect = document.getElementById("filtroEstado");
    const items = document.querySelectorAll(".reserva-item");
    const contador = document.getElementById("contador");

    function aplicarFiltros() {
        const termino = (buscador?.value || "").toLowerCase().trim();
        const filtro = filtroSelect?.value || "todas";
        let visibles = 0;

        items.forEach(item => {
            const estado = item.dataset.estado;
            const textoReserva = item.textContent.toLowerCase();

            // Filtro por estado
            let coincideEstado = false;
            if (filtro === "todas") coincideEstado = true;
            else if (filtro === "pendiente" && estado === "pendiente") coincideEstado = true;
            else if (filtro === "confirmada" && estado === "confirmada") coincideEstado = true;
            else if (filtro === "ocupada" && estado === "ocupada") coincideEstado = true;
            else if (filtro === "finalizada" && estado === "finalizada") coincideEstado = true;
            else if (filtro === "activas" && (estado === "pendiente" || estado === "confirmada" || estado === "ocupada")) coincideEstado = true;

            // Filtro por búsqueda
            const coincideBusqueda = !termino || textoReserva.includes(termino);

            const mostrar = coincideEstado && coincideBusqueda;
            item.style.display = mostrar ? "block" : "none";
            if (mostrar) visibles++;
        });

        if (contador) contador.textContent = visibles;
    }

    if (buscador) buscador.addEventListener("input", aplicarFiltros);
    if (filtroSelect) filtroSelect.addEventListener("change", aplicarFiltros);
});
</script>
';

include 'plantilla_recepcionista.php';
?>