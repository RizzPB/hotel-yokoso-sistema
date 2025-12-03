<?php
// public/vistas/admin/crear_empleado.php


define('ACCESO_PERMITIDO', true);
session_start();

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$current_page = 'crear_empleado';
require_once __DIR__ . '/../../config/database.php';

$error = null;
$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $cargo = trim($_POST['cargo']);
    $nombreUsuario = trim($_POST['nombreUsuario']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmarPassword = $_POST['confirmarPassword'];

    // ✨ Validaciones en el servidor (¡nunca confiar solo en el frontend!)
    if (empty($nombre) || empty($apellido) || empty($cargo) || empty($nombreUsuario) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/', $nombre)) {
        $error = "El nombre solo puede contener letras y espacios.";
    } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/', $apellido)) {
        $error = "El apellido solo puede contener letras y espacios.";
    } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/', $cargo)) {
        $error = "El cargo solo puede contener letras y espacios.";
    } elseif ($password !== $confirmarPassword) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[@$!%*?&]/', $password)) {
        $error = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo (@$!%*?&).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } else {
        // Verificar unicidad
        $stmt = $pdo->prepare("SELECT idUsuario FROM Usuario WHERE nombreUsuario = ? OR email = ?");
        $stmt->execute([$nombreUsuario, $email]);
        if ($stmt->fetch()) {
            $error = "El nombre de usuario o correo ya está registrado.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar en Usuario
            $stmt = $pdo->prepare("INSERT INTO Usuario (nombreUsuario, contrasena, rol, email, activo) VALUES (?, ?, 'empleado', ?, 1)");
            if ($stmt->execute([$nombreUsuario, $hash, $email])) {
                $idUsuario = $pdo->lastInsertId();

                // Insertar en Empleado
                $stmt = $pdo->prepare("INSERT INTO Empleado (nombre, apellido, cargo, idUsuario) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$nombre, $apellido, $cargo, $idUsuario])) {
                    $mensaje = "Empleado registrado exitosamente.";
                } else {
                    $error = "Error al registrar el empleado.";
                }
            } else {
                $error = "Error al crear la cuenta de usuario.";
            }
        }
    }
}

$titulo_pagina = "Crear Empleado - Hotel Yokoso";

$contenido_principal = '
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-rojo fw-bold">Registrar Nuevo Empleado</h2>
    
        </div>
    </div>

    ' . (!empty($mensaje) ? '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>¡Éxito!</strong> ' . htmlspecialchars($mensaje) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>' : '') . '

    ' . (!empty($error) ? '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error:</strong> ' . htmlspecialchars($error) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>' : '') . '
    
    <div class="reserva-form-container">
        <form method="POST" id="formEmpleado">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" 
                           class="form-control" 
                           name="nombre" 
                           required
                           placeholder="Ej: María, José, Ana"
                           oninput="validarTexto(this)">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Apellido *</label>
                    <input type="text" 
                           class="form-control" 
                           name="apellido" 
                           required
                           placeholder="Ej: López, Fernández, Quispe"
                           oninput="validarTexto(this)">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cargo *</label>
                    <input type="text" 
                           class="form-control" 
                           name="cargo" 
                           required
                           placeholder="Ej: Recepcionista, Limpieza, Chef"
                           oninput="validarTexto(this)">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Usuario *</label>
                    <input type="text" 
                           class="form-control" 
                           name="nombreUsuario" 
                           required
                           placeholder="Ej: mary_lopez, jose_fernandez"
                           pattern="[a-zA-Z0-9._-]{3,20}"
                           title="Solo letras, números, puntos, guiones y guiones bajos. Mínimo 3 caracteres.">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" 
                           class="form-control" 
                           name="email" 
                           required
                           placeholder="Ej: empleado@yokoso.com">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contraseña *</label>
                    <input type="password" 
                           class="form-control" 
                           name="password" 
                           required
                           minlength="8">
                    <div class="form-text text-muted">
                        Mín. 8 caracteres: mayúscula, minúscula, número y símbolo (@$!%*?&).
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirmar Contraseña *</label>
                    <input type="password" 
                           class="form-control" 
                           name="confirmarPassword" 
                           required
                           minlength="8">
                </div>
            </div>

            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                <!-- ❤️ Botón Cancelar con icono y color negro -->
                <a href="ver_empleados.php" class="btn btn-cancelar me-md-2">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
                <!-- 💖 Botón Guardar con color rojo Yokoso -->
                <button type="submit" class="btn btn-yokoso btn-lg shadow-sm">
                    <i class="fas fa-save me-2"></i>Guardar Empleado
                </button>
            </div>
        </form>
    </div>

    <script>
    // ❤️ Validación en tiempo real: solo letras y espacios (con tildes y ñ)
    function validarTexto(input) {
        // Permitimos letras, espacios, tildes y ñ (en mayús y minús)
        input.value = input.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúñÑ\\s]/g, "");
    }

    // 💖 Validación adicional al enviar (opcional, pero segura)
    document.getElementById("formEmpleado").addEventListener("submit", function(e) {
        const nombre = document.querySelector("[name=\'nombre\']").value;
        const apellido = document.querySelector("[name=\'apellido\']").value;
        const cargo = document.querySelector("[name=\'cargo\']").value;

        if (!/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\\s]+$/.test(nombre)) {
            alert("El nombre solo puede contener letras y espacios.");
            e.preventDefault();
            return;
        }
        if (!/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\\s]+$/.test(apellido)) {
            alert("El apellido solo puede contener letras y espacios.");
            e.preventDefault();
            return;
        }
        if (!/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\\s]+$/.test(cargo)) {
            alert("El cargo solo puede contener letras y espacios.");
            e.preventDefault();
            return;
        }
    });
    </script>
';

include 'plantilla_admin.php';
?>