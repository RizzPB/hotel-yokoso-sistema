<?php
// public/admin/panel_admin.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// ← RESALTA "INICIO" EN EL SIDEBAR DEL ADMIN
$current_page = 'panel_admin';

$titulo_pagina = "Panel Administrador - Hotel Yokoso";

$contenido_principal = '

<div class="row g-4">
    <div class="col-12">
        <div class="bg-white rounded-4 shadow p-4 border-start border-5 border-rojo-quemado">
            <h4 class="mb-2">
                <i class="fas fa-crown text-warning me-2"></i>
                ¡Hola, <strong>' . htmlspecialchars($_SESSION['nombreEmpleado'] ?? 'Administrador') . '</strong>!
            </h4>
            <p class="text-muted fs-5 mb-0">Tienes control total del Hotel Yokoso</p>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- HABITACIONES -->
    <div class="col-md-6 col-lg-4">
        <a href="ver_habitaciones.php" class="card text-white bg-gradient-rojo border-0 shadow-lg h-100 text-decoration-none hover-lift">
            <div class="card-body text-center py-5">
                <i class="fas fa-hotel fa-4x mb-4"></i>
                <h4 class="mb-2">Habitaciones</h4>
                <p class="mb-0 opacity-90">Gestionar todas las habitaciones del hotel</p>
            </div>
        </a>
    </div>

    <!-- EMPLEADOS -->
    <div class="col-md-6 col-lg-4">
        <a href="ver_empleados.php" class="card text-white bg-gradient-morado border-0 shadow-lg h-100 text-decoration-none hover-lift">
            <div class="card-body text-center py-5">
                <i class="fas fa-user-tie fa-4x mb-4"></i>
                <h4 class="mb-2">Empleados</h4>
                <p class="mb-0 opacity-90">Registrar y administrar personal</p>
            </div>
        </a>
    </div>

    <!-- HUÉSPEDES -->
    <div class="col-md-6 col-lg-4">
        <a href="ver_huespedes.php" class="card text-white bg-gradient-verde border-0 shadow-lg h-100 text-decoration-none hover-lift">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-4x mb-4"></i>
                <h4 class="mb-2">Huéspedes</h4>
                <p class="mb-0 opacity-90">Ver y gestionar todos los huéspedes</p>
            </div>
        </a>
    </div>

    <!-- PAQUETES TURÍSTICOS -->
    <div class="col-md-6 col-lg-4">
        <a href="ver_paquetes.php" class="card text-white bg-gradient-azul border-0 shadow-lg h-100 text-decoration-none hover-lift">
            <div class="card-body text-center py-5">
                <i class="fas fa-suitcase-rolling fa-4x mb-4"></i>
                <h4 class="mb-2">Paquetes Turísticos</h4>
                <p class="mb-0 opacity-90">Crear y editar ofertas especiales</p>
            </div>
        </a>
    </div>

    <!-- RESERVAS (ADMIN VE TODAS) -->
    <div class="col-md-6 col-lg-4">
        <a href="ver_reservas.php" class="card text-white bg-gradient-naranja border-0 shadow-lg h-100 text-decoration-none hover-lift">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-check fa-4x mb-4"></i>
                <h4 class="mb-2">Reservas</h4>
                <p class="mb-0 opacity-90">Control total de todas las reservas</p>
            </div>
        </a>
    </div>

    <!-- REPORTES (EXTRA PARA ADMIN) -->
    <div class="col-md-6 col-lg-4">
        <a href="reportes.php" class="card text-white bg-gradient-cyan border-0 shadow-lg h-100 text-decoration-none hover-lift">
            <div class="card-body text-center py-5">
                <i class="fas fa-chart-bar fa-4x mb-4"></i>
                <h4 class="mb-2">Reportes</h4>
                <p class="mb-0 opacity-90">Estadísticas e ingresos del hotel</p>
            </div>
        </a>
    </div>
</div>


<!-- Estilos específicos para inicio menu! -->
<style>
    .bg-gradient-rojo { background: linear-gradient(135deg, #9b2226, #dc2626) !important; }
    .bg-gradient-morado { background: linear-gradient(135deg, #d29c38ff, #c8a138ff) !important; }
    .bg-gradient-verde { background: linear-gradient(135deg, #0d0d0dff, #343735ff) !important; }
    .bg-gradient-azul { background: linear-gradient(135deg, #edce1fff, #f7b40bff) !important; }
    .bg-gradient-naranja { background: linear-gradient(135deg, #070707ff, #383533ff) !important; }
    .bg-gradient-cyan { background: linear-gradient(135deg, #a60e0eff, #ed4a38ff) !important; }

    .hover-lift {
        transition: all 0.4s ease;
        border-radius: 20px !important;
    }
    .hover-lift:hover {
        transform: translateY(-15px) scale(1.03);
        box-shadow: 0 25px 50px rgba(0,0,0,0.3) !important;
    }
    .border-rojo-quemado { border-color: var(--color-rojo-quemado) !important; }
</style>
';

include 'plantilla_admin.php';
?>