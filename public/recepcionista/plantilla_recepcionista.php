<?php
// public/recepcionista/plantilla_recepcionista.php

if (!defined('ACCESO_PERMITIDO')) {
    exit('Acceso directo no permitido.');
}

// Para resaltar el menú activo
$current_page = $current_page ?? basename($_SERVER['PHP_SELF'], '.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?? 'Panel Recepcionista - Hotel Yokoso' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        :root {
            --navbar-height: 70px;
            --sidebar-width: 280px;
            --color-activo: var(--color-rojo-quemado);
        }

        /* Navbar igual que index.php */
        .navbar {
            height: var(--navbar-height) !important;
            background-color: var(--color-rojo-quemado) !important;
            padding: 0.5rem 1rem !important;
        }
        .navbar-brand img { height: 55px; }
        .navbar-brand span { font-size: 1.4rem; font-weight: 700; }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--navbar-height));
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            padding: 1.5rem 1rem;
            z-index: 1040;
            overflow-y: auto;
            transition: transform 0.35s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: #333 !important;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .sidebar .nav-link i {
            width: 32px;
            font-size: 1.3rem;
            text-align: center;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--color-activo) !important;
            color: white !important;
            transform: translateX(5px);
        }

        /* Contenido */
        .content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            padding-top: calc(var(--navbar-height) + 1.5rem);
            padding-bottom: 100px;
            min-height: 100vh;
            transition: margin-left 0.35s ease;
        }

        /* Footer */
        .footer-wrapper {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: 60px;
            background: #212529;
            color: white;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* MÓVIL */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .content {
                margin-left: 0 !important;
            }
            .sidebar-overlay {
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.6);
                z-index: 1030;
                opacity: 0;
                visibility: hidden;
                transition: all 0.35s;
            }
            .sidebar-overlay.show {
                opacity: 1;
                visibility: visible;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../../index.php">
                <img src="../../assets/img/empresaLogoYokoso.png" alt="Logo" class="me-3">
                <span class="d-none d-md-block">Hotel Yokoso</span>
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3 fw-semibold">
                    Hola, <?= htmlspecialchars($_SESSION['nombreEmpleado'] ?? 'Recepcionista') ?>
                </span>
                <a href="../../logout.php" class="btn btn-warning btn-sm">Cerrar Sesion</a>
            </div>
        </div>
    </nav>

    <!-- Botón menú móvil -->
    <div class="d-lg-none position-fixed start-0 ms-3" style="top: 78px; z-index: 1050;">
        <button class="btn btn-dark rounded-circle shadow-lg p-3" id="menuToggle">
            <i class="fas fa-home fa-lg"></i>
        </button>
    </div>

    <!-- Overlay -->
    <div class="sidebar-overlay" id="overlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="nav flex-column mt-3">
            <a href="panel_recepcionista.php" class="nav-link <?= $current_page == 'panel_recepcionista' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> <span>Inicio</span>
            </a>
            <a href="registrar_huesped.php" class="nav-link <?= $current_page == 'registrar_huesped' ? 'active' : '' ?>">
                <i class="fas fa-user-plus"></i> <span>Registrar Huésped</span>
            </a>
            <a href="ver_huespedes.php" class="nav-link <?= $current_page == 'ver_huespedes' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> <span>Ver Huéspedes</span>
            </a>
            <a href="crear_reserva.php" class="nav-link <?= $current_page == 'crear_reserva' ? 'active' : '' ?>">
                <i class="fas fa-calendar-plus"></i> <span>Hacer Reserva</span>
            </a>
            <a href="ver_reservas.php" class="nav-link <?= $current_page == 'ver_reservas' ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i> <span>Ver Reservas</span>
            </a>
        </div>
    </div>

    <!-- Contenido -->
    <div class="content">
        <?= $contenido_principal ?? '' ?>
    </div>

    <!-- Footer -->
    <footer class="footer-wrapper">
        <div class="container text-center">
            <small>© 2025 Hotel Yokoso. Todos los derechos reservados.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('overlay').classList.toggle('show');
        });
        document.getElementById('overlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
            this.classList.remove('show');
        });
    </script>
</body>
</html>