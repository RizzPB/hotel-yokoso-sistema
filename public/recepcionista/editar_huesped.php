<?php
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

// Verificar que se haya pasado un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ver_huespedes.php");
    exit;
}

$idHuesped = $_GET['id'];

require_once __DIR__ . '/../../../config/database.php';

// Obtener el huésped actual
$stmt = $pdo->prepare("
    SELECT idHuesped, nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria
    FROM Huesped
    WHERE idHuesped = ?
");
$stmt->execute([$idHuesped]);
$huesped = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$huesped) {
    die("Huésped no encontrado.");
}

// Si el formulario fue enviado, procesarlo
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

    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($tipoDocumento) || empty($nroDocumento)) {
        $error = "Los campos nombre, apellido, tipo y número de documento son obligatorios.";
    } else {
        // Actualizar en la base de datos
        $stmt = $pdo->prepare("
            UPDATE Huesped
            SET nombre = ?, apellido = ?, tipoDocumento = ?, nroDocumento = ?, procedencia = ?, email = ?, telefono = ?, motivoVisita = ?, preferenciaAlimentaria = ?
            WHERE idHuesped = ?
        ");
        if ($stmt->execute([$nombre, $apellido, $tipoDocumento, $nroDocumento, $procedencia, $email, $telefono, $motivoVisita, $preferenciaAlimentaria, $idHuesped])) {
            $mensaje = "Huésped actualizado exitosamente.";
            // Recargar los datos después de guardar
            $stmt = $pdo->prepare("
                SELECT idHuesped, nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono, motivoVisita, preferenciaAlimentaria
                FROM Huesped
                WHERE idHuesped = ?
            ");
            $stmt->execute([$idHuesped]);
            $huesped = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Error al actualizar el huésped.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Huésped - Hotel Yokoso</title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-rojo fw-bold">Editar Huésped #<?= htmlspecialchars($huesped['idHuesped']) ?></h2>
            <a href="ver_huespedes.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver a Huéspedes
            </a>
        </div>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($huesped['nombre']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido *</label>
                            <input type="text" class="form-control" name="apellido" value="<?= htmlspecialchars($huesped['apellido']) ?>" required>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Documento *</label>
                            <select class="form-select" name="tipoDocumento" required>
                                <option value="DNI" <?= $huesped['tipoDocumento'] === 'DNI' ? 'selected' : '' ?>>DNI</option>
                                <option value="Pasaporte" <?= $huesped['tipoDocumento'] === 'Pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                                <option value="Carnet" <?= $huesped['tipoDocumento'] === 'Carnet' ? 'selected' : '' ?>>Carnet</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Número de Documento *</label>
                            <input type="text" class="form-control" name="nroDocumento" value="<?= htmlspecialchars($huesped['nroDocumento']) ?>" required>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Procedencia</label>
                            <input type="text" class="form-control" name="procedencia" value="<?= htmlspecialchars($huesped['procedencia']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($huesped['email']) ?>">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" value="<?= htmlspecialchars($huesped['telefono']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Motivo de Visita</label>
                            <input type="text" class="form-control" name="motivoVisita" value="<?= htmlspecialchars($huesped['motivoVisita']) ?>">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Preferencias Alimentarias</label>
                        <textarea class="form-control" rows="3" name="preferenciaAlimentaria" placeholder="Ej. Vegetariano, sin gluten, alérgico a la leche..."><?= htmlspecialchars($huesped['preferenciaAlimentaria']) ?></textarea>
                    </div>

                    <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="ver_huespedes.php" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-rojo btn-lg">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>