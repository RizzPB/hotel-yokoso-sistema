<?php
// public/vistas/recepcionista/ver_huespedes.php

define('ACCESO_PERMITIDO', true);

session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../../config/database.php';

$stmt = $pdo->prepare("
    SELECT idHuesped, nombre, apellido, tipoDocumento, nroDocumento, procedencia, email, telefono
    FROM Huesped
    WHERE activo = 1
    ORDER BY nombre ASC
");
$stmt->execute();
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Ver Huéspedes - Hotel Yokoso";

$contenido_principal = '
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-rojo fw-bold">Huéspedes Registrados</h2>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered border-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Tipo Doc.</th>
                    <th>Nro. Doc.</th>
                    <th>Procedencia</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                ' . (empty($huespedes) ? '<tr><td colspan="9" class="text-center py-4">No hay huéspedes registrados.</td></tr>' : implode('', array_map(function($h) {
                    return '
                        <tr>
                            <td>' . htmlspecialchars($h['idHuesped']) . '</td>
                            <td>' . htmlspecialchars($h['nombre']) . '</td>
                            <td>' . htmlspecialchars($h['apellido']) . '</td>
                            <td>' . htmlspecialchars($h['tipoDocumento']) . '</td>
                            <td>' . htmlspecialchars($h['nroDocumento']) . '</td>
                            <td>' . htmlspecialchars($h['procedencia']) . '</td>
                            <td>' . htmlspecialchars($h['email']) . '</td>
                            <td>' . htmlspecialchars($h['telefono']) . '</td>
                            <td class="text-center">
                                <a href="editar_huesped.php?id=' . $h['idHuesped'] . '" class="btn btn-outline-primary btn-sm me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarHuesped(' . $h['idHuesped'] . ')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>';
                }, $huespedes))) . '
            </tbody>
        </table>
    </div>
';

include 'plantilla_recepcionista.php';
?>