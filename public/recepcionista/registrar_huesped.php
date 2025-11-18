<?php
// public/vistas/recepcionista/registrar_huesped.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Obtener todos los tipos de habitación únicos
$stmt = $pdo->prepare("SELECT DISTINCT tipo FROM Habitacion WHERE estado = 'disponible' ORDER BY tipo ASC");
$stmt->execute();
$tiposHabitacion = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Obtener todas las habitaciones disponibles
$stmt = $pdo->prepare("SELECT idHabitacion, numero, tipo, precioNoche FROM Habitacion WHERE estado = 'disponible'");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener tipos de paquetes turísticos (duración)
$stmt = $pdo->prepare("SELECT DISTINCT duracionDias FROM PaqueteTuristico WHERE activo = 1 ORDER BY duracionDias ASC");
$stmt->execute();
$duracionPaquetes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Obtener paquetes turísticos activos
$stmt = $pdo->prepare("SELECT idPaquete, nombre, descripcion, precio, duracionDias FROM PaqueteTuristico WHERE activo = 1");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si el formulario fue enviado, procesarlo
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $tipoDocumento = $_POST['tipoDocumento'];
    $nroDocumento = trim($_POST['nroDocumento']);
    $procedencia = trim($_POST['procedencia']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $motivoVisita = trim($_POST['motivoVisita']);
    $preferenciaAlimentaria = trim($_POST['preferenciaAlimentaria']);
    $idPaquete = $_POST['idPaquete'] ?? null;

    // Obtener las habitaciones seleccionadas
    $habitacionesSeleccionadas = $_POST['habitaciones'] ?? [];

    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($tipoDocumento) || empty($nroDocumento)) {
        $error = "Los campos nombre, apellido, tipo y número de documento son obligatorios.";
    } elseif (empty($habitacionesSeleccionadas)) {
        $error = "Debes seleccionar al menos una habitación.";
    } else {
        // Insertar huésped
        $stmt = $pdo->prepare("
            INSERT INTO Huesped (nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$nombre, $apellido, $tipoDocumento, $nroDocumento, $procedencia, $email, $telefono, $motivoVisita, $preferenciaAlimentaria])) {
            $idHuesped = $pdo->lastInsertId();

            // Fecha de inicio y fin (por defecto hoy y mañana)
            $fechaInicio = date('Y-m-d');
            $fechaFin = date('Y-m-d', strtotime('+1 day'));

            // Calcular total
            $total = 0;
            foreach ($habitacionesSeleccionadas as $idHab) {
                $stmt = $pdo->prepare("SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?");
                $stmt->execute([$idHab]);
                $hab = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($hab) {
                    $total += $hab['precioNoche'];
                }
            }

            // Si hay paquete, sumar su precio
            if ($idPaquete) {
                $stmt = $pdo->prepare("SELECT precio FROM PaqueteTuristico WHERE idPaquete = ?");
                $stmt->execute([$idPaquete]);
                $pkg = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($pkg) {
                    $total += $pkg['precio'];
                }
            }

            // Crear la reserva
            $stmt = $pdo->prepare("
                INSERT INTO Reserva (idHuesped, idPaquete, fechaInicio, fechaFin, total, estado)
                VALUES (?, ?, ?, ?, ?, 'confirmada')
            ");
            if ($stmt->execute([$idHuesped, $idPaquete, $fechaInicio, $fechaFin, $total])) {
                $idReserva = $pdo->lastInsertId();

                // Insertar cada habitación en la tabla intermedia ReservaHabitacion
                foreach ($habitacionesSeleccionadas as $idHab) {
                    $stmt = $pdo->prepare("
                        INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche)
                        VALUES (?, ?, (SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?))
                    ");
                    $stmt->execute([$idReserva, $idHab, $idHab]);

                    // Cambiar estado de la habitación a ocupada
                    $stmt = $pdo->prepare("UPDATE Habitacion SET estado = 'ocupada' WHERE idHabitacion = ?");
                    $stmt->execute([$idHab]);
                }

                $mensaje = "Huésped registrado exitosamente.";
            } else {
                $error = "Error al crear la reserva.";
            }
        } else {
            $error = "Error al registrar el huésped.";
        }
    }
}

$titulo_pagina = "Registrar Huésped - Hotel Yokoso";

$contenido_principal = '
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-rojo fw-bold">Registrar Nuevo Huésped</h2>
    </div>

    ' . (isset($mensaje) ? '<div class="alert alert-success w-100">' . htmlspecialchars($mensaje) . '</div>' : '') . '
    ' . (isset($error) ? '<div class="alert alert-danger w-100">' . htmlspecialchars($error) . '</div>' : '') . '

    <div class="reserva-form-container">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input type="text" class="form-control" name="nombre" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apellido *</label>
                    <input type="text" class="form-control" name="apellido" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Tipo de Documento *</label>
                    <select class="form-select" name="tipoDocumento" required>
                        <option value="">Seleccionar...</option>
                        <option value="DNI">DNI</option>
                        <option value="Pasaporte">Pasaporte</option>
                        <option value="Carnet">Carnet</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Número de Documento *</label>
                    <input type="text" class="form-control" name="nroDocumento" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Procedencia</label>
                    <input type="text" class="form-control" name="procedencia">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" name="telefono">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Motivo de Visita</label>
                    <input type="text" class="form-control" name="motivoVisita">
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Preferencias Alimentarias</label>
                <textarea class="form-control" rows="3" name="preferenciaAlimentaria" placeholder="Ej. Vegetariano, sin gluten, alérgico a la leche..."></textarea>
            </div>

            <!-- Sección de Habitaciones -->
            <div class="mt-4">
                <h4 class="text-rojo">Habitaciones</h4>
                <label class="form-label">Filtrar por tipo de habitación</label>
                <select class="form-select mb-3" id="filtroHabitacion">
                    <option value="">Ver todas las disponibles</option>
                    ' . implode('', array_map(fn($tipo) => "<option value=\"" . htmlspecialchars($tipo) . "\">" . htmlspecialchars($tipo) . "</option>", $tiposHabitacion)) . '
                </select>

                <div id="listaHabitaciones" class="border p-3 rounded bg-light">
                    ' . implode('', array_map(function($hab) {
                        return '
                            <div class="habitacion-item mb-2 p-2 bg-white rounded shadow-sm" data-tipo="' . $hab['tipo'] . '">
                                <input type="checkbox" name="habitaciones[]" value="' . $hab['idHabitacion'] . '" id="hab_' . $hab['idHabitacion'] . '">
                                <label for="hab_' . $hab['idHabitacion'] . '" class="fw-semibold">
                                    ' . htmlspecialchars($hab['numero']) . ' (' . htmlspecialchars($hab['tipo']) . ') - Bs. ' . htmlspecialchars($hab['precioNoche']) . '/noche
                                </label>
                            </div>';
                    }, $habitaciones)) . '
                </div>
            </div>

            <!-- Sección de Paquetes Turísticos -->
            <div class="mt-4">
                <h4 class="text-rojo">Paquetes Turísticos</h4>
                <label class="form-label">Filtrar por duración</label>
                <select class="form-select mb-3" id="filtroPaquete">
                    <option value="">Ver todos</option>
                    ' . implode('', array_map(fn($duracion) => "<option value=\"$duracion\">$duracion día(s)</option>", $duracionPaquetes)) . '
                </select>

                <div id="listaPaquetes" class="row">
                    ' . implode('', array_map(function($pkg) {
                        return '
                            <div class="col-md-6 mb-3 paquete-card" data-duracion="' . $pkg['duracionDias'] . '">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title text-rojo">' . htmlspecialchars($pkg['nombre']) . '</h5>
                                        <p class="card-text text-muted small">' . htmlspecialchars($pkg['descripcion']) . '</p>
                                        <p class="fw-bold text-mostaza">Bs. ' . htmlspecialchars($pkg['precio']) . '</p>
                                        
                                        <div class="mt-2">
                                            <input type="radio" name="idPaquete" value="' . $pkg['idPaquete'] . '" id="pkg_' . $pkg['idPaquete'] . '">
                                            <label for="pkg_' . $pkg['idPaquete'] . '" class="fw-semibold">Seleccionar este paquete</label>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                    }, $paquetes)) . '
                </div>
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="panel_recepcionista.php" class="btn btn-cancelar me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Guardar Huésped
                </button>
            </div>
        </form>
    </div>

    <script>
        // Filtrar habitaciones por tipo
        document.getElementById("filtroHabitacion").addEventListener("change", function() {
            const tipo = this.value;
            const items = document.querySelectorAll("#listaHabitaciones .habitacion-item");

            items.forEach(item => {
                if (tipo === "" || item.getAttribute("data-tipo") === tipo) {
                    item.style.display = "flex";
                } else {
                    item.style.display = "none";
                }
            });
        });

        // Filtrar paquetes por duración
        document.getElementById("filtroPaquete").addEventListener("change", function() {
            const duracion = this.value;
            const cards = document.querySelectorAll("#listaPaquetes .paquete-card");

            cards.forEach(card => {
                if (duracion === "" || card.getAttribute("data-duracion") == duracion) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        });
    </script>
';

include 'plantilla_recepcionista.php';
?>