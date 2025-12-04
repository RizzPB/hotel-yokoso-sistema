<?php
// public/admin/ver_huespedes.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$current_page = 'huespedes';

// ✅ Traemos TODOS los huéspedes (el filtrado lo hará JS en el cliente)
$stmt = $pdo->prepare("SELECT idHuesped, nombre, apellido, tipoDocumento, nroDocumento, email, telefono, activo FROM Huesped ORDER BY apellido, nombre");
$stmt->execute();
// Fetch de huéspedes 
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Huéspedes - Hotel Yokoso";
?>

<!-- ✨ Script para búsqueda y orden en tiempo real -->
<script>
//evento para filtrar y ordenar huéspedes 
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('buscadorHuespedes');
    const orderSelect = document.getElementById('ordenHuespedes');
    const cards = document.querySelectorAll('.huesped-card');

    // Obtener todos los datos de los huéspedes (almacenados en data-*)
    const huespedesData = Array.from(cards).map(card => ({
        element: card,
        nombre: card.dataset.nombre,
        apellido: card.dataset.apellido,
        id: parseInt(card.dataset.id)
    }));

    // Función para aplicar filtros y orden
    function aplicarFiltrosYOrden() {
        const searchTerm = (searchInput.value || '').toLowerCase().trim();
        const orden = orderSelect.value;

        // Filtrar por búsqueda
        let resultados = huespedesData.filter(h => {
            const nombreCompleto = (h.nombre + ' ' + h.apellido).toLowerCase();
            return nombreCompleto.includes(searchTerm);
        });

        // Ordenar
        if (orden === 'nuevo') {
            resultados.sort((a, b) => b.id - a.id); // más nuevo primero
        } else if (orden === 'antiguo') {
            resultados.sort((a, b) => a.id - b.id); // más antiguo primero
        } else if (orden === 'az') {
            resultados.sort((a, b) => 
                (a.apellido + a.nombre).localeCompare(b.apellido + b.nombre)
            );
        } else if (orden === 'za') {
            resultados.sort((a, b) => 
                (b.apellido + b.nombre).localeCompare(a.apellido + a.nombre)
            );
        }

        // Resetear contenedor
        const container = document.getElementById('huespedesGrid');
        container.innerHTML = '';

        if (resultados.length === 0) {
            container.innerHTML = '<div class="col-12 text-center py-5 text-muted"><i class="fas fa-users fa-4x mb-3"></i><h5>No se encontraron huéspedes</h5></div>';
        } else {
            resultados.forEach(h => container.appendChild(h.element));
        }
    }

    // Eventos
    searchInput?.addEventListener('input', aplicarFiltrosYOrden);
    orderSelect?.addEventListener('change', aplicarFiltrosYOrden);
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

<!-- 🔍 BARRA DE BÚSQUEDA Y ORDEN -->
<div class="row mb-4 g-3">
    <div class="col-md-8">
        <label for="buscadorHuespedes" class="form-label fw-bold">Buscar huésped</label>
        <input type="text" 
               id="buscadorHuespedes" 
               class="form-control form-control-lg rounded-pill" 
               placeholder="Escribe nombre o apellido...">
    </div>
    <div class="col-md-4">
        <label for="ordenHuespedes" class="form-label fw-bold">Ordenar por</label>
        <select id="ordenHuespedes" class="form-select form-select-lg rounded-pill">
            <option value="az">Apellido: A → Z</option>
            <option value="za">Apellido: Z → A</option>
            <option value="nuevo">Más reciente primero</option>
            <option value="antiguo">Más antiguo primero</option>
        </select>
    </div>
</div>

<!-- 🧍‍♂️ LISTA DE HUÉSPEDES -->
<div class="row g-4" id="huespedesGrid">
    ' . (empty($huespedes) ? '<div class="col-12 text-center py-5 text-muted"><i class="fas fa-users fa-4x mb-3"></i><h5>No hay huéspedes</h5></div>' : '') . '
    ' . implode('', array_map(function($h) {
        $badge = $h['activo'] 
            ? '<span class="badge bg-success fs-6">Activo</span>' 
            : '<span class="badge bg-danger fs-6">Inactivo</span>';

        // ✅ Añadimos data-* para que JS pueda filtrar y ordenar
        return '
        <div class="col-md-6 col-lg-4 huesped-card" 
             data-id="' . (int)$h['idHuesped'] . '"
             data-nombre="' . htmlspecialchars($h['nombre']) . '"
             data-apellido="' . htmlspecialchars($h['apellido']) . '">
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