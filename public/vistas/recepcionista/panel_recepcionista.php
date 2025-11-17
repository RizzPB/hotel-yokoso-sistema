<?php
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../login.php");
    exit;
}

$rol = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Recepcionista - Hotel Yokoso</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--color-rojo-quemado);">
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
                        <span class="nav-link text-white me-3" style="font-weight: 600;">Hola, Recepcionista</span>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="../../logout.php" class="btn btn-warning btn-sm text-dark">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="text-center text-rojo mb-4">Panel de Recepcionista</h2>
                <p>¡Hola! Bienvenido a tu panel de trabajo.</p>

                <div class="mt-4">
                    <h4>¿Qué quieres hacer?</h4>
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
                </div>

                <div class="mt-4">
                    <a href="../../logout.php" class="btn btn-outline-danger">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>