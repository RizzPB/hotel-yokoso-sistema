<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si hay sesión
if (!isset($_SESSION['idUsuario'])) {
    header("Location: ../public/login.php");
    exit();
}

// Verificar rol: solo huésped puede acceder
if (($_SESSION['rol'] ?? '') !== 'huésped') {
    die("Acceso denegado. Solo huéspedes.");
}