<?php
// public/recepcionista/registrar_huesped.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'registrar_huesped';
require_once __DIR__ . '/../../config/database.php';

$mensaje = $error = '';

// Cargar habitaciones disponibles
$stmt = $pdo->prepare("SELECT DISTINCT tipo FROM Habitacion WHERE estado = 'disponible' ORDER BY tipo");
$stmt->execute();
$tiposHabitacion = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("SELECT idHabitacion, numero, tipo, precioNoche FROM Habitacion WHERE estado = 'disponible' ORDER BY numero");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT idPaquete, nombre, descripcion, precio FROM PaqueteTuristico WHERE activo = 1");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario de registro 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $tipoDocumento = $_POST['tipoDocumento'] ?? '';
    $nroDocumento = trim($_POST['nroDocumento'] ?? '');
    $procedencia = trim($_POST['procedencia'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $motivoVisita = trim($_POST['motivoVisita'] ?? '');
    $preferenciaAlimentaria = trim($_POST['preferenciaAlimentaria'] ?? '');
    $idPaquete = !empty($_POST['idPaquete']) ? $_POST['idPaquete'] : null;
    $habitacionesSeleccionadas = $_POST['habitaciones'] ?? [];

    if (empty($nombre) || empty($apellido) || empty($tipoDocumento) || empty($nroDocumento) || empty($habitacionesSeleccionadas)) {
        $error = "Completa todos los campos obligatorios y selecciona al menos una habitación.";
    } else {
        try {
            $pdo->beginTransaction();

            // Registrar huésped
            $stmt = $pdo->prepare("INSERT INTO Huesped (nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$nombre, $apellido, $tipoDocumento, $nroDocumento, $procedencia, $email, $telefono, $motivoVisita, $preferenciaAlimentaria]);
            $idHuesped = $pdo->lastInsertId();

            // Fechas por defecto
            $fechaInicio = date('Y-m-d');
            $fechaFin = date('Y-m-d', strtotime('+1 day'));

            // Calcular total
            $total = 0;
            foreach ($habitacionesSeleccionadas as $idHab) {
                $stmt = $pdo->prepare("SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?");
                $stmt->execute([$idHab]);
                $hab = $stmt->fetch();
                $total += $hab['precioNoche'] ?? 0;
            }
            if ($idPaquete) {
                $stmt = $pdo->prepare("SELECT precio FROM PaqueteTuristico WHERE idPaquete = ?");
                $stmt->execute([$idPaquete]);
                $pkg = $stmt->fetch();
                $total += $pkg['precio'] ?? 0;
            }

            // Crear reserva
            $stmt = $pdo->prepare("INSERT INTO Reserva (idHuesped, idPaquete, fechaInicio, fechaFin, total, estado) VALUES (?, ?, ?, ?, ?, 'confirmada')");
            $stmt->execute([$idHuesped, $idPaquete, $fechaInicio, $fechaFin, $total]);
            $idReserva = $pdo->lastInsertId();

            // Asignar habitaciones
            foreach ($habitacionesSeleccionadas as $idHab) {
                $stmt = $pdo->prepare("INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche) VALUES (?, ?, (SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?))");
                $stmt->execute([$idReserva, $idHab, $idHab]);
                $stmt = $pdo->prepare("UPDATE Habitacion SET estado = 'ocupada' WHERE idHabitacion = ?");
                $stmt->execute([$idHab]);
            }

            $pdo->commit();
            $mensaje = "Huésped registrado y habitación asignada con éxito.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al procesar el registro. Intenta nuevamente.";
        }
    }
}

$titulo_pagina = "Registrar Huésped - Hotel Yokoso";

$contenido_principal = '
<div class="container py-5">
    <h2 class="text-rojo fw-bold text-center mb-5">Registrar Nuevo Huésped</h2>

    ' . ($mensaje ? '<div class="alert alert-success text-center mx-auto" style="max-width: 900px;"><i class="fas fa-check-circle fa-2x"></i><br>' . $mensaje . '</div>' : '') . '
    ' . ($error ? '<div class="alert alert-danger text-center mx-auto" style="max-width: 900px;"><i class="fas fa-times-circle fa-2x"></i><br>' . $error . '</div>' : '') . '

    <div class="row justify-content-center">
        <div class="col-xl-10 col-xxl-9">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5 p-lg-6">

                    <form method="POST">
                        <!-- DATOS PERSONALES -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Nombre *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="nombre" required placeholder="Ej. Rebeca">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Apellido *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="apellido" required placeholder="Ej. Lopez Choque">
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Tipo Documento *</label>
                                <select class="form-select form-select-lg rounded-pill" name="tipoDocumento" required >
                                    <option value="">Seleccionar...</option>
                                    <option value="Carnet">Carnet</option>
                                    <option value="DNI">DNI</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Nro. Documento *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="nroDocumento" required placeholder="Ej. 456852">
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Procedencia</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="procedencia" placeholder="Ej. Cochabamba">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Email</label>
                                <input type="email" class="form-control form-control-lg rounded-pill" name="email" placeholder="Ej. usuario@gmail.com">
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Teléfono</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="telefono">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Motivo de Visita</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="motivoVisita" placeholder="Ej. Turismo, Trabajo">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark">Preferencias Alimentarias</label>
                            <textarea class="form-control form-control-lg rounded-4" rows="3" name="preferenciaAlimentaria" placeholder="Ej. Vegetariano, sin gluten, alérgico a la leche..."></textarea>
                        </div>

                        <!-- HABITACIONES -->
                        <hr class="my-5 border-secondary">
                        <h4 class="text-rojo fw-bold mb-4">Seleccionar Habitación(es)</h4>
                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <select class="form-select form-select-lg rounded-pill" id="filtroHabitacion">
                                    <option value="">Todas las disponibles</option>
                                    ' . implode('', array_map(fn($t) => '<option value="'.htmlspecialchars($t).'">'.ucwords($t).'</option>', $tiposHabitacion)) . '
                                </select>
                            </div>
                        </div>

                        <div class="row g-4" id="listaHabitaciones">
                            ' . implode('', array_map(function($hab) {
                                return '
                                <div class="col-md-6 col-lg-4 habitacion-item" data-tipo="'.htmlspecialchars($hab['tipo']).'">
                                    <div class="card h-100 border-0 shadow hover-lift transition">
                                        <div class="card-body text-center p-4">
                                            <h3 class="text-rojo fw-bold mb-1">Hab. '.$hab['numero'].'</h3>
                                            <p class="text-muted fw-bold">'.ucwords($hab['tipo']).'</p>
                                            <h4 class="text-success fw-bold mb-3">Bs. '.number_format($hab['precioNoche'], 2).'</h4>
                                            <input type="checkbox" name="habitaciones[]" value="'.$hab['idHabitacion'].'" id="hab_'.$hab['idHabitacion'].'" class="btn-check">
                                            <label for="hab_'.$hab['idHabitacion'].'" class="btn btn-yokoso btn-lg w-100 rounded-pill shadow-sm">
                                                <i class="fas fa-bed me-2"></i>Seleccionar
                                            </label>
                                        </div>
                                    </div>
                                </div>';
                            }, $habitaciones)) . '
                        </div>

                        <!-- PAQUETES -->
                        <hr class="my-5 border-secondary">
                        <h4 class="text-rojo fw-bold mb-4">Paquete Turístico (Opcional)</h4>
                        <div class="row g-4">
                            ' . implode('', array_map(function($pkg) {
                                return '
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <h5 class="text-rojo fw-bold">'.$pkg['nombre'].'</h5>
                                            <p class="small text-muted">'.$pkg['descripcion'].'</p>
                                            <h4 class="text-mostaza fw-bold">Bs. '.number_format($pkg['precio'],2).'</h4>
                                            <input type="radio" name="idPaquete" value="'.$pkg['idPaquete'].'" id="pkg_'.$pkg['idPaquete'].'" class="btn-check">
                                            <label for="pkg_'.$pkg['idPaquete'].'" class="btn btn-outline-yokoso btn-lg w-100 mt-3 rounded-pill">Elegir</label>
                                        </div>
                                    </div>
                                </div>';
                            }, $paquetes)) . '
                        </div>

                        <!-- BOTONES FINALES -->
                        <div class="mt-5 pt-4 text-end">
                            <a href="panel_recepcionista.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill me-3">Cancelar</a>
                            <button type="submit" class="btn btn-yokoso btn-lg px-5 rounded-pill shadow-lg">
                                <i class="fas fa-user-plus fa-lg me-2"></i>
                                Registrar Huésped
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// FILTRO DE HABITACIONES 
document.getElementById("filtroHabitacion").addEventListener("change", function() {
    const tipo = this.value.toLowerCase();
    document.querySelectorAll(".habitacion-item").forEach(item => {
        const tipoHab = item.dataset.tipo.toLowerCase();
        item.style.display = (tipo === "" || tipoHab.includes(tipo)) ? "block" : "none";
    });
});
</script>
';

include 'plantilla_recepcionista.php';
?>