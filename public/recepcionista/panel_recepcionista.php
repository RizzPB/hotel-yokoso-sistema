<?php
// public/vistas/recepcionista/panel_recepcionista.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

$titulo_pagina = "Panel Recepcionista - Hotel Yokoso";

$contenido_principal = '
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-rojo fw-bold">Panel de Recepcionista</h2>
    </div>

    <p>¡Hola! Bienvenido a tu panel de trabajo.</p>
    <p>¿Qué quieres hacer?</p>

    <div class="row">
        <div class="col-md-6">
            <a href="registrar_huesped.php" class="btn btn-rojo btn-lg w-100 mb-3">
                <i class="fas fa-user-plus me-2"></i>Registrar Huésped
            </a>
        </div>
        <div class="col-md-6">
            <a href="ver_huespedes.php" class="btn btn-rojo btn-lg w-100 mb-3">
                <i class="fas fa-users me-2"></i>Ver Huéspedes
            </a>
        </div>
        <div class="col-md-6">
            <a href="crear_reserva.php" class="btn btn-rojo btn-lg w-100 mb-3">
                <i class="fas fa-calendar-plus me-2"></i>Hacer Reserva
            </a>
        </div>
        <div class="col-md-6">
            <a href="ver_reservas.php" class="btn btn-rojo btn-lg w-100 mb-3">
                <i class="fas fa-calendar-check me-2"></i>Ver Reservas
            </a>
        </div>
    </div>

    <div class="mt-4">
        <a href="../../logout.php" class="btn btn-outline-danger">Cerrar Sesión</a>
    </div>
';

include 'plantilla_recepcionista.php';
?>