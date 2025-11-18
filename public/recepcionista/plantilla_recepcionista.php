<?php
// public/vistas/recepcionista/plantilla_recepcionista.php

// Este archivo no se accede directamente
if (!defined('ACCESO_PERMITIDO')) {
    exit('Acceso directo no permitido.');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?? 'Panel Recepcionista - Hotel Yokoso' ?></title>
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
            overflow-y: auto;
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
    
        <hr>
        <div class="nav flex-column">
            <a href="panel_recepcionista.php" class="nav-link">
                <i class="fas fa-home me-2"></i>Inicio
            </a>
            <a href="registrar_huesped.php" class="nav-link">
                <i class="fas fa-user-plus me-2"></i>Registrar Huésped
            </a>
            <a href="ver_huespedes.php" class="nav-link">
                <i class="fas fa-users me-2"></i>Ver Huéspedes
            </a>
            <a href="crear_reserva.php" class="nav-link">
                <i class="fas fa-calendar-plus me-2"></i>Hacer Reserva
            </a>
            <a href="ver_reservas.php" class="nav-link">
                <i class="fas fa-calendar-check me-2"></i>Ver Reservas
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