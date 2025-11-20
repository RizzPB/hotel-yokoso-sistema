<?php
// public/vistas/admin/ver_paquetes.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$current_page = 'paquetes';  // ← RESALTA EL MENÚ

$stmt = $pdo->prepare("SELECT idPaquete, nombre, descripcion, precio, duracionDias, activo FROM PaqueteTuristico ORDER BY nombre");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Paquetes Turísticos - Hotel Yokoso";

$contenido_principal = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-rojo fw-bold">Paquetes Turísticos</h2>
    <a href="crear_paquete.php" class="btn btn-dark btn-lg shadow-lg px-5 position-relative overflow-hidden">
        <i class="fas fa-plus me-2"></i>Nuevo Paquete
    </a>
</div>

<div class="row g-4">
    ' . (empty($paquetes) ? '
    <div class="col-12">
        <div class="text-center py-5 text-muted">
            <i class="fas fa-suitcase fa-4x mb-3"></i>
            <h5>No hay paquetes turísticos registrados</h5>
        </div>
    </div>' : '') . '

    ' . implode('', array_map(function($p) {
        $badge = $p['activo'] 
            ? '<span class="badge bg-success">Activo</span>' 
            : '<span class="badge bg-secondary">Inactivo</span>';
        return '
        <div class="col-md-6 col-lg-12 col-xl-6">
            <div class="card h-100 shadow-sm border-0 hover-lift">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">' . htmlspecialchars($p['nombre']) . '</h5>
                        ' . $badge . '
                    </div>
                    <p class="text-muted small"><i class="fas fa-clock me-1"></i>' . $p['duracionDias'] . ' días</p>
                    <p class="card-text flex-grow-1">' . htmlspecialchars($p['descripcion']) . '</p>
                    <div class="d-flex justify-content-between align-items-end mt-3">
                        <h4 class="text-rojo fw-bold mb-0">Bs. ' . number_format($p['precio'], 2) . '</h4>
                        <div>
                            <a href="editar_paquete.php?id=' . $p['idPaquete'] . '" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="eliminar(' . $p['idPaquete'] . ')" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }, $paquetes)) . '
</div>

<!-- Modal eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmar eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        ¿Eliminar este paquete turístico? Esta acción no se puede deshacer.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="" id="confirmDelete" class="btn btn-danger">Eliminar</a>
      </div>
    </div>
  </div>
</div>

<script>
function eliminar(id) {
    document.getElementById("confirmDelete").href = "eliminar_paquete.php?id=" + id;
    new bootstrap.Modal(document.getElementById("deleteModal")).show();
}
</script>
';

include 'plantilla_admin.php';
?>