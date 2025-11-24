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
    <p class="fs-5">¡Hola <strong>' . htmlspecialchars($_SESSION['nombreEmpleado'] ?? 'Recepcionista') . '</strong>! Bienvenido a tu panel de trabajo.</p>
    <p class="text-muted">¿Qué deseas hacer hoy?</p>

    <div class="row g-4 mt-3">
        <div class="col-md-6">
            <a href="registrar_huesped.php" class="btn btn-rojo btn-lg w-100 py-4 shadow-sm d-flex align-items-center justify-content-center text-white hover-text-mostaza transition">
                <i class="fas fa-user-plus fa-2x me-3"></i>
                <span class="fs-5 fw-bold">Registrar Huésped</span>
            </a>
        </div>
        <div class="col-md-6">
            <a href="ver_huespedes.php" class="btn btn-rojo btn-lg w-100 py-4 shadow-sm d-flex align-items-center justify-content-center text-white hover-text-mostaza transition">
                <i class="fas fa-users fa-2x me-3"></i>
                <span class="fs-5 fw-bold">Ver Huéspedes</span>
            </a>
        </div>
        <div class="col-md-6">
            <a href="crear_reserva.php" class="btn btn-rojo btn-lg w-100 py-4 shadow-sm d-flex align-items-center justify-content-center text-white hover-text-mostaza transition">
                <i class="fas fa-calendar-plus fa-2x me-3"></i>
                <span class="fs-5 fw-bold">Hacer Reserva</span>
            </a>
        </div>
        <div class="col-md-6">
            <a href="ver_reservas.php" class="btn btn-rojo btn-lg w-100 py-4 shadow-sm d-flex align-items-center justify-content-center text-white hover-text-mostaza transition">
                <i class="fas fa-calendar-check fa-2x me-3"></i>
                <span class="fs-5 fw-bold">Ver Reservas</span>
            </a>
        </div>
    </div>
';

include 'plantilla_recepcionista.php';
?>