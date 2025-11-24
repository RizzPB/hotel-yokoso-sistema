<?php
// public/recepcionista/ver_huespedes.php

define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// 1. Traemos todos los huéspedes activos (sin ordenar aún, lo hará JS)
$stmt = $pdo->prepare("
    SELECT idHuesped, nombre, apellido, tipoDocumento, nroDocumento, 
           procedencia, email, telefono
    FROM Huesped
    WHERE activo = 1
");
$stmt->execute();
$huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Huéspedes Registrados - Hotel Yokoso";

$contenido_principal = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-rojo fw-bold">Huéspedes Registrados</h2>
    <div class="d-flex align-items-center gap-3">
        <small class="text-muted">
            Total: <strong id="totalHuespedes">' . count($huespedes) . '</strong> huésped(es)
        </small>
        <select class="form-select form-select-sm w-auto rounded-pill shadow-sm" id="ordenarHuespedes">
            <option value="nuevo">Más nuevo primero</option>
            <option value="antiguo">Más antiguo primero</option>
            <option value="nombre-asc">Nombre A → Z</option>
            <option value="nombre-desc">Nombre Z → A</option>
        </select>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover table-striped align-middle" id="tablaHuespedes">
        <thead class="table-dark text-center">
            <tr>
                <th class="text-start">ID</th>
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
        <tbody id="listaHuespedes">
            <!-- Aquí JavaScript pondrá las filas dinámicamente -->
        </tbody>
    </table>
</div>

<script>
// ==================== DATOS DE HUESPEDES DESDE PHP ====================
const huespedes = ' . json_encode($huespedes) . ';  
// → json_encode convierte el array PHP en un objeto JavaScript válido y seguro

// ==================== FUNCIÓN PARA MOSTRAR LA TABLA ====================
function renderizarHuespedes(lista) {
    const tbody = document.getElementById("listaHuespedes");

    // Si no hay huéspedes
    if (lista.length === 0) {
        tbody.innerHTML = "<tr><td colspan=\"9\" class=\"text-center py-5 text-muted\">No hay huéspedes registrados aún.</td></tr>";
        return;
    }

    // Generamos las filas con map() + join() 
    const filas = lista.map(h => {
        return "<tr>" +
            "<td class=\"fw-bold text-rojo\">#" + h.idHuesped + "</td>" +
            "<td>" + escaparHTML(h.nombre || "") + "</td>" +
            "<td>" + escaparHTML(h.apellido || "") + "</td>" +
            "<td><span class=\"badge bg-secondary\">" + escaparHTML(h.tipoDocumento || "") + "</span></td>" +
            "<td>" + escaparHTML(h.nroDocumento || "") + "</td>" +
            "<td>" + escaparHTML(h.procedencia || "-") + "</td>" +
            "<td><small>" + escaparHTML(h.email || "-") + "</small></td>" +
            "<td>" + escaparHTML(h.telefono || "-") + "</td>" +
            "<td class=\"text-center\">" +
                "<a href=\"editar_huesped.php?id=" + h.idHuesped + "\" class=\"btn btn-outline-primary btn-sm\">Editar</a> " +
                "<button class=\"btn btn-outline-danger btn-sm ms-1\" onclick=\"eliminarHuesped(" + h.idHuesped + ")\">Eliminar</button>" +
            "</td>" +
        "</tr>";
    }).join("");  // Une todas las filas sin comas

    tbody.innerHTML = filas;
}

// ==================== FUNCIÓN PARA ESCAPAR HTML (SEGURIDAD) ====================
function escaparHTML(texto) {
    const div = document.createElement("div");
    div.textContent = texto;
    return div.innerHTML;
}

// ==================== FUNCIÓN PARA ORDENAR ====================
function ordenarHuespedes() {
    const criterio = document.getElementById("ordenarHuespedes").value;
    let ordenados = [...huespedes];  // Copia del array original

    if (criterio === "nuevo") {
        ordenados.sort((a, b) => b.idHuesped - a.idHuesped);        // ID descendente
    } else if (criterio === "antiguo") {
        ordenados.sort((a, b) => a.idHuesped - b.idHuesped);        // ID ascendente
    } else if (criterio === "nombre-asc") {
        ordenados.sort((a, b) => (a.nombre + " " + a.apellido).localeCompare(b.nombre + " " + b.apellido));
    } else if (criterio === "nombre-desc") {
        ordenados.sort((a, b) => (b.nombre + " " + b.apellido).localeCompare(a.nombre + " " + a.apellido));
    }

    renderizarHuespedes(ordenados);
    document.getElementById("totalHuespedes").textContent = ordenados.length;
}

// ==================== INICIAR ====================
ordenarHuespedes();  // Carga inicial: más nuevo primero

// Escuchar cambios en el select
document.getElementById("ordenarHuespedes").addEventListener("change", ordenarHuespedes);

// ==================== ELIMINAR HUÉSPED ====================
function eliminarHuesped(id) {
    if (confirm("¿Estás segura de eliminar este huésped? Esta acción no se puede deshacer.")) {
        window.location.href = "eliminar_huesped.php?id=" + id;
    }
}
</script>
';

include 'plantilla_recepcionista.php';
?>