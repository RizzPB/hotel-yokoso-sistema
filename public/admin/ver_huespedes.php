<?php
// public/vistas/admin/ver_huespedes.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$current_page = 'huespedes';  // ← RESALTA

$stmt = $pdo->prepare("SELECT idHuesped, nombre, apellido, tipoDocumento, nroDocumento, email, telefono, activo FROM Huesped ORDER BY apellido");
$stmt->execute();
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Huéspedes - Hotel Yokoso";

$contenido_principal = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-rojo fw-bold">Gestión de Huéspedes</h2>
    <a href="crear_huesped.php" class="btn btn-dark btn-lg shadow-lg px-5 position-relative overflow-hidden"">
        <i class="fas fa-user-plus me-2"></i>Nuevo Huésped
    </a>
    
</div>

<div class="row g-4">
    ' . (empty($huespedes) ? '<div class="col-12 text-center py-5 text-muted"><i class="fas fa-users fa-4x mb-3"></i><h5>No hay huéspedes</h5></div>' : '') . '
    ' . implode('', array_map(function($h) {
        $badge = $h['activo'] 
            ? '<span class="badge bg-success fs-6">Activo</span>' 
            : '<span class="badge bg-danger fs-6">Inactivo</span>';
        return '
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">' . htmlspecialchars($h['nombre'] . ' ' . $h['apellido']) . '</h5>
                            <small class="text-muted">' . htmlspecialchars($h['tipoDocumento']) . ': ' . htmlspecialchars($h['nroDocumento']) . '</small>
                        </div>
                        ' . $badge . '
                    </div>
                    <hr class="my-2">
                    <p class="small mb-1"><i class="fas fa-envelope text-primary"></i> ' . htmlspecialchars($h['email'] ?: 'Sin email') . '</p>
                    <p class="small mb-0"><i class="fas fa-phone text-success"></i> ' . htmlspecialchars($h['telefono'] ?: 'Sin teléfono') . '</p>
                    <div class="mt-3 text-end">
                        <a href="editar_huesped.php?id=' . $h['idHuesped'] . '" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>';
    }, $huespedes)) . '
</div>
';

include 'plantilla_admin.php';
?>