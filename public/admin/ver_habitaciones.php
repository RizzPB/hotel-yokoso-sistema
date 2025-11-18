<?php
// public/vistas/admin/ver_habitaciones.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

// Obtener todas las habitaciones
$stmt = $pdo->prepare("
    SELECT idHabitacion, numero, tipo, precioNoche, estado, foto
    FROM Habitacion
    ORDER BY numero ASC
");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Ver Habitaciones - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Gestión de Habitaciones</h2>
            <a href="crear_habitacion.php" class="btn btn-yokoso btn-lg shadow-sm">
                <i class="fas fa-plus me-2"></i>Nueva Habitación
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered border-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Número</th>
                    <th>Tipo</th>
                    <th>Precio por Noche (Bs.)</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                ' . (empty($habitaciones) ? '<tr><td colspan="6" class="text-center py-4">No hay habitaciones registradas.</td></tr>' : implode('', array_map(function($hab) {
                    $estado = $hab['estado'] === 'disponible' ? 'Disponible' : ($hab['estado'] === 'ocupada' ? 'Ocupada' : 'Mantenimiento');
                    $estadoClass = $hab['estado'] === 'disponible' ? 'status-disponible' : ($hab['estado'] === 'ocupada' ? 'status-ocupada' : 'status-mantenimiento');
                    return '
                        <tr>
                            <td>' . htmlspecialchars($hab['idHabitacion']) . '</td>
                            <td>' . htmlspecialchars($hab['numero']) . '</td>
                            <td>' . htmlspecialchars(ucfirst($hab['tipo'])) . '</td>
                            <td>' . htmlspecialchars($hab['precioNoche']) . '</td>
                            <td>
                                <span class="status-badge ' . $estadoClass . '">' . $estado . '</span>
                            </td>
                            <td class="text-center">
                                <a href="editar_habitacion.php?id=' . $hab['idHabitacion'] . '" class="btn btn-outline-primary btn-sm me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarHabitacion(' . $hab['idHabitacion'] . ')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>';
                }, $habitaciones))) . '
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
                    ¿Estás seguro de que deseas eliminar esta habitación? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminarBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let habitacionAEliminar = null;

        function eliminarHabitacion(id) {
            habitacionAEliminar = id;
            const modal = new bootstrap.Modal(document.getElementById("confirmModal"));
            modal.show();
        }

        document.getElementById("confirmarEliminarBtn").addEventListener("click", function() {
            if (habitacionAEliminar) {
                window.location.href = "eliminar_habitacion.php?id=" + habitacionAEliminar;
            }
        });
    </script>
';

include 'plantilla_admin.php';
?>