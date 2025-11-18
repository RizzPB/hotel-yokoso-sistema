<?php
// public/vistas/recepcionista/ver_reservas.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || ($_SESSION['rol'] !== 'empleado' && $_SESSION['rol'] !== 'admin')) {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Obtener todas las reservas con información del huésped, habitación y paquete
$stmt = $pdo->prepare("
    SELECT r.idReserva, h.nombre, h.apellido, hab.tipo, hab.numero, r.fechaInicio, r.fechaFin, r.total, r.estado
    FROM Reserva r
    JOIN Huesped h ON r.idHuesped = h.idHuesped
    JOIN ReservaHabitacion rh ON r.idReserva = rh.idReserva
    JOIN Habitacion hab ON rh.idHabitacion = hab.idHabitacion
    ORDER BY r.fechaInicio DESC
");
$stmt->execute();
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Ver Reservas - Hotel Yokoso";

$contenido_principal = '
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-rojo fw-bold">Reservas Registradas</h2>
    </div>

    <!-- Tabla de Reservas -->
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered border-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Huésped</th>
                    <th>Tipo de Habitación</th>
                    <th>Número de Habitación</th>
                    <th>Fechas</th>
                    <th>Total (Bs.)</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                ' . (empty($reservas) ? '<tr><td colspan="8" class="text-center py-4">No hay reservas registradas.</td></tr>' : implode('', array_map(function($res) {
                    return '
                    <tr>
                        <td>' . htmlspecialchars($res['idReserva']) . '</td>
                        <td>' . htmlspecialchars($res['nombre'] . ' ' . $res['apellido']) . '</td>
                        <td>' . htmlspecialchars($res['tipo']) . '</td>
                        <td>' . htmlspecialchars($res['numero']) . '</td>
                        <td>' . htmlspecialchars($res['fechaInicio']) . ' - ' . htmlspecialchars($res['fechaFin']) . '</td>
                        <td>' . htmlspecialchars($res['total']) . '</td>
                        <td>
                            <span class="status-badge status-' . $res['estado'] . '">' . ucfirst($res['estado']) . '</span>
                        </td>
                        <td class="text-center">
                            <a href="editar_reserva.php?id=' . $res['idReserva'] . '" class="btn btn-outline-primary btn-sm me-1" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-outline-danger btn-sm" onclick="eliminarReserva(' . $res['idReserva'] . ')" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>';
                }, $reservas))) . '
            </tbody>
        </table>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar esta reserva? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminarBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let reservaAEliminar = null;

        function eliminarReserva(id) {
            reservaAEliminar = id;
            const modal = new bootstrap.Modal(document.getElementById("confirmModal"));
            modal.show();
        }

        document.getElementById("confirmarEliminarBtn").addEventListener("click", function() {
            if (reservaAEliminar) {
                window.location.href = "eliminar_reserva.php?id=" + reservaAEliminar;
            }
        });
    </script>
';

include 'plantilla_recepcionista.php';
?>