<?php
// public/recepcionista/registrar_huesped.php
// ✅ PERMITE REGISTRAR UN HUÉSPED NUEVO
// ✅ O ACTUALIZAR UNA RESERVA EXISTENTE (CHECK-IN)
// ✅ SI RECIBE ?reserva=123, ACTUALIZA ESA RESERVA A 'ocupada'

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'registrar_huesped';
require_once __DIR__ . '/../../config/database.php';

$mensaje = $error = '';

// Cargar datos para formularios
$stmt = $pdo->prepare("SELECT DISTINCT tipo FROM Habitacion WHERE estado = 'disponible' ORDER BY tipo");
$stmt->execute();
$tiposHabitacion = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("SELECT idHabitacion, numero, tipo, precioNoche FROM Habitacion WHERE estado = 'disponible' ORDER BY CAST(numero AS UNSIGNED)");
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT idPaquete, nombre, descripcion, precio FROM PaqueteTuristico WHERE activo = 1");
$stmt->execute();
$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ver si viene de una reserva existente (check-in)
$idReservaExistente = $_GET['reserva'] ?? null;

// Valores previos para mantener el formulario
$prev = $_POST ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($prev['nombre'] ?? '');
    $apellido = trim($prev['apellido'] ?? '');
    $tipoDocumento = $prev['tipoDocumento'] ?? '';
    $nroDocumento = trim($prev['nroDocumento'] ?? '');
    $procedencia = trim($prev['procedencia'] ?? '');
    $email = trim($prev['email'] ?? '');
    $telefono = trim($prev['telefono'] ?? '');
    $motivoVisita = trim($prev['motivoVisita'] ?? '');
    $preferenciaAlimentaria = trim($prev['preferenciaAlimentaria'] ?? '');
    $idPaquete = !empty($prev['idPaquete']) ? (int)$prev['idPaquete'] : null;
    $habitacionesSeleccionadas = array_map('intval', $prev['habitaciones'] ?? []);

    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($tipoDocumento) || empty($nroDocumento) || empty($habitacionesSeleccionadas)) {
        $error = "Completa todos los campos obligatorios y selecciona al menos una habitación.";
    } elseif (count($habitacionesSeleccionadas) !== count(array_unique($habitacionesSeleccionadas))) {
        $error = "No puedes seleccionar la misma habitación dos veces.";
    } else {
        try {
            $pdo->beginTransaction();

            // Reutilizar huésped si ya existe por documento
            $stmt = $pdo->prepare("SELECT idHuesped FROM Huesped WHERE nroDocumento = ?");
            $stmt->execute([$nroDocumento]);
            $huesped = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($huesped) {
                $idHuesped = $huesped['idHuesped'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO Huesped (nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$nombre, $apellido, $tipoDocumento, $nroDocumento, $procedencia, $email, $telefono, $motivoVisita, $preferenciaAlimentaria]);
                $idHuesped = $pdo->lastInsertId();
            }

            // Fechas
            $fechaInicio = date('Y-m-d');
            $duracionDias = 1;
            $fechaFin = date('Y-m-d', strtotime("+$duracionDias days", strtotime($fechaInicio)));

            // Validar disponibilidad
            $idsHabStr = str_repeat('?,', count($habitacionesSeleccionadas) - 1) . '?';
            $stmt = $pdo->prepare("SELECT idHabitacion FROM Habitacion WHERE idHabitacion IN ($idsHabStr) AND estado = 'disponible' FOR UPDATE");
            $stmt->execute($habitacionesSeleccionadas);
            $disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($disponibles) !== count($habitacionesSeleccionadas)) {
                throw new Exception("Algunas habitaciones ya no están disponibles. Actualiza la página.");
            }

            // Calcular total
            $total = 0;
            $preciosHabitaciones = [];
            foreach ($habitacionesSeleccionadas as $idHab) {
                $stmt = $pdo->prepare("SELECT precioNoche FROM Habitacion WHERE idHabitacion = ?");
                $stmt->execute([$idHab]);
                $precio = (float)$stmt->fetchColumn();
                $total += $precio * $duracionDias;
                $preciosHabitaciones[$idHab] = $precio;
            }
            if ($idPaquete) {
                $stmt = $pdo->prepare("SELECT precio FROM PaqueteTuristico WHERE idPaquete = ?");
                $stmt->execute([$idPaquete]);
                $total += (float)$stmt->fetchColumn();
            }

            // ✅ LÓGICA PRINCIPAL: ¿ACTUALIZAR RESERVA EXISTENTE O CREAR NUEVA?
            if ($idReservaExistente) {
                // Verificar que la reserva exista y esté en estado 'confirmada'
                $stmt = $pdo->prepare("SELECT idReserva, estado FROM Reserva WHERE idReserva = ? AND estado = 'confirmada'");
                $stmt->execute([$idReservaExistente]);
                $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$reserva) {
                    throw new Exception("La reserva no existe o ya fue procesada.");
                }

                // Actualizar reserva: estado = 'ocupada', fechaCheckIn = hoy
                $stmt = $pdo->prepare("
                    UPDATE Reserva 
                    SET idHuesped = ?, 
                        idPaquete = ?, 
                        fechaInicio = ?, 
                        fechaFin = ?, 
                        fechaCheckIn = ?, 
                        total = ?, 
                        anticipo = ?, 
                        estado = 'ocupada' 
                    WHERE idReserva = ?
                ");
                $stmt->execute([$idHuesped, $idPaquete, $fechaInicio, $fechaFin, $fechaInicio, $total, $total, $idReservaExistente]);

                // Eliminar asignaciones anteriores y asignar nuevas habitaciones
                $stmt = $pdo->prepare("DELETE FROM ReservaHabitacion WHERE idReserva = ?");
                $stmt->execute([$idReservaExistente]);

                $stmtInsert = $pdo->prepare("INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche) VALUES (?, ?, ?)");
                $stmtUpdate = $pdo->prepare("UPDATE Habitacion SET estado = 'ocupada' WHERE idHabitacion = ?");

                foreach ($habitacionesSeleccionadas as $idHab) {
                    $stmtInsert->execute([$idReservaExistente, $idHab, $preciosHabitaciones[$idHab]]);
                    $stmtUpdate->execute([$idHab]);
                }

                $idReserva = $idReservaExistente;

            } else {
                // Crear nueva reserva
                $stmt = $pdo->prepare("INSERT INTO Reserva (idHuesped, idPaquete, fechaInicio, fechaFin, fechaCheckIn, total, anticipo, estado, fechaCreacion) VALUES (?, ?, ?, ?, ?, ?, ?, 'ocupada', NOW())");
                $stmt->execute([$idHuesped, $idPaquete, $fechaInicio, $fechaFin, $fechaInicio, $total, $total]);
                $idReserva = $pdo->lastInsertId();

                // Asignar habitaciones
                $stmtInsert = $pdo->prepare("INSERT INTO ReservaHabitacion (idReserva, idHabitacion, precioNoche) VALUES (?, ?, ?)");
                $stmtUpdate = $pdo->prepare("UPDATE Habitacion SET estado = 'ocupada' WHERE idHabitacion = ?");

                foreach ($habitacionesSeleccionadas as $idHab) {
                    $stmtInsert->execute([$idReserva, $idHab, $preciosHabitaciones[$idHab]]);
                    $stmtUpdate->execute([$idHab]);
                }
            }

            $pdo->commit();
            $mensaje = "¡Check-in completado! Huésped registrado y habitación(es) asignada(s). Reserva #$idReserva";

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}

$titulo_pagina = "Check-in: Registrar Huésped - Hotel Yokoso";

$contenido_principal = '
<div class="container py-5">
    <h2 class="text-rojo fw-bold text-center mb-5">
        Check-in: Registrar Huésped
    </h2>

    ' . ($mensaje ? '<div class="alert alert-success text-center mx-auto" style="max-width: 900px;"><i class="fas fa-check-circle fa-3x mb-3"></i><br><strong>' . htmlspecialchars($mensaje) . '</strong></div>' : '') . '
    ' . ($error ? '<div class="alert alert-danger text-center mx-auto" style="max-width: 900px;"><i class="fas fa-times-circle fa-3x mb-3"></i><br>' . htmlspecialchars($error) . '</div>' : '') . '

    <div class="row justify-content-center">
        <div class="col-xl-10 col-xxl-9">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5 p-lg-6">

                    <form method="POST">
                        <!-- DATOS DEL HUÉSPED -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Nombre *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="nombre" value="' . htmlspecialchars($prev['nombre'] ?? '') . '" required placeholder="Ej. Juan Carlos">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Apellido *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="apellido" value="' . htmlspecialchars($prev['apellido'] ?? '') . '" required placeholder="Ej. Pérez Gómez">
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Tipo Documento *</label>
                                <select class="form-select form-select-lg rounded-pill" name="tipoDocumento" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="Carnet" ' . (isset($prev['tipoDocumento']) && $prev['tipoDocumento'] === 'Carnet' ? 'selected' : '') . '>Carnet de Identidad</option>
                                    <option value="DNI" ' . (isset($prev['tipoDocumento']) && $prev['tipoDocumento'] === 'DNI' ? 'selected' : '') . '>DNI</option>
                                    <option value="Pasaporte" ' . (isset($prev['tipoDocumento']) && $prev['tipoDocumento'] === 'Pasaporte' ? 'selected' : '') . '>Pasaporte</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Nro. Documento *</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="nroDocumento" value="' . htmlspecialchars($prev['nroDocumento'] ?? '') . '" required placeholder="Ej. 12345678">
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Procedencia</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="procedencia" value="' . htmlspecialchars($prev['procedencia'] ?? '') . '" placeholder="Ej. La Paz, Santa Cruz">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Teléfono</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="telefono" value="' . htmlspecialchars($prev['telefono'] ?? '') . '" placeholder="Ej. 70707070">
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Email</label>
                                <input type="email" class="form-control form-control-lg rounded-pill" name="email" value="' . htmlspecialchars($prev['email'] ?? '') . '" placeholder="Ej. juan@gmail.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Motivo de Visita</label>
                                <input type="text" class="form-control form-control-lg rounded-pill" name="motivoVisita" value="' . htmlspecialchars($prev['motivoVisita'] ?? '') . '" placeholder="Ej. Turismo, Negocios">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark">Preferencias Alimentarias</label>
                            <textarea class="form-control form-control-lg rounded-4" rows="3" name="preferenciaAlimentaria" placeholder="Ej. Sin gluten, vegetariano, alérgico al maní...">' . htmlspecialchars($prev['preferenciaAlimentaria'] ?? '') . '</textarea>
                        </div>

                        <!-- HABITACIONES DISPONIBLES -->
                        <hr class="my-5 border-secondary">
                        <h4 class="text-rojo fw-bold mb-4">Seleccionar Habitación(es) Disponibles</h4>
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
                                                Seleccionar
                                            </label>
                                        </div>
                                    </div>
                                </div>';
                            }, $habitaciones)) . '
                        </div>

                        <!-- PAQUETES OPCIONALES -->
                        <hr class="my-5 border-secondary">
                        <h4 class="text-rojo fw-bold mb-4">Paquete Turístico (Opcional)</h4>
                        <div class="row g-4">
                            ' . (!empty($paquetes) ? implode('', array_map(function($pkg) {
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
                            }, $paquetes)) : '<div class="col-12 text-center text-muted">No hay paquetes disponibles.</div>') . '
                        </div>

                        <!-- BOTONES -->
                        <div class="mt-5 pt-4 text-end">
                            <a href="panel_recepcionista.php" class="btn btn-cancelar me-3">
                                <i class="fas fa-xmark me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-yokoso btn-lg px-5 rounded-pill shadow-lg">
                                Finalizar Check-in
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById("filtroHabitacion")?.addEventListener("change", function() {
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