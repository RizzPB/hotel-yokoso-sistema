<?php
// public/recepcionista/ver_huespedes.php


define('ACCESO_PERMITIDO', true);
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

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
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <h2 class="text-rojo fw-bold mb-0">Huéspedes Registrados</h2>
    
    <div class="d-flex flex-wrap align-items-center gap-3">
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

<!-- ❤️ Buscador en tiempo real -->
<div class="mb-4">
    <div class="input-group input-group-lg">
        <span class="input-group-text bg-rojo-quemado text-white">
            <i class="fas fa-search"></i>
        </span>
        <input type="text" 
               id="buscarHuespedes" 
               class="form-control rounded-pill" 
               placeholder="Buscar por nombre, apellido, documento, email o teléfono...">
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
            <!-- Las filas se generarán con JavaScript -->
        </tbody>
    </table>
</div>

<script>
// ==================== DATOS DE HUESPEDES DESDE PHP ====================
const huespedes = ' . json_encode($huespedes) . ';  

// ==================== ESTADO ACTUAL ====================
let huéspedesOrdenados = [...huespedes]; // Copia inicial

// ==================== FUNCIÓN PARA MOSTRAR LA TABLA ====================
function renderizarHuespedes(lista) {
    const tbody = document.getElementById("listaHuespedes");
    const totalElement = document.getElementById("totalHuespedes");

    totalElement.textContent = lista.length;

    if (lista.length === 0) {
        tbody.innerHTML = "<tr><td colspan=\"9\" class=\"text-center py-5 text-muted\"><i class=\"fas fa-search fa-3x mb-3\"></i><br>No se encontraron huéspedes.</td></tr>";
        return;
    }

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
    }).join("");

    tbody.innerHTML = filas;
}

// ==================== ESCAPAR HTML (SEGURIDAD) ====================
function escaparHTML(texto) {
    const div = document.createElement("div");
    div.textContent = texto;
    return div.innerHTML;
}

// ==================== ORDENAR HUÉSPEDES ====================
function ordenarHuespedes() {
    const criterio = document.getElementById("ordenarHuespedes").value;

    if (criterio === "nuevo") {
        huéspedesOrdenados.sort((a, b) => b.idHuesped - a.idHuesped);
    } else if (criterio === "antiguo") {
        huéspedesOrdenados.sort((a, b) => a.idHuesped - b.idHuesped);
    } else if (criterio === "nombre-asc") {
        huéspedesOrdenados.sort((a, b) => 
            (a.nombre + " " + a.apellido).localeCompare(b.nombre + " " + b.apellido, "es", { sensitivity: "base" })
        );
    } else if (criterio === "nombre-desc") {
        huéspedesOrdenados.sort((a, b) => 
            (b.nombre + " " + b.apellido).localeCompare(a.nombre + " " + a.apellido, "es", { sensitivity: "base" })
        );
    }

    // Aplicamos búsqueda actual (si hay algo en el input)
    aplicarBusqueda();
}

// ==================== APLICAR BÚSQUEDA EN TIEMPO REAL ====================
function aplicarBusqueda() {
    const termino = document.getElementById("buscarHuespedes").value.toLowerCase().trim();
    
    if (termino === "") {
        renderizarHuespedes(huéspedesOrdenados);
        return;
    }

    const filtrados = huéspedesOrdenados.filter(h => 
        (h.nombre && h.nombre.toLowerCase().includes(termino)) ||
        (h.apellido && h.apellido.toLowerCase().includes(termino)) ||
        (h.nroDocumento && h.nroDocumento.toLowerCase().includes(termino)) ||
        (h.email && h.email.toLowerCase().includes(termino)) ||
        (h.telefono && h.telefono.toLowerCase().includes(termino))
    );

    renderizarHuespedes(filtrados);
}

// ==================== INICIAR ====================
ordenarHuespedes(); // Carga inicial

// Eventos
document.getElementById("ordenarHuespedes").addEventListener("change", ordenarHuespedes);
document.getElementById("buscarHuespedes").addEventListener("input", aplicarBusqueda);

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