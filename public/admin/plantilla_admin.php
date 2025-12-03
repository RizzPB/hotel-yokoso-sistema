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
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="../../assets/css/style.css">
<style>
    :root {
        --navbar-height: 80px;
        --sidebar-width: 280px;
        --color-activo: var(--color-rojo-quemado);
    }

    /* Navbar */
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

    /* Submenús */
    .sidebar .collapse .nav-link {
        padding-left: 30px !important;
        font-size: 0.95rem;
        color: #555 !important;
        margin-bottom: 4px;
    }
    .sidebar .collapse .nav-link:hover,
    .sidebar .collapse .nav-link.active {
        color: var(--color-activo) !important;
        background: #f1f1f1 !important;
    }

    /* Flecha de submenu */
    .sidebar .nav-link i.ms-auto {
        margin-left: auto;
        transition: transform 0.2s;
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

    /* MÓVIL: Sidebar deslizable */
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

    /* Flecha girada cuando el menú está abierto */
    .sidebar .collapse.show + .nav-link i.ms-auto,
    .sidebar .nav-link[data-bs-toggle="collapse"].active i.ms-auto {
        transform: rotate(180deg) !important;
    }
</style>
</head>
<body>
    <!-- Navbar superior fijo -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--color-rojo-quemado);">
        <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="../../index.php">
            <img src="../../assets/img/empresaLogoYokoso.png" alt="Logo Hotel Yokoso" class="me-3" style="height: 55px;">
            <span class="fw-bold d-none d-md-block">Hotel Yokoso</span>
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
                        <a href="../../logout.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-door-open me-1"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Botón flotante para abrir menú en móvil -->
    <div class="d-lg-none position-fixed start-0 ms-3" style="top: 78px; z-index: 1050;">
        <button class="btn btn-dark rounded-circle shadow-lg p-3" id="menuToggle" style="width: 58px; height: 58px;">
            <i class="fas fa-home fa-lg"></i>
        </button>
    </div>

    <!-- Overlay oscuro -->
    <div class="sidebar-overlay" id="overlay"></div>

    <!-- SIDEBAR CON SUBMENÚS PERSISTENTES -->
    <div class="sidebar" id="sidebar">
        <div class="nav flex-column mt-3">

            <!-- INICIO -->
            <a href="panel_admin.php" class="nav-link <?= ($current_page ?? '') == 'panel_admin' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> <span>Inicio</span>
            </a>

            <!-- HABITACIONES -->
            <div class="nav-item">
                <a class="nav-link <?= in_array($current_page ?? '', ['ver_habitaciones', 'crear_habitacion', 'editar_habitacion']) ? 'active' : '' ?>" 
                   data-bs-toggle="collapse" href="#submenuHabitaciones" role="button">
                    <i class="fas fa-hotel"></i> <span>Gestionar Habitaciones</span>
                    <i class="fas fa-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse <?= in_array($current_page ?? '', ['ver_habitaciones', 'crear_habitacion', 'editar_habitacion']) ? 'show' : '' ?>" id="submenuHabitaciones">
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'ver_habitaciones' ? 'active' : '' ?>" href="ver_habitaciones.php">Listar Habitaciones</a>
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'crear_habitacion' ? 'active' : '' ?>" href="crear_habitacion.php">Crear Habitación</a>
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'editar_habitacion' ? 'active' : '' ?>" href="editar_habitacion.php">Editar Habitación</a>
                </div>
            </div>

            <!-- EMPLEADOS -->
            <div class="nav-item">
                <a class="nav-link <?= in_array($current_page ?? '', ['ver_empleados', 'crear_empleado', 'editar_empleado']) ? 'active' : '' ?>" 
                   data-bs-toggle="collapse" href="#submenuEmpleados" role="button">
                    <i class="fas fa-user-tie"></i> <span>Gestionar Empleados</span>
                    <i class="fas fa-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse <?= in_array($current_page ?? '', ['ver_empleados', 'crear_empleado', 'editar_empleado']) ? 'show' : '' ?>" id="submenuEmpleados">
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'ver_empleados' ? 'active' : '' ?>" href="ver_empleados.php">Listar Empleados</a>
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'crear_empleado' ? 'active' : '' ?>" href="crear_empleado.php">Crear Empleado</a>
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'editar_empleado' ? 'active' : '' ?>" href="editar_empleado.php">Editar Empleado</a>
                </div>
            </div>

            <!-- HUÉSPEDES -->
            <div class="nav-item">
                <a class="nav-link <?= in_array($current_page ?? '', ['ver_huespedes', 'crear_huesped', 'editar_huesped']) ? 'active' : '' ?>" 
                   data-bs-toggle="collapse" href="#submenuHuespedes" role="button">
                    <i class="fas fa-users"></i> <span>Gestionar Huéspedes</span>
                    <i class="fas fa-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse <?= in_array($current_page ?? '', ['ver_huespedes', 'crear_huesped', 'editar_huesped']) ? 'show' : '' ?>" id="submenuHuespedes">
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'ver_huespedes' ? 'active' : '' ?>" href="ver_huespedes.php">Listar Huéspedes</a>
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'crear_huesped' ? 'active' : '' ?>" href="crear_huesped.php">Registrar Huésped</a>
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'editar_huesped' ? 'active' : '' ?>" href="editar_huesped.php">Editar Huésped</a>
                </div>
            </div>

            <!-- PAQUETES -->
            <div class="nav-item">
                <a class="nav-link <?= in_array($current_page ?? '', ['ver_paquetes', 'crear_paquete', 'editar_paquete']) ? 'active' : '' ?>" 
                   data-bs-toggle="collapse" href="#submenuPaquetes" role="button">
                    <i class="fas fa-suitcase"></i> <span>Paquetes Turísticos</span>
                    <i class="fas fa-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse <?= in_array($current_page ?? '', ['ver_paquetes', 'crear_paquete', 'editar_paquete']) ? 'show' : '' ?>" id="submenuPaquetes">
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'ver_paquetes' ? 'active' : '' ?>" href="ver_paquetes.php">Listar Paquetes</a>
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'crear_paquete' ? 'active' : '' ?>" href="crear_paquete.php">Crear Paquete</a>
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'editar_paquete' ? 'active' : '' ?>" href="editar_paquete.php">Editar Paquete</a>
                </div>
            </div>

            <!-- RESERVAS -->
            <div class="nav-item">
                <a class="nav-link <?= in_array($current_page ?? '', ['ver_reservas', 'crear_reserva_admin', 'editar_reserva_admin']) ? 'active' : '' ?>" 
                   data-bs-toggle="collapse" href="#submenuReservas" role="button">
                    <i class="fas fa-calendar-check"></i> <span>Reservas</span>
                    <i class="fas fa-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse <?= in_array($current_page ?? '', ['ver_reservas', 'crear_reserva_admin', 'editar_reserva_admin']) ? 'show' : '' ?>" id="submenuReservas">
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'ver_reservas' ? 'active' : '' ?>" href="ver_reservas.php">Listar Reservas</a>
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'crear_reserva_admin' ? 'active' : '' ?>" href="crear_reserva_admin.php">Crear Reserva</a>
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'editar_reserva_admin' ? 'active' : '' ?>" href="editar_reserva_admin.php">Editar Reserva</a>
                </div>
            </div>

            <!-- REPORTES -->
            <div class="nav-item">
                <a class="nav-link <?= in_array($current_page ?? '', ['reportes', 'generar_reporte']) ? 'active' : '' ?>" 
                   data-bs-toggle="collapse" href="#submenuReportes" role="button">
                    <i class="fas fa-chart-bar"></i> <span>Reportes</span>
                    <i class="fas fa-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse <?= in_array($current_page ?? '', ['reportes', 'generar_reporte']) ? 'show' : '' ?>" id="submenuReportes">
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'reportes' ? 'active' : '' ?>" href="reportes.php">Ver Reportes</a>
                    <a class="nav-link py-2 ps-5 <?= ($current_page ?? '') === 'generar_reporte' ? 'active' : '' ?>" href="generar_reporte.php">Generar Reporte</a>
                </div>
            </div>

        </div>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <?= $contenido_principal ?? '<p>Contenido no definido.</p>' ?>
    </div>

    <!-- Footer fijo -->
    <footer class="footer-wrapper bg-dark text-white py-3" style="z-index: 900;">
        <div class="container text-center">
            <small>&copy; 2025 Hotel Yokoso. Todos los derechos reservados.</small>
        </div>
    </footer>

    <script>
        /* Toggle sidebar móvil */
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('overlay').classList.toggle('show');
        });

        document.getElementById('overlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
            this.classList.remove('show');
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>