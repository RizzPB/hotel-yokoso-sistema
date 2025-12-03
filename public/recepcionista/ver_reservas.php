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

// TRAEMOS TODAS LAS RESERVAS
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
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-rojo fw-bold">
            Gestión de Reservas
        </h2>
        <a href="crear_reserva.php" class="btn btn-yokoso btn-lg rounded-pill px-5 shadow-lg">
            Nueva Reserva
        </a>
    </div>

    <!-- BUSCADOR + FILTRO -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <!-- ❤️ BUSCADOR -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark">Buscar reservas</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-rojo-quemado text-white">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               id="buscarReservas" 
                               class="form-control rounded-pill" 
                               placeholder="Nombre, apellido, ID o habitación...">
                    </div>
                </div>
                <!-- FILTRO DE ESTADO -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark mb-0">Filtrar reservas:</label>
                    <select class="form-select form-select-lg rounded-pill" id="filtroEstado">
                        <option value="todas">Todas las reservas</option>
                        <option value="pendiente">Solo pendientes</option>
                        <option value="confirmada">Solo confirmadas</option>
                        <option value="activas" selected>Pendientes + Confirmadas</option>
                        <option value="finalizada">Finalizadas</option>
                    </select>
                </div>
            </div>
            <div class="text-end mt-2">
                <small class="text-muted">Total: <strong id="contador">' . count($todas_reservas) . '</strong> reservas activas</small>
            </div>
        </div>
    </div>

    <!-- LISTA DE RESERVAS -->
    <div class="row g-4" id="listaReservas">
        ' . (empty($todas_reservas) ? '<div class="col-12 text-center py-5"><h4>No hay reservas activas</h4></div>' : '') . '
        ' . implode('', array_map(function($r) {
            $badge = match($r['estado']) {
                'pendiente'     => 'warning',
                'confirmada'    => 'success',
                'finalizada'    => 'secondary',
                'ocupada'       => 'primary',
                default         => 'info'
            };
            $estadoTexto = ucfirst($r['estado']);

            $fechaHoraCreacion = 'Sin fecha';
            if (!empty($r['fechaCreacion']) && $r['fechaCreacion'] !== '0000-00-00 00:00:00') {
                try {
                    $dt = new DateTime($r['fechaCreacion']);
                    $fechaHoraCreacion = $dt->format('d/m/Y H:i');
                } catch (Exception $e) {
                    $fechaHoraCreacion = 'Error en fecha';
                }
            }

            return '
            <div class="col-md-6 col-lg-4 reserva-item" 
                 data-estado="' . $r['estado'] . '"
                 data-nombre="' . htmlspecialchars(strtolower($r['nombre'] . ' ' . $r['apellido'])) . '"
                 data-id="' . $r['idReserva'] . '"
                 data-habitacion="' . htmlspecialchars(strtolower($r['numeros_habitacion'] ?? '')) . '">
                <div class="card h-100 shadow hover-lift border-0 transition">
                    <div class="card-header bg-rojo-quemado text-white">
                        <h5 class="mb-0 text-black">
                            Reserva #<strong>' . $r['idReserva'] . '</strong>
                        </h5>
                        <small class="text-white-50 d-block mt-1">
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
                            <span class="badge bg-' . $badge . ' fs-6 px-3 py-2">' . $estadoTexto . '</span>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0">
                        <a href="editar_reserva.php?id=' . $r['idReserva'] . '" class="btn btn-yokoso btn-sm w-100 rounded-pill">
                            Gestionar
                        </a>
                    </div>
                </div>
            </div>';
        }, $todas_reservas)) . '
    </div>
</div>

<script>
// ==================== CARGAR TODAS LAS RESERVAS EN JS ====================
// (Ya están en el HTML, pero los datos están en los atributos data-*)

// ==================== FUNCIÓN PARA FILTRAR ====================
function filtrarReservas() {
    const termino = document.getElementById("buscarReservas").value.toLowerCase().trim();
    const filtroEstado = document.getElementById("filtroEstado").value;
    const items = document.querySelectorAll(".reserva-item");
    let visibles = 0;

    items.forEach(item => {
        const nombre = item.dataset.nombre || "";
        const id = item.dataset.id || "";
        const habitacion = item.dataset.habitacion || "";
        const estado = item.dataset.estado || "";

        // ✨ Filtrar por estado
        let coincideEstado = false;
        if (filtroEstado === "todas") coincideEstado = true;
        else if (filtroEstado === "pendiente" && estado === "pendiente") coincideEstado = true;
        else if (filtroEstado === "confirmada" && estado === "confirmada") coincideEstado = true;
        else if (filtroEstado === "finalizada" && estado === "finalizada") coincideEstado = true;
        else if (filtroEstado === "activas" && (["pendiente", "confirmada", "ocupada"].includes(estado))) coincideEstado = true;

        // 💖 Filtrar por término de búsqueda
        let coincideBusqueda = true;
        if (termino !== "") {
            coincideBusqueda = 
                nombre.includes(termino) ||
                id.includes(termino) ||
                habitacion.includes(termino);
        }

        const mostrar = coincideEstado && coincideBusqueda;
        item.style.display = mostrar ? "block" : "none";
        if (mostrar) visibles++;
    });

    document.getElementById("contador").textContent = visibles;

    // 🌸 Mensaje si no hay resultados
    if (visibles === 0) {
        // Ver si ya existe el mensaje
        if (!document.getElementById("noResultados")) {
            const mensaje = document.createElement("div");
            mensaje.id = "noResultados";
            mensaje.className = "col-12 text-center py-5 text-muted";
            mensaje.innerHTML = \'<i class="fas fa-search fa-4x mb-3"></i><br>No se encontraron reservas.\';
            document.getElementById("listaReservas").appendChild(mensaje);
        }
    } else {
        // Eliminar mensaje si existe
        const mensaje = document.getElementById("noResultados");
        if (mensaje) mensaje.remove();
    }
}

// ==================== EVENTOS ====================
document.getElementById("buscarReservas").addEventListener("input", filtrarReservas);
document.getElementById("filtroEstado").addEventListener("change", filtrarReservas);

// Ejecutar al inicio (por si hay datos pre-cargados)
filtrarReservas();
</script>
';

include 'plantilla_recepcionista.php';
?>