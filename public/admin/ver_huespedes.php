<?php
// public/vistas/admin/ver_huespedes.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

// Obtener todos los huéspedes
$stmt = $pdo->prepare("
    SELECT idHuesped, nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria, activo
    FROM Huesped
    ORDER BY nombre ASC
");
$stmt->execute();
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Ver Huéspedes - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Gestión de Huéspedes</h2>
            <a href="crear_huesped.php" class="btn btn-yokoso btn-lg shadow-sm">
                <i class="fas fa-plus me-2"></i>Nuevo Huésped
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered border-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Tipo Doc.</th>
                    <th>Nro. Doc.</th>
                    <th>Procedencia</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                ' . (empty($huespedes) ? '<tr><td colspan="10" class="text-center py-4">No hay huéspedes registrados.</td></tr>' : implode('', array_map(function($h) {
                    $estado = $h['activo'] ? 'Activo' : 'Inactivo';
                    $estadoClass = $h['activo'] ? 'status-activo' : 'status-inactivo';
                    return '
                        <tr>
                            <td>' . htmlspecialchars($h['idHuesped']) . '</td>
                            <td>' . htmlspecialchars($h['nombre']) . '</td>
                            <td>' . htmlspecialchars($h['apellido']) . '</td>
                            <td>' . htmlspecialchars($h['tipoDocumento']) . '</td>
                            <td>' . htmlspecialchars($h['nroDocumento']) . '</td>
                            <td>' . htmlspecialchars($h['procedencia']) . '</td>
                            <td>' . htmlspecialchars($h['email']) . '</td>
                            <td>' . htmlspecialchars($h['telefono']) . '</td>
                            <td>
                                <span class="status-badge ' . $estadoClass . '">' . $estado . '</span>
                            </td>
                            <td class="text-center">
                                <a href="editar_huesped.php?id=' . $h['idHuesped'] . '" class="btn btn-outline-primary btn-sm me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarHuesped(' . $h['idHuesped'] . ')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>';
                }, $huespedes))) . '
            </tbody>
        </table>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar este huésped? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminarBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let huespedAEliminar = null;

        function eliminarHuesped(id) {
            huespedAEliminar = id;
            const modal = new bootstrap.Modal(document.getElementById("confirmModal"));
            modal.show();
        }

        document.getElementById("confirmarEliminarBtn").addEventListener("click", function() {
            if (huespedAEliminar) {
                window.location.href = "eliminar_huesped.php?id=" + huespedAEliminar;
            }
        });
    </script>
';

include 'plantilla_admin.php';
?>