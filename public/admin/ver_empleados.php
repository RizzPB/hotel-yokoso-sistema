<?php
// public/vistas/admin/ver_empleados.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Obtener todos los empleados con información del usuario
$stmt = $pdo->prepare("
    SELECT e.idEmpleado, e.nombre, e.apellido, e.cargo, u.nombreUsuario, u.email, u.rol, u.activo
    FROM Empleado e
    JOIN Usuario u ON e.idUsuario = u.idUsuario
    ORDER BY e.nombre ASC
");
$stmt->execute();
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Ver Empleados - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Gestión de Empleados</h2>
            <a href="crear_empleado.php" class="btn btn-yokoso btn-lg shadow-sm">
                <i class="fas fa-plus me-2"></i>Nuevo Empleado
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
                    <th>Cargo</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                ' . (empty($empleados) ? '<tr><td colspan="9" class="text-center py-4">No hay empleados registrados.</td></tr>' : implode('', array_map(function($emp) {
                    $estado = $emp['activo'] ? 'Activo' : 'Inactivo';
                    $estadoClass = $emp['activo'] ? 'status-activo' : 'status-inactivo';
                    return '
                        <tr>
                            <td>' . htmlspecialchars($emp['idEmpleado']) . '</td>
                            <td>' . htmlspecialchars($emp['nombre']) . '</td>
                            <td>' . htmlspecialchars($emp['apellido']) . '</td>
                            <td>' . htmlspecialchars($emp['cargo']) . '</td>
                            <td>' . htmlspecialchars($emp['nombreUsuario']) . '</td>
                            <td>' . htmlspecialchars($emp['email']) . '</td>
                            <td>' . htmlspecialchars($emp['rol']) . '</td>
                            <td>
                                <span class="status-badge ' . $estadoClass . '">' . $estado . '</span>
                            </td>
                            <td class="text-center">
                                <a href="editar_empleado.php?id=' . $emp['idEmpleado'] . '" class="btn btn-outline-primary btn-sm me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarEmpleado(' . $emp['idEmpleado'] . ')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>';
                }, $empleados))) . '
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
                    ¿Estás seguro de que deseas eliminar este empleado? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminarBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let empleadoAEliminar = null;

        function eliminarEmpleado(id) {
            empleadoAEliminar = id;
            const modal = new bootstrap.Modal(document.getElementById("confirmModal"));
            modal.show();
        }

        document.getElementById("confirmarEliminarBtn").addEventListener("click", function() {
            if (empleadoAEliminar) {
                // Aquí iría la lógica para eliminar (por ejemplo, con fetch o redirección)
                window.location.href = "eliminar_empleado.php?id=" + empleadoAEliminar;
            }
        });
    </script>
';

include 'plantilla_admin.php';
?>