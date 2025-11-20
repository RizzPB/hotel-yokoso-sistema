<?php
// public/admin/ver_empleados.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$current_page = 'empleados';  // ← resalta la opcion seleccionada en el sidebar

$stmt = $pdo->prepare("SELECT e.idEmpleado, e.nombre, e.apellido, e.cargo, u.email, u.rol, u.activo 
                       FROM Empleado e JOIN Usuario u ON e.idUsuario = u.idUsuario ORDER BY e.apellido");
$stmt->execute();
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Empleados - Hotel Yokoso";

$contenido_principal = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-rojo fw-bold">Gestión de Empleados</h2>
    <a href="crear_empleado.php" class="btn btn-dark btn-lg shadow-lg px-5 position-relative overflow-hidden">
        <i class="fas fa-user-plus me-2"></i>Nuevo Empleado
    </a>
</div>

<div class="row g-4">
    ' . (empty($empleados) ? '<div class="col-12 text-center py-5 text-muted"><i class="fas fa-user-tie fa-4x mb-3"></i><h5>No hay empleados</h5></div>' : '') . '
    ' . implode('', array_map(function($e) {
        $badge = $e['activo'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
        $rolBadge = $e['rol'] === 'admin' ? '<span class="badge bg-danger">ADMIN</span>' : '<span class="badge bg-primary">Empleado</span>';
        return '
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow hover-lift border-0">
                <div class="card-body text-center">
                    <div class="avatar bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                        ' . strtoupper(substr($e['nombre'],0,1).substr($e['apellido'],0,1)) . '
                    </div>
                    <h5>' . htmlspecialchars($e['nombre'] . ' ' . $e['apellido']) . '</h5>
                    <p class="text-muted mb-2">' . htmlspecialchars($e['cargo']) . '</p>
                    ' . $rolBadge . ' ' . $badge . '
                    <p class="small text-muted mt-3 mb-1">' . htmlspecialchars($e['email']) . '</p>
                    <a href="editar_empleado.php?id=' . $e['idEmpleado'] . '" class="btn btn-outline-primary btn-sm mt-3">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
        </div>';
    }, $empleados)) . '
</div>
';

include 'plantilla_admin.php';
?>