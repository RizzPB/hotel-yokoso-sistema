<?php
// public/vistas/admin/panel_admin.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$titulo_pagina = "Panel Administrador - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <h2 class="text-rojo fw-bold">Panel de Administrador</h2>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <p>¡Hola! Bienvenido a tu panel de administración.</p>
            <p>Aquí puedes gestionar todos los aspectos del hotel.</p>

            <div class="row">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-hotel card-icon mb-3"></i>
                            <h5 class="card-title">Habitaciones</h5>
                            <p class="card-text">Ver, crear, editar o eliminar habitaciones.</p>
                            <a href="ver_habitaciones.php" class="btn btn-admin">Ir a Habitaciones</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-user-tie card-icon mb-3"></i>
                            <h5 class="card-title">Empleados</h5>
                            <p class="card-text">Registrar, editar o eliminar empleados.</p>
                            <a href="ver_empleados.php" class="btn btn-admin">Ir a Empleados</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-users card-icon mb-3"></i>
                            <h5 class="card-title">Huéspedes</h5>
                            <p class="card-text">Ver, registrar, editar o eliminar huéspedes.</p>
                            <a href="ver_huespedes.php" class="btn btn-admin">Ir a Huéspedes</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-suitcase card-icon mb-3"></i>
                            <h5 class="card-title">Paquetes Turísticos</h5>
                            <p class="card-text">Ver, crear, editar o eliminar paquetes turísticos.</p>
                            <a href="ver_paquetes.php" class="btn btn-admin">Ir a Paquetes</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="../../logout.php" class="btn btn-outline-danger">Cerrar Sesión</a>
            </div>
        </div>
    </div>
';

include 'plantilla_admin.php';
?>