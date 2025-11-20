<?php
// public/admin/ver_habitaciones.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'habitaciones';
require_once __DIR__ . '/../../config/database.php';

$stmt = $pdo->prepare("SELECT idHabitacion, numero, tipo, precioNoche, estado FROM Habitacion ORDER BY CAST(numero AS UNSIGNED)");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Habitaciones - Hotel Yokoso";

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

    <!-- ÁREA FIJA -->
    <div class="row justify-content-center">
        <div class="col-xl-11 col-xxl-10"> <!-- tamaño -->

            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5">

                    <div class="row g-4">
                        ' . (empty($habitaciones) ? '
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-bed fa-5x text-muted mb-4"></i>
                            <h4 class="text-muted">No hay habitaciones registradas</h4>
                        </div>' : '') . '

                        ' . implode('', array_map(function($h) {
                            $color = $h['estado'] === 'disponible' ? 'success' : ($h['estado'] === 'ocupada' ? 'warning' : 'secondary');
                            $texto = ucfirst($h['estado']);

                            return '
                            
                            <div class="col-md-6 col-lg-4">
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

            <!-- Contador elegante al final -->
            <div class="text-center mt-4">
                <h5 class="text-muted">
                    Total: <strong class="text-rojo">' . count($habitaciones) . '</strong> habitaciones registradas
                </h5>
            </div>

        </div>
    </div>
</div>
';

include 'plantilla_admin.php';
?>