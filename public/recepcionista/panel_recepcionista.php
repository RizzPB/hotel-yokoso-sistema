<?php
// public/recepcionista/panel_recepcionista.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'panel_recepcionista';

$titulo_pagina = "Panel Recepcionista - Hotel Yokoso";

$contenido_principal = '

    <div class="row g-4">
        <div class="col-12">
            <div class="bg-white rounded-4 shadow p-4 border-start border-5 border-dark">
                <h4 class="mb-2">
                    <i class="fas fa-crown text-warning me-2"></i>
                    ¡Hola, <strong>' . htmlspecialchars($_SESSION['nombreEmpleado'] ?? 'Recepcionista') . '</strong>!
                </h4>
                <p class="text-muted fs-5 mb-0">Bienvenido a tu panel de trabajo. <br> ¿Qué deseas hacer hoy?</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
    <!-- registro huesped -->
    <div class="col-md-6 ">
        <a href="registrar_huesped.php" class="card text-white bg-gradient-rojo border-0 shadow-lg h-70 text-decoration-none hover-lift">
            <div class="card-body text-center py-5">
            <i class="fas fa-user-plus fa-2x me-3"></i>
                <span class="fs-5 fw-bold">Registrar Huésped</span>
            </div>
        </a>
    </div>

    <!-- ver huespedes -->
    <div class="col-md-6 ">
        <a href="ver_huespedes.php" class="card text-white bg-gradient-morado border-0 shadow-lg h-70 text-decoration-none hover-lift">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-2x me-3"></i>
                <span class="fs-5 fw-bold">Ver Huéspedes</span>
            </div>
        </a>
    </div>

    <!-- HUÉSPEDES -->
    <div class="col-md-6 ">
        <a href="crear_reserva.php" class="card text-white bg-gradient-verde border-0 shadow-lg h-70 text-decoration-none hover-lift">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-plus fa-2x me-3"></i>
                <span class="fs-5 fw-bold">Hacer Reserva</span>
            </div>
        </a>
    </div>

    <!-- PAQUETES TURÍSTICOS -->
    <div class="col-md-6 ">
        <a href="ver_reservas.php" class="card text-white bg-gradient-azul border-0 shadow-lg h-70 text-decoration-none hover-lift">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-check fa-2x me-3"></i>
                <span class="fs-5 fw-bold">Ver Reservas</span>
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

include 'plantilla_recepcionista.php';
?>