<?php
$body_class = 'layout-dashboard'; // para footer fijo
$title = "Selecciona tu Habitación - Hotel Yokoso";
ob_start();
?>

<!-- Navbar igual al dashboard -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--color-rojo-quemado);">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="../index.php">
            <img src="../assets/img/empresaLogoYokoso.png" 
                 alt="Logo Hotel Yokoso" 
                 class="logo-navbar">
            <span class="fw-bold">Hotel Yokoso</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavGuest"
                aria-controls="navbarNavGuest" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNavGuest">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <span class="navbar-text text-white me-3">
                        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['nombreUsuario'] ?? 'Huésped') ?>
                    </span>
                </li>
                <li class="nav-item ms-2">
                    <a href="../logout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-door-open me-1"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Indicador de pasos -->
<nav aria-label="Progreso de reserva" class="mb-4">
  <ol class="progress">
    <li class="progress-bar bg-mostaza" style="width: 33%; margin-left: -33px;">Habitación</li>
    <li class="progress-bar bg-light" style="width: 33%;">Paquete</li>
    <li class="progress-bar bg-light" style="width: 36%; ">Confirmar</li>
  </ol>
</nav>

<div class="container py-4">
  <div class="text-center mb-5">
    <h1 class="display-5 fw-bold" style="font-family: var(--font-heading); color: var(--color-rojo);">
      Elige tu Habitación en Uyuni
    </h1>
    <p class="lead" style="font-family: var(--font-body);">
      Selecciona una o más habitaciones disponibles para tu estadía.
    </p>
  </div>

  <!-- Filtro por tipo -->
  <div class="mb-4">
    <label for="filtroTipo" class="form-label fw-bold">Filtrar por tipo de habitación:</label>
    <select class="form-select" id="filtroTipo">
      <option value="todos">Todos los tipos</option>
      <option value="simple">Simple</option>
      <option value="doble">Doble</option>
      <option value="suite">Suite</option>
    </select>
  </div>

  <!-- Contenedor de habitaciones -->
  <div id="contenedorHabitaciones">
    <!-- Se llenará con JS -->
  </div>

  <!-- Botón siguiente (deshabilitado al inicio) -->
  <div class="text-center mt-5">
    <button type="button" class="btn btn-rojo btn-lg px-5 py-2" id="btnSiguiente" disabled
            style="font-family: var(--font-body);">
      <i class="fas fa-arrow-right me-2"></i> Siguiente: Paquetes Turísticos
    </button>
  </div>
</div>

<!-- Formulario oculto -->
<form id="formHabitaciones" method="POST" action="packages.php">
  <input type="hidden" name="habitaciones_seleccionadas" id="inputHabitaciones">
</form>

<!-- Footer -->
<footer class="bg-black text-white text-center py-3 small">
    <div class="container">
        <p class="mb-1">© <?= date('Y') ?> Hotel Yokoso. Todos los derechos reservados.</p>
        <p class="mb-0"><i class="fas fa-phone me-1"></i> +591 7000 0000</p>
    </div>
</footer>

<script>
// Datos de habitaciones desde PHP
const habitacionesPorTipo = <?= json_encode($habitacionesPorTipo) ?>;

function renderHabitaciones(tipo = 'todos') {
    const contenedor = document.getElementById('contenedorHabitaciones');
    let habitacionesAMostrar = [];

    if (tipo === 'todos') {
        // Aplanar todas
        habitacionesAMostrar = Object.values(habitacionesPorTipo).flat();
    } else {
        habitacionesAMostrar = habitacionesPorTipo[tipo] || [];
    }

    if (habitacionesAMostrar.length === 0) {
        contenedor.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-bed text-muted" style="font-size: 3rem;"></i>
                <p class="mt-2 text-muted">No hay habitaciones disponibles de este tipo.</p>
            </div>
        `;
        return;
    }

    let html = '<div class="row g-4">';
    habitacionesAMostrar.forEach(h => {
        const foto = h.foto ? `../assets/img/habitaciones/${h.foto}` : '../assets/img/habitaciones/normal-placeholder.jpg';
        const tipoTexto = h.tipo.charAt(0).toUpperCase() + h.tipo.slice(1);
        html += `
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <img src="${foto}" class="card-img-top" alt="Habitación ${h.numero}" 
                     style="height: 200px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title" style="font-family: var(--font-heading);">Habitación ${h.numero}</h5>
                    <p class="card-text flex-grow-1" style="font-family: var(--font-body);">
                        <span class="d-block mb-1">
                            <i class="fas fa-home text-mostaza me-1"></i> 
                            <strong>${tipoTexto}</strong>
                        </span>
                        <span class="d-block">
                            <i class="fas fa-tag text-mostaza me-1"></i> 
                            <strong>Bs ${parseFloat(h.precioNoche).toFixed(2)}</strong> / noche
                        </span>
                    </p>
                    <div class="form-check mt-auto">
                        <input class="form-check-input habitacion-checkbox" type="checkbox" 
                               value="${h.idHabitacion}" id="h${h.idHabitacion}">
                        <label class="form-check-label" for="h${h.idHabitacion}">
                            Seleccionar
                        </label>
                    </div>
                </div>
            </div>
        </div>
        `;
    });
    html += '</div>';
    contenedor.innerHTML = html;

    // Re-vincular eventos
    document.querySelectorAll('.habitacion-checkbox').forEach(cb => {
        cb.addEventListener('change', actualizarBoton);
    });
    actualizarBoton(); // en caso de que ya hubiera selección
}

function actualizarBoton() {
    const seleccionadas = Array.from(document.querySelectorAll('.habitacion-checkbox:checked'))
                               .map(cb => cb.value);
    document.getElementById('btnSiguiente').disabled = seleccionadas.length === 0;
}

// Inicializar
document.getElementById('filtroTipo').addEventListener('change', e => {
    renderHabitaciones(e.target.value);
});

document.getElementById('btnSiguiente').addEventListener('click', function() {
    const seleccionadas = Array.from(document.querySelectorAll('.habitacion-checkbox:checked'))
                               .map(cb => cb.value);
    if (seleccionadas.length === 0) {
        alert("⚠️ Por favor, selecciona al menos una habitación.");
        return;
    }
    document.getElementById('inputHabitaciones').value = JSON.stringify(seleccionadas);
    document.getElementById('formHabitaciones').submit();
});

// Mostrar todas al cargar
renderHabitaciones('todos');
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>