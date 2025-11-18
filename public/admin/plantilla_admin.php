<?php
// public/vistas/admin/plantilla_admin.php

// Este archivo no se accede directamente
if (!defined('ACCESO_PERMITIDO')) {
    exit('Acceso directo no permitido.');
}

// Verificar si está logueado y es admin
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?? 'Panel Administrador - Hotel Yokoso' ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .navbar {
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-family: var(--font-heading);
            font-weight: 600;
        }

        .navbar-nav .nav-link {
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .navbar-nav .nav-link:hover {
            color: var(--color-mostaza) !important;
            transform: translateY(-2px);
        }

        .sidebar {
            position: fixed;
            top: 56px; /* Altura del navbar */
            left: 0;
            width: 250px;
            height: calc(100vh - 56px);
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
            padding: 20px;
            z-index: 1000;
            overflow-y: auto; /* Permite desplazamiento si hay muchos elementos */
        }

        .sidebar .nav-link {
            padding: 10px 15px;
            margin-bottom: 5px;
            border-radius: 5px;
            display: block;
            text-decoration: none;
            color: #212529;
        }

        .sidebar .nav-link:hover {
            background-color: #e9ecef;
            color: #212529;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            min-height: calc(100vh - 56px - 50px); /* Altura navbar + footer */
        }

        .content-header {
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pendiente { background-color: #ffc107; color: black; }
        .status-confirmada { background-color: #28a745; color: white; }
        .status-cancelada { background-color: #dc3545; color: white; }
        .status-finalizada { background-color: #6c757d; color: white; }

        .footer-wrapper {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 999;
        }

        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important;
        }
    </style>
</head>
<body>
    <!-- Navbar superior fijo -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--color-rojo-quemado);">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../../index.php">
                <img src="../../assets/img/empresaLogoYokoso.png" alt="Logo Hotel Yokoso" width="40" class="me-2">
                <span class="fw-bold">Hotel Yokoso</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <?php
                        $nombreEmpleado = $_SESSION['nombreEmpleado'] ?? '';
                        $apellidoEmpleado = $_SESSION['apellidoEmpleado'] ?? '';
                        $rol = $_SESSION['rol'] ?? 'huésped';
                        $nombreMostrar = $nombreEmpleado ? "$rol $nombreEmpleado $apellidoEmpleado" : $rol;
                        ?>
                        <span class="nav-link text-white me-3" style="font-weight: 600;">Hola, <?= htmlspecialchars($nombreMostrar) ?></span>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="../../logout.php" class="btn btn-warning btn-sm text-dark">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar fijo -->
    <div class="sidebar">
        <h5 class="mb-3">Menú Administrador</h5>
        <hr>
        <div class="nav flex-column">
            <a href="panel_admin.php" class="nav-link active">
                <i class="fas fa-home me-2"></i>Inicio
            </a>
            <a href="ver_habitaciones.php" class="nav-link">
                <i class="fas fa-hotel me-2"></i>Gestionar Habitaciones
            </a>
            <a href="ver_empleados.php" class="nav-link">
                <i class="fas fa-user-tie me-2"></i>Gestionar Empleados
            </a>
            <a href="ver_huespedes.php" class="nav-link">
                <i class="fas fa-users me-2"></i>Gestionar Huéspedes
            </a>
            <a href="ver_paquetes.php" class="nav-link">
                <i class="fas fa-suitcase me-2"></i>Gestionar Paquetes Turísticos
            </a>
            <a href="#" class="nav-link disabled">
                <i class="fas fa-chart-line me-2"></i>Reportes e Informes
            </a>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <?= $contenido_principal ?? '<p>Contenido no definido.</p>' ?>
    </div>

    <!-- Footer fijo -->
    <footer class="footer-wrapper bg-dark text-white py-2">
        <div class="container text-center">
            <small>&copy; 2025 Hotel Yokoso. Todos los derechos reservados.</small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>