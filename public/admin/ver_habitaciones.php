<?php
// public/admin/ver_habitaciones.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Conexión a la base de datos
$current_page = 'habitaciones';
require_once __DIR__ . '/../../config/database.php';

// ✅ Traemos todas las habitaciones con su estado calculado
$stmt = $pdo->prepare("
    SELECT 
        h.idHabitacion, 
        h.numero, 
        h.tipo, 
        h.precioNoche,
        h.estado AS estado_manual,
        CASE 
            WHEN h.estado = 'mantenimiento' THEN 'mantenimiento'
            WHEN EXISTS (
                SELECT 1 FROM Reserva r
                JOIN ReservaHabitacion rh ON r.idReserva = rh.idReserva
                WHERE rh.idHabitacion = h.idHabitacion
                  AND r.estado IN ('confirmada', 'ocupada')
                  AND CURDATE() BETWEEN r.fechaInicio AND r.fechaFin
            ) THEN 'ocupada'
            ELSE 'disponible'
        END AS estado_final
    FROM Habitacion h
    ORDER BY CAST(h.numero AS UNSIGNED)
");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Habitaciones - Hotel Yokoso";

// Tipos de habitación (para el filtro y para evitar listas duras)
$tipos_habitacion = ['simple', 'doble', 'triple', 'cuadruple', 'familiar', 'suite', 'de sal'];

// Función para obtener color y texto del estado
function obtenerEstadoInfo($estado) {
    switch ($estado) {
        case 'ocupada':
            return ['color' => 'warning', 'texto' => 'Ocupada'];
        case 'mantenimiento':
            return ['color' => 'danger', 'texto' => 'Mantenimiento'];
        case 'disponible':
        default:
            return ['color' => 'success', 'texto' => 'Disponible'];
    }
}
?>

<!-- Script para filtrar sin recargar -->
<script>
// Filtrado de habitaciones por estado y tipo
document.addEventListener('DOMContentLoaded', function() {
    const estadoFilter = document.getElementById('filtroEstado');
    const tipoFilter = document.getElementById('filtroTipo');
    //filtrar las tarjetas de habitaciones
    const cards = document.querySelectorAll('.habitacion-card');

    //funcion para aplicar los filtros
    function aplicarFiltros() {
        //parametros seleccionados 
        const estadoSel = estadoFilter.value;
        const tipoSel = tipoFilter.value;

        //cards de habitaciones donde se aplicaran los filtros
        cards.forEach(card => {
            const estadoCard = card.dataset.estado;
            const tipoCard = card.dataset.tipo;

            const coincideEstado = !estadoSel || estadoCard === estadoSel;
            const coincideTipo = !tipoSel || tipoCard === tipoSel;

            card.style.display = (coincideEstado && coincideTipo) ? 'block' : 'none';
        });
    }

    estadoFilter?.addEventListener('change', aplicarFiltros);
    tipoFilter?.addEventListener('change', aplicarFiltros);
});
</script>

<?php
$contenido_principal = '
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-rojo fw-bold">
            Gestión de Habitaciones
        </h2>
        <a href="crear_habitacion.php" class="btn btn-yokoso btn-lg rounded-pill px-5 shadow-lg">
             + Nueva Habitación
        </a>
    </div>

    <!-- 🔍 BARRA DE FILTROS -->
    <div class="row mb-4 g-3">
        <div class="col-md-6">
            <label for="filtroEstado" class="form-label fw-bold">Filtrar por Estado</label>
            <select id="filtroEstado" class="form-select form-select-lg rounded-pill">
                <option value="">Todos los estados</option>
                <option value="disponible">Disponible</option>
                <option value="ocupada">Ocupada</option>
                <option value="mantenimiento">En Mantenimiento</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="filtroTipo" class="form-label fw-bold">Filtrar por Tipo</label>
            <select id="filtroTipo" class="form-select form-select-lg rounded-pill">
                <option value="">Todos los tipos</option>
                <option value="simple">Simple</option>
                <option value="doble">Doble</option>
                <option value="triple">Triple</option>
                <option value="cuadruple">Cuádruple</option>
                <option value="familiar">Familiar</option>
                <option value="suite">Suite de Sal</option>
                
            </select>
        </div>
    </div>

    <!-- ÁREA FIJA -->
    <div class="row justify-content-center">
        <div class="col-xl-11 col-xxl-10">

            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5">

                    <div class="row g-4 habitaciones-grid" id="habitacionesGrid">
                        ' . (empty($habitaciones) ? '
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-bed fa-5x text-muted mb-4"></i>
                            <h4 class="text-muted">No hay habitaciones registradas</h4>
                        </div>' : '') . '

                        ' . implode('', array_map(function($h) {
                            $estadoInfo = obtenerEstadoInfo($h['estado_final']);
                            $color = $estadoInfo['color'];
                            $texto = $estadoInfo['texto'];

                            // Añadimos dataset para el filtro JS
                            return '
                            <div class="col-md-6 col-lg-4 habitacion-card" 
                                 data-estado="' . htmlspecialchars($h['estado_final']) . '" 
                                 data-tipo="' . htmlspecialchars($h['tipo']) . '">
                                <div class="card h-100 shadow-sm border-0 hover-lift position-relative">
                                    <div class="card-body text-center py-5">
                                        <h1 class="display-4 fw-bold text-rojo mb-3">' . htmlspecialchars($h['numero']) . '</h1>
                                        <h5 class="text-uppercase text-muted">' . htmlspecialchars($h['tipo']) . '</h5>
                                        <h4 class="text-success fw-bold mt-3">Bs. ' . number_format($h['precioNoche'], 2) . ' / noche</h4>
                                        <span class="badge bg-' . $color . ' position-absolute top-0 end-0 mt-3 me-3 fs-6">' . $texto . '</span>
                                        <div class="mt-4">
                                            <a href="editar_habitacion.php?id=' . $h['idHabitacion'] . '" class="btn btn-outline-primary">
                                                Editar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                        }, $habitaciones)) . '
                    </div>

                </div>
            </div>

            <!-- Contador de habitaciones al final -->
            <div class="text-center mt-4">
                <h5 class="text-muted">
                    Total: <strong class="text-rojo">' . count($habitaciones) . '</strong> habitaciones registradas
                </h5>
                <p class="text-muted mt-2">
                    Disponibles: <strong class="text-success">' . 
                    count(array_filter($habitaciones, fn($h) => $h['estado_final'] === 'disponible')) . 
                    '</strong> |
                    Ocupadas: <strong class="text-warning">' . 
                    count(array_filter($habitaciones, fn($h) => $h['estado_final'] === 'ocupada')) . 
                    '</strong> |
                    En mantenimiento: <strong class="text-danger">' . 
                    count(array_filter($habitaciones, fn($h) => $h['estado_final'] === 'mantenimiento')) . 
                    '</strong>
                </p>
            </div>

        </div>
    </div>
</div>
';

include 'plantilla_admin.php';
?>