<?php
// public/vistas/admin/ver_paquetes.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

// Obtener todos los paquetes turísticos
$stmt = $pdo->prepare("
    SELECT idPaquete, nombre, descripcion, precio, duracionDias, incluye, noIncluye, activo
    FROM PaqueteTuristico
    ORDER BY nombre ASC
");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Ver Paquetes Turísticos - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Gestión de Paquetes Turísticos</h2>
            <a href="crear_paquete.php" class="btn btn-yokoso btn-lg shadow-sm">
                <i class="fas fa-plus me-2"></i>Nuevo Paquete
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered border-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio (Bs.)</th>
                    <th>Duración (días)</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                ' . (empty($paquetes) ? '<tr><td colspan="7" class="text-center py-4">No hay paquetes turísticos registrados.</td></tr>' : implode('', array_map(function($pkg) {
                    $estado = $pkg['activo'] ? 'Activo' : 'Inactivo';
                    $estadoClass = $pkg['activo'] ? 'status-activo' : 'status-inactivo';
                    return '
                        <tr>
                            <td>' . htmlspecialchars($pkg['idPaquete']) . '</td>
                            <td>' . htmlspecialchars($pkg['nombre']) . '</td>
                            <td>' . htmlspecialchars(substr($pkg['descripcion'], 0, 50)) . '...</td>
                            <td>' . htmlspecialchars($pkg['precio']) . '</td>
                            <td>' . htmlspecialchars($pkg['duracionDias']) . '</td>
                            <td>
                                <span class="status-badge ' . $estadoClass . '">' . $estado . '</span>
                            </td>
                            <td class="text-center">
                                <a href="editar_paquete.php?id=' . $pkg['idPaquete'] . '" class="btn btn-outline-primary btn-sm me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarPaquete(' . $pkg['idPaquete'] . ')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>';
                }, $paquetes))) . '
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
                    ¿Estás seguro de que deseas eliminar este paquete turístico? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminarBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let paqueteAEliminar = null;

        function eliminarPaquete(id) {
            paqueteAEliminar = id;
            const modal = new bootstrap.Modal(document.getElementById("confirmModal"));
            modal.show();
        }

        document.getElementById("confirmarEliminarBtn").addEventListener("click", function() {
            if (paqueteAEliminar) {
                window.location.href = "eliminar_paquete.php?id=" + paqueteAEliminar;
            }
        });
    </script>
';

include 'plantilla_admin.php';
?>