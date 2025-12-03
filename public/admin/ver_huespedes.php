<?php
// public/vistas/admin/ver_huespedes.php


define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'ver_huespedes';
require_once __DIR__ . '/../../config/database.php';

$current_page = 'huespedes';

// Cargamos todos los huéspedes al inicio (para mostrar algo antes del AJAX)
$stmt = $pdo->prepare("SELECT idHuesped, nombre, apellido, tipoDocumento, nroDocumento, email, telefono, activo FROM Huesped ORDER BY apellido");
$stmt->execute();
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Huéspedes - Hotel Yokoso";

$contenido_principal = '
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <h2 class="text-rojo fw-bold mb-0">Gestión de Huéspedes</h2>
   
</div>

<!-- ❤️ Buscador en tiempo real -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row">
            <div class="col-md-8">
                <label class="form-label fw-bold">Buscar huésped</label>
                <input type="text" id="buscarHuespedes" class="form-control form-control-lg rounded-pill" 
                       placeholder="Nombre, apellido, documento o email...">
            </div>
        </div>
    </div>
</div>

<!-- 🌸 Lista de huéspedes (se actualizará con AJAX) -->
<div id="listaHuespedes" class="row g-4">
    ' . (empty($huespedes) ? '
    <div class="col-12 text-center py-5 text-muted">
        <i class="fas fa-users fa-4x mb-3"></i>
        <h5>No hay huéspedes registrados</h5>
    </div>' : implode('', array_map(function($h) {
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
    }, $huespedes))) . '
</div>

<script>
// ❤️ Función para cargar huéspedes con AJAX
function cargarHuespedes() {
    const buscar = document.getElementById("buscarHuespedes").value;

    fetch("buscar_huespedes_ajax.php?buscar=" + encodeURIComponent(buscar))
        .then(response => response.text())
        .then(html => {
            document.getElementById("listaHuespedes").innerHTML = html;
        })
        .catch(err => {
            console.error("Error al cargar huéspedes:", err);
            document.getElementById("listaHuespedes").innerHTML = 
                \'<div class="col-12 text-center py-5 text-danger"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><h5>Error al cargar los huéspedes</h5></div>\';
        });
}

// 🌸 Escuchar cada tecla que escribes (¡en tiempo real!)
document.getElementById("buscarHuespedes").addEventListener("input", cargarHuespedes);
</script>
';

include 'plantilla_admin.php';
?>