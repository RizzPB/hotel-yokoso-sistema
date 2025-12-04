<?php
// public/recepcionista/ver_huespedes.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$current_page = 'huespedes';

// Mensajes de sesión
$mensaje_exito = $_SESSION['mensaje_exito'] ?? null;
$mensaje_error = $_SESSION['mensaje_error'] ?? null;
if ($mensaje_exito) unset($_SESSION['mensaje_exito']);
if ($mensaje_error) unset($_SESSION['mensaje_error']);

$stmt = $pdo->prepare("SELECT idHuesped, nombre, apellido, tipoDocumento, nroDocumento, email, telefono, activo FROM Huesped ORDER BY apellido");
$stmt->execute();
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Huéspedes - Hotel Yokoso";
?>

<!-- ✅ Script para búsqueda en tiempo real + modal de eliminación -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Buscador
    const buscador = document.getElementById('buscadorHuespedes');
    const cards = document.querySelectorAll('.huesped-card');

    if (buscador) {
        buscador.addEventListener('input', function() {
            const termino = this.value.toLowerCase().trim();
            let visibles = 0;

            cards.forEach(card => {
                const textoCompleto = card.dataset.busqueda.toLowerCase();
                const mostrar = !termino || textoCompleto.includes(termino);
                card.style.display = mostrar ? 'block' : 'none';
                if (mostrar) visibles++;
            });

            // Actualizar contador si lo tuvieras (opcional)
        });
    }

    // Modal de eliminación
    window.confirmarEliminacion = function(id) {
        document.getElementById('confirmDeleteBtn').href = 'eliminar_huesped.php?id=' + id;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    };
});
</script>

<?php
$contenido_principal = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-rojo fw-bold">Gestión de Huéspedes</h2>
    <a href="crear_huesped.php" class="btn btn-dark btn-lg shadow-lg px-5 position-relative overflow-hidden">
        <i class="fas fa-user-plus me-2"></i>Nuevo Huésped
    </a>
</div>

<!-- 🔍 BUSCADOR EN TIEMPO REAL -->
<div class="row mb-4">
    <div class="col-md-6">
        <label for="buscadorHuespedes" class="form-label fw-bold">Buscar huésped</label>
        <input type="text" 
               id="buscadorHuespedes" 
               class="form-control form-control-lg rounded-pill" 
               placeholder="Ej: María López, DNI 12345678, juan@email.com...">
    </div>
</div>

<!-- ✅ Mensajes de éxito/error -->
' . ($mensaje_exito ? '<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($mensaje_exito) . '
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>' : '') . '

' . ($mensaje_error ? '<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($mensaje_error) . '
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>' : '') . '

<div class="row g-4" id="listaHuespedes">
    ' . (empty($huespedes) ? '<div class="col-12 text-center py-5 text-muted"><i class="fas fa-users fa-4x mb-3"></i><h5>No hay huéspedes</h5></div>' : '') . '
    ' . implode('', array_map(function($h) {
        $badge = $h['activo'] 
            ? '<span class="badge bg-success fs-6">Activo</span>' 
            : '<span class="badge bg-danger fs-6">Inactivo</span>';

        // ✅ Datos para el buscador (todo en uno)
        $textoBusqueda = implode(' ', [
            $h['nombre'],
            $h['apellido'],
            $h['tipoDocumento'],
            $h['nroDocumento'],
            $h['email'] ?? '',
            $h['telefono'] ?? ''
        ]);

        return '
        <div class="col-md-6 col-lg-4 huesped-card" data-busqueda="' . htmlspecialchars($textoBusqueda) . '">
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
                        <a href="editar_huesped.php?id=' . $h['idHuesped'] . '" class="btn btn-outline-primary btn-sm me-1">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <button onclick="confirmarEliminacion(' . $h['idHuesped'] . ')" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>';
    }, $huespedes)) . '
</div>

<!-- ✅ Modal de confirmación integrado -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmar desactivación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        ¿Desactivar este huésped? Esta acción no se puede deshacer, pero los datos se conservarán.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="" id="confirmDeleteBtn" class="btn btn-danger">Desactivar</a>
      </div>
    </div>
  </div>
</div>
';

include 'plantilla_recepcionista.php';
?>